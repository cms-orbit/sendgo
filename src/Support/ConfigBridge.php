<?php

declare(strict_types=1);

namespace CmsOrbit\Sendgo\Support;

use CmsOrbit\Sendgo\Settings\SendgoSettings;

class ConfigBridge
{
    public function __construct(private readonly SendgoSettings $settings) {}

    public function apply(): void
    {
        config(['sendgo' => array_merge(config('sendgo', []), $this->settings->toSendgoConfig())]);
    }
}
