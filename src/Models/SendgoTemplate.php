<?php

declare(strict_types=1);

namespace CmsOrbit\Sendgo\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $uuid
 * @property string $template_code
 * @property string|null $template_name
 * @property string|null $status
 * @property string|null $inspection_status
 * @property string|null $kakao_sender_id
 * @property string|null $template_content
 * @property array<string, mixed>|null $buttons
 * @property array<string, mixed>|null $payload
 * @property \Illuminate\Support\Carbon|null $synced_at
 */
class SendgoTemplate extends Model
{
    protected $table = 'sendgo_templates';

    protected $fillable = [
        'uuid',
        'template_code',
        'template_name',
        'status',
        'inspection_status',
        'kakao_sender_id',
        'template_content',
        'buttons',
        'payload',
        'synced_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'buttons' => 'array',
            'payload' => 'array',
            'synced_at' => 'datetime',
        ];
    }
}
