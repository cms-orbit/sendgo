<?php

declare(strict_types=1);

namespace CmsOrbit\Sendgo\Screens\Campaigns;

use CmsOrbit\Core\Screen\Action;
use CmsOrbit\Core\Screen\Actions\Link;
use CmsOrbit\Core\Screen\Layout;
use CmsOrbit\Core\Screen\Screen;
use CmsOrbit\Core\Screen\Sight;
use CmsOrbit\Core\Screen\TD;
use CmsOrbit\Core\Support\Facades\Layout as LayoutFactory;
use CmsOrbit\Sendgo\Screens\Concerns\InteractsWithSendgoApi;

abstract class AbstractCampaignViewScreen extends Screen
{
    use InteractsWithSendgoApi;

    abstract protected function listRouteName(): string;

    /**
     * @return array<string, mixed>
     */
    abstract protected function fetchCampaign(string $campaignId): array;

    public function permission(): ?iterable
    {
        return ['sendgo.campaigns'];
    }

    /**
     * @return array<string, mixed>
     */
    public function query(string $id): array
    {
        $error = null;
        $campaign = [];
        $items = [];

        if ($this->settings()->configured()) {
            try {
                $response = $this->fetchCampaign($id);
                $campaign = data_get($response, 'data.campaign', []);
                $items = is_array($campaign['items'] ?? null) ? $campaign['items'] : [];
            } catch (\Throwable $exception) {
                $error = $exception->getMessage();
            }
        }

        return [
            'campaign' => is_array($campaign) ? $campaign : [],
            'items' => $items,
            'error' => $error,
        ];
    }

    /**
     * @return Action[]
     */
    public function commandBar(): array
    {
        return [
            Link::make(__('Back to list'))
                ->icon('bs.arrow-left')
                ->route($this->listRouteName()),
        ];
    }

    /**
     * @return Layout[]
     */
    public function layout(): array
    {
        return [
            LayoutFactory::legend('campaign', [
                Sight::make('uuid', __('Campaign ID')),
                Sight::make('management_code', __('Management code')),
                Sight::make('status', __('Status')),
                Sight::make('message_type', __('Message type')),
                Sight::make('template_code', __('Template code')),
                Sight::make('total_count', __('Total recipients')),
                Sight::make('success_count', __('Success')),
                Sight::make('failed_count', __('Failed')),
                Sight::make('content', __('Content')),
            ]),

            LayoutFactory::table('items', [
                TD::make('name', __('Name'))->cantHide(),
                TD::make('phone_e164', __('Phone')),
                TD::make('status', __('Status')),
                TD::make('sms_status', __('SMS status')),
            ])->title(__('Recipients')),
        ];
    }
}
