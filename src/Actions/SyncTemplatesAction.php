<?php

declare(strict_types=1);

namespace CmsOrbit\Sendgo\Actions;

use CmsOrbit\Core\Crud\Action;
use CmsOrbit\Core\Screen\Actions\Button;
use CmsOrbit\Core\Support\Color;
use CmsOrbit\Core\Support\Facades\Toast;
use CmsOrbit\Sendgo\Services\TemplateSyncService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class SyncTemplatesAction extends Action
{
    public function button(): Button
    {
        return Button::make(__('Sync templates'))
            ->icon('bs.arrow-repeat')
            ->type(Color::PRIMARY)
            ->confirm(__('Pull the latest AlimTalk templates from SendGo?'));
    }

    /**
     * @param  Collection<int, Model>  $models
     */
    public function handle(Collection $models)
    {
        try {
            $count = app(TemplateSyncService::class)->sync();
        } catch (\Throwable $exception) {
            Toast::error($exception->getMessage());

            return back();
        }

        Toast::success(__('Synced :count template(s).', ['count' => $count]));

        return back();
    }
}
