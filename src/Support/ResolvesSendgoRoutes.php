<?php

declare(strict_types=1);

namespace CmsOrbit\Sendgo\Support;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

trait ResolvesSendgoRoutes
{
    protected function resolveOrbitDomain(): ?string
    {
        return match (config('orbit.access.mode', 'subdomain')) {
            'subdomain' => $this->resolveOrbitSubdomainHost(),
            'domain' => config('orbit.access.domain'),
            default => null,
        };
    }

    protected function resolveOrbitSubdomainHost(): ?string
    {
        $label = (string) config('orbit.access.subdomain', 'orbit');
        $host = parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'localhost';

        return $label.'.'.$host;
    }

    protected function resolveOrbitPrefix(): string
    {
        if (config('orbit.access.mode') === 'path') {
            return Str::start((string) config('orbit.access.prefix', 'settings'), '/');
        }

        return '/';
    }

    protected function sendgoRoute(string $name, array $parameters = []): string
    {
        return Route::has($name) ? route($name, $parameters) : '#';
    }
}
