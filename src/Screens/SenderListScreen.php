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

class SenderListScreen extends Screen
{
    use InteractsWithSendgoApi;

    public function name(): ?string
    {
        return __('SendGo Senders');
    }

    public function description(): ?string
    {
        return __('Approved SMS sender numbers registered in SendGo.');
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
                $response = $this->client()->listSenders();
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
                TD::make('sender_alias', __('Alias'))->cantHide(),
                TD::make('phone_number', __('Number')),
                TD::make('status', __('Status')),
                TD::make('primary_type', __('Type')),
                TD::make('uuid', __('Sender key')),
            ])->title(__('Senders')),
        ];
    }
}
