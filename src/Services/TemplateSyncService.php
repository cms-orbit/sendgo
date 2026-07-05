<?php

declare(strict_types=1);

namespace CmsOrbit\Sendgo\Services;

use CmsOrbit\Sendgo\Api\SendgoAdminClient;
use CmsOrbit\Sendgo\Models\SendgoTemplate;
use CmsOrbit\Sendgo\Settings\SendgoSettings;
use Illuminate\Support\Carbon;

class TemplateSyncService
{
    public function __construct(
        private readonly SendgoSettings $settings,
        private readonly SendgoAdminClient $client,
    ) {}

    public function sync(): int
    {
        if (! $this->settings->configured()) {
            throw new \RuntimeException(__('SendGo credentials must be configured before syncing templates.'));
        }

        $filters = [];

        if ($this->settings->kakaoSenderKey() !== null) {
            $filters['kakaoSenderKey'] = $this->settings->kakaoSenderKey();
        }

        $response = $this->client->listTemplates($filters);
        $templates = data_get($response, 'data.templates.data', []);
        $synced = 0;
        $now = Carbon::now();

        foreach ($templates as $template) {
            if (! is_array($template) || empty($template['uuid'])) {
                continue;
            }

            SendgoTemplate::query()->updateOrCreate(
                ['uuid' => (string) $template['uuid']],
                [
                    'template_code' => (string) ($template['template_code'] ?? ''),
                    'template_name' => $template['template_name'] ?? null,
                    'status' => $template['status'] ?? null,
                    'inspection_status' => $template['inspection_status'] ?? null,
                    'kakao_sender_id' => $template['kakao_sender_id'] ?? null,
                    'template_content' => $template['template_content'] ?? null,
                    'buttons' => $template['buttons'] ?? null,
                    'payload' => $template,
                    'synced_at' => $now,
                ]
            );

            $synced++;
        }

        return $synced;
    }
}
