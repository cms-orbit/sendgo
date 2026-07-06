<?php

declare(strict_types=1);

namespace CmsOrbit\Sendgo;

use CmsOrbit\Core\Auth\Phone\PhoneVerificationSender;
use CmsOrbit\Sendgo\Services\SendgoAlimtalkSender;
use CmsOrbit\Sendgo\Settings\SendgoSettings;
use Illuminate\Support\Facades\Log;
use Techigh\SendgoNotification\Attributes\Sms\Sms;
use Techigh\SendgoNotification\Attributes\Sms\SmsMessage;

class SendgoPhoneVerificationSender implements PhoneVerificationSender
{
    public function __construct(
        private readonly SendgoSettings $settings,
        private readonly SendgoAlimtalkSender $alimtalkSender,
    ) {}

    public function send(string $phone, string $code, string $channel): void
    {
        if (! $this->settings->configured() && app()->environment(['local', 'testing'])) {
            Log::info('Orbit SendGo verification code', [
                'channel' => $channel,
                'phone' => $phone,
                'code' => $code,
            ]);

            return;
        }

        if (! $this->settings->configured()) {
            throw new \RuntimeException('SendGo endpoint and credentials must be configured before phone login can send verification codes.');
        }

        if ($channel === 'alimtalk') {
            $this->sendAlimTalk($phone, $code);

            return;
        }

        $this->sendSms($phone, $code);
    }

    protected function sendSms(string $phone, string $code): void
    {
        app(Sms::class)->send(
            SmsMessage::make()
                ->messageType('SMS')
                ->content(__('인증번호는 :code 입니다.', ['code' => $code]))
                ->to([
                    'contact' => $phone,
                    'var1' => $code,
                ])
                ->toArray()
        );
    }

    protected function sendAlimTalk(string $phone, string $code): void
    {
        $templateCode = $this->settings->phoneVerificationTemplateCode();

        if ($templateCode === null || $templateCode === '') {
            throw new \RuntimeException('SendGo phone verification AlimTalk template code must be configured.');
        }

        $this->alimtalkSender->send(
            templateCode: $templateCode,
            contacts: [
                [
                    'contact' => $phone,
                    'var1' => $code,
                ],
            ],
            replaceSms: true,
            smsContent: __('인증번호는 :code 입니다.', ['code' => $code]),
        );
    }
}
