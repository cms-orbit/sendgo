<?php

declare(strict_types=1);

namespace CmsOrbit\Sendgo\Services;

use CmsOrbit\Sendgo\Models\SendgoDeliveryLog;
use CmsOrbit\Sendgo\Settings\SendgoSettings;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Techigh\SendgoNotification\Attributes\Alim\AlimTalk;
use Techigh\SendgoNotification\Attributes\Alim\AlimTalkMessage;
use Techigh\SendgoNotification\Exceptions\SendGoException;

class SendgoAlimtalkSender
{
    public function __construct(
        private readonly SendgoSettings $settings,
    ) {}

    /**
     * @param  list<array<string, mixed>>  $contacts
     */
    public function send(
        string $templateCode,
        array $contacts,
        bool $replaceSms = false,
        ?string $smsContent = null,
        ?string $storeUuid = null,
    ): void {
        if ($templateCode === '' || $contacts === []) {
            Log::warning('[SendgoAlimtalkSender] skipped — missing template or contacts', [
                'templateCode' => $templateCode,
                'contactCount' => count($contacts),
            ]);

            return;
        }

        if (! $this->settings->configured() && app()->environment(['local', 'testing'])) {
            Log::info('[SendgoAlimtalkSender] stub send (SendGo not configured)', [
                'templateCode' => $templateCode,
                'storeUuid' => $storeUuid,
                'contacts' => $contacts,
            ]);

            $this->logDelivery($storeUuid, $templateCode, count($contacts), true);

            return;
        }

        if (! $this->settings->configured()) {
            throw new \RuntimeException('SendGo credentials must be configured before sending AlimTalk messages.');
        }

        $message = AlimTalkMessage::make()
            ->templateCode($templateCode)
            ->replaceSms($replaceSms ? 'Y' : 'N');

        if ($smsContent !== null && $smsContent !== '') {
            $message->smsContent($smsContent);
        }

        $recipientCount = 0;

        foreach ($contacts as $contact) {
            if (! is_array($contact)) {
                continue;
            }

            $phone = $contact['phone'] ?? $contact['contact'] ?? null;

            if (! is_string($phone) || $phone === '') {
                continue;
            }

            $vars = $contact;
            unset($vars['phone'], $vars['contact']);
            $vars['contact'] = $phone;

            $message->to($vars);
            $recipientCount++;
        }

        if ($recipientCount === 0) {
            Log::warning('[SendgoAlimtalkSender] skipped — no valid contacts', [
                'templateCode' => $templateCode,
            ]);

            return;
        }

        try {
            app(AlimTalk::class)->send($message->toArray());
            $this->logDelivery($storeUuid, $templateCode, $recipientCount, true);
        } catch (SendGoException $exception) {
            $this->logDelivery($storeUuid, $templateCode, $recipientCount, false);

            Log::error('[SendgoAlimtalkSender] SendGo failed', [
                'message' => $exception->getMessage(),
                'templateCode' => $templateCode,
            ]);

            throw $exception;
        }
    }

    public function countUsageForStore(string $storeUuid, Carbon $date): int
    {
        [$startOfDay, $endOfDay] = [
            $date->copy()->startOfDay(),
            $date->copy()->endOfDay(),
        ];

        return (int) SendgoDeliveryLog::query()
            ->where('store_uuid', $storeUuid)
            ->where('channel', 'alimtalk')
            ->where('success', true)
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->sum('recipient_count');
    }

    protected function logDelivery(?string $storeUuid, string $templateCode, int $recipientCount, bool $success): void
    {
        SendgoDeliveryLog::query()->create([
            'store_uuid' => $storeUuid,
            'template_code' => $templateCode,
            'channel' => 'alimtalk',
            'recipient_count' => $recipientCount,
            'success' => $success,
        ]);
    }
}
