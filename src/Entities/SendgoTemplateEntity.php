<?php

declare(strict_types=1);

namespace CmsOrbit\Sendgo\Entities;

use CmsOrbit\Core\Foundation\Entity\Entity;
use CmsOrbit\Sendgo\Actions\SyncTemplatesAction;
use CmsOrbit\Sendgo\Models\SendgoTemplate;
use CmsOrbit\Core\Screen\Sight;
use CmsOrbit\Core\Screen\TD;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SendgoTemplateEntity extends Entity
{
    public function model(): string
    {
        return SendgoTemplate::class;
    }

    public function label(): string
    {
        return __('SendGo Templates');
    }

    public function singularLabel(): string
    {
        return __('SendGo Template');
    }

    public function icon(): string
    {
        return 'bs.file-earmark-richtext';
    }

    public function section(): string
    {
        return __('SendGo');
    }

    public function sectionKey(): string
    {
        return 'integrations';
    }

    public function menuParent(): ?string
    {
        return 'sendgo-records';
    }

    public function sort(): int
    {
        return 4520;
    }

    public function crud(): array
    {
        return ['list', 'view'];
    }

    public function query(): Builder
    {
        return parent::query()->latest('synced_at');
    }

    /**
     * @return array<int, class-string>
     */
    public function actions(): array
    {
        return [
            SyncTemplatesAction::class,
        ];
    }

    public function fields(): array
    {
        return [];
    }

    /**
     * @return TD[]
     */
    public function columns(): array
    {
        return [
            TD::make('template_code', __('Template code'))->cantHide(),
            TD::make('template_name', __('Name'))->cantHide(),
            TD::make('status', __('Status')),
            TD::make('inspection_status', __('Inspection')),
            TD::make('synced_at', __('Synced at'))->sort(),
        ];
    }

    /**
     * @return Sight[]
     */
    public function legend(): array
    {
        return [
            Sight::make('template_code', __('Template code')),
            Sight::make('template_name', __('Name')),
            Sight::make('status', __('Status')),
            Sight::make('inspection_status', __('Inspection')),
            Sight::make('kakao_sender_id', __('Kakao sender')),
            Sight::make('template_content', __('Content')),
            Sight::make('synced_at', __('Synced at')),
        ];
    }

    public function presenter(Model $model): array
    {
        return [
            'label' => $this->singularLabel(),
            'title' => (string) ($model->getAttribute('template_name') ?: $model->getAttribute('template_code')),
            'subTitle' => (string) $model->getAttribute('template_code'),
            'url' => $this->showUrl($model),
        ];
    }
}
