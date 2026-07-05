<?php

declare(strict_types=1);

namespace CmsOrbit\Sendgo\Screens\Campaigns;

use CmsOrbit\Core\Screen\Action;
use CmsOrbit\Core\Screen\Actions\Link;
use CmsOrbit\Core\Screen\Layout;
use CmsOrbit\Core\Screen\Screen;
use CmsOrbit\Core\Screen\TD;
use CmsOrbit\Core\Support\Facades\Layout as LayoutFactory;
use CmsOrbit\Sendgo\Screens\Concerns\InteractsWithSendgoApi;
use Illuminate\Http\Request;

abstract class AbstractCampaignListScreen extends Screen
{
    use InteractsWithSendgoApi;

    abstract protected function listRouteName(): string;

    abstract protected function viewRouteName(): string;

    /**
     * @return array<string, mixed>
     */
    abstract protected function fetchCampaigns(array $filters): array;

    public function permission(): ?iterable
    {
        return ['sendgo.campaigns'];
    }

    /**
     * @return array<string, mixed>
     */
    public function query(Request $request): array
    {
        $filters = $this->apiFilters($request);
        $error = null;
        $rows = [];

        if ($this->settings()->configured()) {
            try {
                $response = $this->fetchCampaigns($filters);
                $rows = $this->campaignRows($response);
            } catch (\Throwable $exception) {
                $error = $exception->getMessage();
            }
        }

        return [
            'filters' => $filters,
            'error' => $error,
            'campaigns' => collect($rows)->map(function (array $row): array {
                $uuid = (string) ($row['uuid'] ?? '');

                return [
                    'uuid' => $uuid,
                    'management_code' => $row['management_code'] ?? '—',
                    'status' => $row['status'] ?? '—',
                    'message_type' => $row['message_type'] ?? ($row['template_code'] ?? '—'),
                    'total_count' => $row['total_count'] ?? 0,
                    'success_count' => $row['success_count'] ?? 0,
                    'failed_count' => $row['failed_count'] ?? 0,
                    'created_at_formatted' => $row['created_at_formatted'] ?? ($row['updated_at_formatted'] ?? '—'),
                    'url' => $uuid !== '' ? route($this->viewRouteName(), ['id' => $uuid]) : '#',
                ];
            })->all(),
        ];
    }

    /**
     * @return Action[]
     */
    public function commandBar(): array
    {
        return [
            Link::make(__('SendGo Hub'))
                ->icon('bs.grid')
                ->route('orbit.sendgo.index'),
        ];
    }

    /**
     * @return Layout[]
     */
    public function layout(): array
    {
        return [
            LayoutFactory::table('campaigns', [
                TD::make('management_code', __('Code'))->cantHide(),
                TD::make('message_type', __('Type')),
                TD::make('status', __('Status')),
                TD::make('total_count', __('Total'))->alignRight(),
                TD::make('success_count', __('Success'))->alignRight(),
                TD::make('failed_count', __('Failed'))->alignRight(),
                TD::make('created_at_formatted', __('Created')),
                TD::make('url', __('View'))
                    ->render(fn (array $row) => '<a href="'.e((string) $row['url']).'">'.e(__('View')).'</a>'),
            ])->title($this->name() ?? ''),
        ];
    }
}
