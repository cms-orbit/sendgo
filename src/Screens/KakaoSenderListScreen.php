<?php

declare(strict_types=1);

namespace CmsOrbit\Sendgo\Screens;

use CmsOrbit\Core\Screen\Action;
use CmsOrbit\Core\Screen\Actions\Link;
use CmsOrbit\Core\Screen\Layout;
use CmsOrbit\Core\Screen\Screen;
use CmsOrbit\Core\Screen\TD;
use CmsOrbit\Core\Support\Facades\Layout as LayoutFactory;
use CmsOrbit\Sendgo\Screens\Concerns\InteractsWithSendgoApi;

class KakaoSenderListScreen extends Screen
{
    use InteractsWithSendgoApi;

    public function name(): ?string
    {
        return __('Kakao Profiles');
    }

    public function description(): ?string
    {
        return __('Kakao channel profiles connected to SendGo.');
    }

    public function permission(): ?iterable
    {
        return ['sendgo.senders'];
    }

    /**
     * @return array<string, mixed>
     */
    public function query(): array
    {
        $error = null;
        $senders = [];

        if ($this->settings()->configured()) {
            try {
                $response = $this->client()->listKakaoSenders();
                $senders = data_get($response, 'data.senders', []);
            } catch (\Throwable $exception) {
                $error = $exception->getMessage();
            }
        }

        return [
            'error' => $error,
            'senders' => is_array($senders) ? $senders : [],
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
            LayoutFactory::table('senders', [
                TD::make('name', __('Channel'))->cantHide(),
                TD::make('yellow_id', __('Yellow ID')),
                TD::make('sender_key', __('Sender key')),
                TD::make('status', __('Status')),
                TD::make('alim_talk', __('AlimTalk')),
            ])->title(__('Kakao profiles')),
        ];
    }
}
