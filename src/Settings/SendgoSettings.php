<?php

declare(strict_types=1);

namespace CmsOrbit\Sendgo\Settings;

use Illuminate\Support\Facades\Schema;

class SendgoSettings
{
    /**
     * @return array<string, string>
     */
    public function fields(): array
    {
        return [
            'url' => 'sendgo.url',
            'access_key' => 'sendgo.access_key',
            'secret_key' => 'sendgo.secret_key',
            'sms_sender_key' => 'sendgo.sms_sender_key',
            'kakao_sender_key' => 'sendgo.kakao_sender_key',
            'api_version' => 'sendgo.api_version',
            'phone_verification_template_code' => 'sendgo.phone_verification_template_code',
        ];
    }

    public function configured(): bool
    {
        return filled($this->accessKey()) && filled($this->secretKey()) && filled($this->url());
    }

    public function url(): ?string
    {
        return $this->value('url');
    }

    public function accessKey(): ?string
    {
        return $this->value('access_key');
    }

    public function secretKey(): ?string
    {
        return $this->value('secret_key');
    }

    public function smsSenderKey(): ?string
    {
        return $this->value('sms_sender_key');
    }

    public function kakaoSenderKey(): ?string
    {
        return $this->value('kakao_sender_key');
    }

    public function apiVersion(): string
    {
        return $this->value('api_version') ?? 'v2';
    }

    public function phoneVerificationTemplateCode(): ?string
    {
        return $this->value('phone_verification_template_code');
    }

    public function value(string $field): ?string
    {
        $envValue = $this->environmentValue($field);

        if ($envValue !== null) {
            return $envValue;
        }

        $configKey = $this->configKey($field);

        if (! $this->configTableExists()) {
            return $this->normalize(config($configKey));
        }

        return $this->normalize(orbit_config($configKey, config($configKey)));
    }

    public function environmentValue(string $field): ?string
    {
        return $this->normalize(config($this->environmentConfigKey($field)));
    }

    public function isManagedByEnvironment(string $field): bool
    {
        return $this->environmentValue($field) !== null;
    }

    public function displayValue(string $field): ?string
    {
        if ($this->isManagedByEnvironment($field) && in_array($field, ['access_key', 'secret_key'], true)) {
            return '***';
        }

        return $this->value($field);
    }

    /**
     * @return array<string, mixed>
     */
    public function toSendgoConfig(): array
    {
        return [
            'url' => $this->url(),
            'access_key' => $this->accessKey(),
            'secret_key' => $this->secretKey(),
            'sms_sender_key' => $this->smsSenderKey(),
            'kakao_sender_key' => $this->kakaoSenderKey(),
            'api_version' => $this->apiVersion(),
            'content_type' => 'application/json',
            'accept' => 'application/json',
        ];
    }

    protected function configKey(string $field): string
    {
        return $this->fields()[$field]
            ?? throw new \InvalidArgumentException("Unsupported SendGo setting [{$field}].");
    }

    protected function environmentConfigKey(string $field): string
    {
        return match ($field) {
            'url' => 'sendgo.url',
            'access_key' => 'sendgo.access_key',
            'secret_key' => 'sendgo.secret_key',
            'sms_sender_key' => 'sendgo.sms_sender_key',
            'kakao_sender_key' => 'sendgo.kakao_sender_key',
            'api_version' => 'sendgo.api_version',
            'phone_verification_template_code' => 'sendgo.phone_verification_template_code',
            default => $this->configKey($field),
        };
    }

    protected function normalize(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }

    protected function configTableExists(): bool
    {
        return Schema::hasTable('orbit_configs');
    }
}
