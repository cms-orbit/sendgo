<?php

declare(strict_types=1);

namespace CmsOrbit\Sendgo\Console;

use CmsOrbit\Core\Config\Models\OrbitConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class MigrateAuthSendgoConfigCommand extends Command
{
    protected $signature = 'sendgo:migrate-config';

    protected $description = 'Migrate legacy auth_sendgo.* orbit config keys to sendgo.*';

    /**
     * @var array<string, string>
     */
    protected array $map = [
        'auth_sendgo.access_key' => 'sendgo.access_key',
        'auth_sendgo.secret_key' => 'sendgo.secret_key',
        'auth_sendgo.endpoint' => 'sendgo.url',
        'auth_sendgo.sender_key' => 'sendgo.sms_sender_key',
        'auth_sendgo.kakao_sender_key' => 'sendgo.kakao_sender_key',
        'auth_sendgo.api_version' => 'sendgo.api_version',
    ];

    public function handle(): int
    {
        if (! Schema::hasTable('orbit_configs')) {
            $this->components->warn(__('orbit_configs table does not exist. Nothing to migrate.'));

            return self::SUCCESS;
        }

        $migrated = 0;

        foreach ($this->map as $from => $to) {
            $record = OrbitConfig::query()->where('key', $from)->first();

            if ($record === null) {
                continue;
            }

            OrbitConfig::query()->updateOrCreate(
                ['key' => $to],
                ['value' => $record->value]
            );

            $record->delete();
            $migrated++;
        }

        $this->components->info(__('Migrated :count config key(s).', ['count' => $migrated]));

        return self::SUCCESS;
    }
}
