<?php

declare(strict_types=1);

namespace CmsOrbit\Sendgo\Models;

use Illuminate\Database\Eloquent\Model;

class SendgoDeliveryLog extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'store_uuid',
        'template_code',
        'channel',
        'recipient_count',
        'success',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'recipient_count' => 'integer',
            'success' => 'boolean',
        ];
    }
}
