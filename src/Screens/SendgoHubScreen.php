<?php

declare(strict_types=1);

namespace CmsOrbit\Sendgo\Screens;

use CmsOrbit\Core\Screen\Action;
use CmsOrbit\Core\Screen\Actions\Button;
use CmsOrbit\Core\Screen\Actions\Link;
use CmsOrbit\Core\Screen\Layout;
use CmsOrbit\Core\Screen\Screen;
use CmsOrbit\Core\Screen\TD;
use CmsOrbit\Core\Support\Facades\Layout as LayoutFactory;
use CmsOrbit\Core\Support\Facades\Toast;
use CmsOrbit\Sendgo\Screens\Concerns\InteractsWithSendgoApi;
use CmsOrbit\Sendgo\Settings\SendgoSettings;
use Techigh\SendgoNotification\Exceptions\SendGoException;

class SendgoHubScreen extends Screen
{
    use InteractsWithSendgoApi;

    public function name(): ?string
    {
        return __('SendGo Hub');
    }

    public function description(): ?string
    {
        return __('Manage SendGo credentials, credits, templates, and delivery records.');
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
        $settings = app(SendgoSettings::class);
        $credits = null;
        $error = null;

        if ($settings->configured()) {
            try {
                $response = $this->client()->credits();
                $credits = data_get($response, 'data');
            } catch (SendGoException|\Throwable $exception) {
                $error = $exception->getMessage();
            }
        }

        return [
            'hub' => [
                'configured' => $settings->configured(),
                'connectionError' => $error,
                'credits' => is_array($credits) ? $credits : null,
                'links' => [
                    [
                        'title' => __('Settings'),
                        'description' => __('Configure SendGo API credentials and sender keys.'),
                        'url' => route('orbit.configs.group', ['group' => 'SendGo']),
                        'cta' => __('Open settings'),
                    ],
                    [
                        'title' => __('Templates'),
                        'description' => __('Review synced AlimTalk templates.'),
                        'url' => route('orbit.entities.sendgo-templates.index'),
                        'cta' => __('Open templates'),
                    ],
                    [
                        'title' => __('SMS campaigns'),
                        'description' => __('Browse SMS, LMS, and MMS delivery records.'),
                        'url' => route('orbit.sendgo.messages.index'),
                        'cta' => __('Open records'),
                    ],
                    [
                        'title' => __('AlimTalk campaigns'),
                        'description' => __('Browse Kakao AlimTalk delivery records.'),
                        'url' => route('orbit.sendgo.notices.index'),
                        'cta' => __('Open records'),
                    ],
                    [
                        'title' => __('FriendTalk campaigns'),
                        'description' => __('Browse Kakao FriendTalk delivery records.'),
                        'url' => route('orbit.sendgo.friends.index'),
                        'cta' => __('Open records'),
                    ],
                    [
                        'title' => __('Senders'),
                        'description' => __('Review approved SMS sender numbers.'),
                        'url' => route('orbit.sendgo.senders.index'),
                        'cta' => __('Open senders'),
                    ],
                    [
                        'title' => __('Kakao profiles'),
                        'description' => __('Review connected Kakao channel profiles.'),
                        'url' => route('orbit.sendgo.kakao-senders.index'),
                        'cta' => __('Open profiles'),
                    ],
                ],
            ],
        ];
    }

    /**
     * @return Action[]
     */
    public function commandBar(): array
    {
        return [
            Button::make(__('Refresh credits'))
                ->icon('bs.arrow-repeat')
                ->method('refreshCredits'),

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
            LayoutFactory::metrics([
                __('Usable credits') => 'hub.credits.usable_credits',
                __('Balance credits') => 'hub.credits.balance_credits',
                __('SMS available') => 'hub.credits.usable_counts.sms',
                __('AlimTalk available') => 'hub.credits.usable_counts.kakao_no',
            ])->title(__('Credits overview')),

            LayoutFactory::table('hub.links', [
                TD::make('title', __('Section'))->cantHide(),
                TD::make('description', __('Description')),
                TD::make('url', __('Open'))
                    ->render(fn (array $row) => '<a href="'.e((string) $row['url']).'">'.e((string) $row['cta']).'</a>'),
            ])->title(__('Quick links')),
        ];
    }

    public function refreshCredits()
    {
        if (! $this->settings()->configured()) {
            Toast::warning(__('SendGo credentials are not configured yet.'));

            return back();
        }

        try {
            $this->client()->credits();
        } catch (\Throwable $exception) {
            Toast::error($exception->getMessage());

            return back();
        }

        Toast::success(__('Credits refreshed.'));

        return back();
    }
}
