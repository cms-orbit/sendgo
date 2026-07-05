<?php

declare(strict_types=1);

namespace CmsOrbit\Sendgo\Console;

use CmsOrbit\Sendgo\Services\TemplateSyncService;
use Illuminate\Console\Command;

class SyncTemplatesCommand extends Command
{
    protected $signature = 'sendgo:sync-templates';

    protected $description = 'Sync AlimTalk templates from SendGo into the local database';

    public function handle(TemplateSyncService $syncService): int
    {
        try {
            $count = $syncService->sync();
        } catch (\Throwable $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->components->info(__('Synced :count template(s).', ['count' => $count]));

        return self::SUCCESS;
    }
}
