<?php

declare(strict_types=1);

namespace CmsOrbit\Sendgo\Screens;

use CmsOrbit\Core\Screen\Action;
use CmsOrbit\Core\Screen\Actions\Button;
use CmsOrbit\Core\Screen\Actions\Link;
use CmsOrbit\Core\Screen\Layout;
use CmsOrbit\Core\Screen\Screen;
use CmsOrbit\Core\Support\Facades\Layout as LayoutFactory;
use CmsOrbit\Core\Support\Facades\Toast;
use CmsOrbit\Sendgo\Services\SendgoHubDashboard;
use CmsOrbit\Sendgo\Settings\SendgoSettings;

class SendgoHubScreen extends Screen
{
    public function name(): ?string
    {
        return __('SendGo Hub');
    }

    public function description(): ?string
    {
        return __('Message delivery overview, channel mix, senders, and synced templates.');
    }

    public function permission(): ?iterable
    {
        return ['sendgo.dashboard'];
    }

    /**
     * @return array<string, mixed>
     */
    public function query(): array
    {
        return [
            'hub' => app(SendgoHubDashboard::class)->build(),
        ];
    }

    /**
     * @return Action[]
     */
    public function commandBar(): array
    {
        return [
            Button::make(__('Refresh dashboard'))
                ->icon('bs.arrow-repeat')
                ->method('refreshDashboard'),

            Link::make(__('Settings'))
                ->icon('bs.gear')
                ->href(route('orbit.configs.group', ['group' => 'SendGo'])),
        ];
    }

    /**
     * @return Layout[]
     */
    public function layout(): array
    {
        return [
            LayoutFactory::component('sendgo-hub-dashboard'),
        ];
    }

    public function refreshDashboard()
    {
        if (! app(SendgoSettings::class)->configured()) {
            Toast::warning(__('SendGo credentials are not configured yet.'));

            return back();
        }

        Toast::success(__('Dashboard refreshed.'));

        return back();
    }
}
