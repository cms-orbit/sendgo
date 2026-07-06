<?php

declare(strict_types=1);

namespace CmsOrbit\Sendgo\Services;

use CmsOrbit\Sendgo\Api\SendgoAdminClient;
use CmsOrbit\Sendgo\Models\SendgoTemplate;
use CmsOrbit\Sendgo\Settings\SendgoSettings;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class SendgoHubDashboard
{
    public function __construct(
        protected SendgoSettings $settings,
        protected SendgoAdminClient $client,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(): array
    {
        $settingsUrl = route('orbit.configs.group', ['group' => 'SendGo']);
        $templatesUrl = route('orbit.entities.sendgo-templates.index');
        $sendersUrl = route('orbit.sendgo.senders.index');
        $profilesUrl = route('orbit.sendgo.kakao-senders.index');

        if (! $this->settings->configured()) {
            return [
                'needsSetup' => true,
                'settingsUrl' => $settingsUrl,
                'connectionError' => null,
                'recentMessages' => [],
                'messageTypes' => [],
                'senders' => [],
                'kakaoProfiles' => [],
                'templates' => $this->templates($templatesUrl),
                'links' => [
                    'templates' => $templatesUrl,
                    'senders' => $sendersUrl,
                    'profiles' => $profilesUrl,
                ],
            ];
        }

        $connectionError = null;
        $recentMessages = [];
        $messageTypes = [];
        $senders = [];
        $kakaoProfiles = [];

        try {
            $campaigns = $this->collectCampaigns();
            $recentMessages = $this->recentMessages($campaigns);
            $messageTypes = $this->messageTypeChart($campaigns);
            $senders = $this->senders();
            $kakaoProfiles = $this->kakaoProfiles();
        } catch (\Throwable $exception) {
            $connectionError = $exception->getMessage();
        }

        return [
            'needsSetup' => false,
            'settingsUrl' => $settingsUrl,
            'connectionError' => $connectionError,
            'recentMessages' => $recentMessages,
            'messageTypes' => $messageTypes,
            'senders' => $senders,
            'kakaoProfiles' => $kakaoProfiles,
            'templates' => $this->templates($templatesUrl),
            'links' => [
                'templates' => $templatesUrl,
                'senders' => $sendersUrl,
                'profiles' => $profilesUrl,
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function collectCampaigns(): array
    {
        $filters = [
            'from' => Carbon::now()->subDays(30)->toDateString(),
            'to' => Carbon::now()->toDateString(),
            'count' => 30,
        ];

        $campaigns = [];

        foreach ($this->campaignSources() as $source) {
            $response = $this->client->{$source['method']}($filters);
            $rows = data_get($response, $source['dataKey'].'.data', []);

            if (! is_array($rows)) {
                continue;
            }

            foreach ($rows as $row) {
                if (! is_array($row)) {
                    continue;
                }

                $campaigns[] = $this->normalizeCampaign($row, $source);
            }
        }

        return $campaigns;
    }

    /**
     * @return array<int, array{method: string, dataKey: string, channel: string, viewRoute: string}>
     */
    protected function campaignSources(): array
    {
        return [
            [
                'method' => 'listMessages',
                'dataKey' => 'data.campaigns',
                'channel' => 'SMS',
                'viewRoute' => 'orbit.sendgo.messages.view',
            ],
            [
                'method' => 'listNotices',
                'dataKey' => 'data.campaigns',
                'channel' => 'AlimTalk',
                'viewRoute' => 'orbit.sendgo.notices.view',
            ],
            [
                'method' => 'listFriends',
                'dataKey' => 'data.campaigns',
                'channel' => 'FriendTalk',
                'viewRoute' => 'orbit.sendgo.friends.view',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array{channel: string, viewRoute: string}  $source
     * @return array<string, mixed>
     */
    protected function normalizeCampaign(array $row, array $source): array
    {
        $uuid = (string) ($row['uuid'] ?? '');
        $messageType = (string) ($row['message_type'] ?? '—');
        $sortKey = (int) ($row['id'] ?? 0);

        return [
            'uuid' => $uuid,
            'channel' => $source['channel'],
            'message_type' => $messageType,
            'type_label' => $this->messageTypeLabel($messageType, $source['channel']),
            'management_code' => (string) ($row['management_code'] ?? '—'),
            'status' => (string) ($row['status'] ?? '—'),
            'total_count' => (int) ($row['total_count'] ?? 0),
            'success_count' => (int) ($row['success_count'] ?? 0),
            'failed_count' => (int) ($row['failed_count'] ?? 0),
            'created_at' => (string) ($row['created_at_formatted'] ?? ($row['updated_at_formatted'] ?? '—')),
            'sort_key' => $sortKey,
            'url' => $uuid !== '' ? route($source['viewRoute'], ['id' => $uuid]) : '#',
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $campaigns
     * @return array<int, array<string, mixed>>
     */
    protected function recentMessages(array $campaigns): array
    {
        return collect($campaigns)
            ->sortByDesc('sort_key')
            ->take(8)
            ->values()
            ->map(fn (array $row): array => [
                'channel' => $row['channel'],
                'message_type' => $row['message_type'],
                'management_code' => $row['management_code'],
                'status' => $row['status'],
                'total_count' => $row['total_count'],
                'success_count' => $row['success_count'],
                'failed_count' => $row['failed_count'],
                'created_at' => $row['created_at'],
                'url' => $row['url'],
            ])
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $campaigns
     * @return array<int, array{name: string, values: array<int, int>, labels: array<int, string>}>
     */
    protected function messageTypeChart(array $campaigns): array
    {
        $counts = collect($campaigns)
            ->countBy(fn (array $row): string => (string) $row['type_label'])
            ->sortDesc();

        if ($counts->isEmpty()) {
            return [];
        }

        return [[
            'name' => __('Volume'),
            'labels' => $counts->keys()->values()->all(),
            'values' => $counts->values()->all(),
        ]];
    }

    protected function messageTypeLabel(string $messageType, string $channel): string
    {
        return match ($messageType) {
            'SMS' => __('SMS'),
            'LMS' => __('LMS'),
            'MMS' => __('MMS'),
            'AT' => __('AlimTalk'),
            'FT' => __('FriendTalk text'),
            'FI' => __('FriendTalk image'),
            'FW' => __('FriendTalk wide'),
            'FL' => __('FriendTalk list'),
            default => $channel !== '' ? $channel : ($messageType !== '' && $messageType !== '—' ? $messageType : __('Other')),
        };
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function senders(): array
    {
        $response = $this->client->listSenders();
        $rows = data_get($response, 'data.senders', []);

        if (! is_array($rows)) {
            return [];
        }

        return collect($rows)
            ->filter(fn ($row): bool => is_array($row))
            ->take(6)
            ->map(fn (array $row): array => [
                'alias' => (string) ($row['sender_alias'] ?? '—'),
                'number' => (string) ($row['phone_number'] ?? ($row['phone_e164'] ?? '—')),
                'status' => (string) ($row['status'] ?? '—'),
                'type' => (string) ($row['primary_type'] ?? '—'),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function kakaoProfiles(): array
    {
        $response = $this->client->listKakaoSenders();
        $rows = data_get($response, 'data.senders', []);

        if (! is_array($rows)) {
            return [];
        }

        return collect($rows)
            ->filter(fn ($row): bool => is_array($row))
            ->take(6)
            ->map(fn (array $row): array => [
                'name' => (string) ($row['name'] ?? '—'),
                'yellow_id' => (string) ($row['yellow_id'] ?? '—'),
                'status' => (string) ($row['status'] ?? '—'),
                'sender_key' => Str::limit((string) ($row['sender_key'] ?? '—'), 18),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function templates(string $templatesUrl): array
    {
        return SendgoTemplate::query()
            ->latest('synced_at')
            ->latest('id')
            ->limit(8)
            ->get()
            ->map(fn (SendgoTemplate $template): array => [
                'template_code' => $template->template_code,
                'template_name' => $template->template_name ?: $template->template_code,
                'status' => $template->status ?: '—',
                'inspection_status' => $template->inspection_status ?: '—',
                'synced_at' => $template->synced_at?->diffForHumans() ?? '—',
                'url' => $templatesUrl,
            ])
            ->all();
    }
}
