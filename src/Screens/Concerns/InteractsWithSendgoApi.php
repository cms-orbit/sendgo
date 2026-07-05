<?php

declare(strict_types=1);

namespace CmsOrbit\Sendgo\Screens\Concerns;

use CmsOrbit\Sendgo\Api\SendgoAdminClient;
use CmsOrbit\Sendgo\Settings\SendgoSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

trait InteractsWithSendgoApi
{
    protected function apiFilters(Request $request): array
    {
        $from = $request->string('from')->toString();
        $to = $request->string('to')->toString();
        $count = max(1, min(100, (int) $request->input('count', 30)));

        return array_filter([
            'from' => $from !== '' ? $from : Carbon::now()->subDays(90)->toDateString(),
            'to' => $to !== '' ? $to : Carbon::now()->toDateString(),
            'count' => $count,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function campaignRows(array $response, string $key = 'campaigns'): array
    {
        $rows = data_get($response, "data.{$key}.data", []);

        return is_array($rows) ? $rows : [];
    }

    protected function settings(): SendgoSettings
    {
        return app(SendgoSettings::class);
    }

    protected function client(): SendgoAdminClient
    {
        return app(SendgoAdminClient::class);
    }
}
