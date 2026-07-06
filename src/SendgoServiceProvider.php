<?php

declare(strict_types=1);

namespace CmsOrbit\Sendgo;

use CmsOrbit\Core\Auth\Phone\PhoneVerificationSender;
use CmsOrbit\Core\Foundation\Entity\EntityRegistry;
use CmsOrbit\Core\Foundation\ItemPermission;
use CmsOrbit\Core\Foundation\OrbitServiceProvider;
use CmsOrbit\Core\Screen\Actions\Menu;
use CmsOrbit\Core\Support\Facades\Config as OrbitConfig;
use CmsOrbit\Core\Support\Facades\Orbit;
use CmsOrbit\Core\Support\Locale;
use CmsOrbit\Sendgo\Api\SendgoAdminClient;
use CmsOrbit\Sendgo\Console\MigrateAuthSendgoConfigCommand;
use CmsOrbit\Sendgo\Console\SyncTemplatesCommand;
use CmsOrbit\Sendgo\Entities\SendgoTemplateEntity;
use CmsOrbit\Sendgo\Services\SendgoAlimtalkSender;
use CmsOrbit\Sendgo\Settings\SendgoSettings;
use CmsOrbit\Sendgo\Support\ConfigBridge;
use CmsOrbit\Sendgo\Support\ResolvesSendgoRoutes;
use Illuminate\Support\Facades\Route;

class SendgoServiceProvider extends OrbitServiceProvider
{
    use ResolvesSendgoRoutes;

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/sendgo.php', 'sendgo');

        $this->app->singleton(SendgoSettings::class);
        $this->app->singleton(SendgoAdminClient::class);
        $this->app->singleton(ConfigBridge::class);
        $this->app->singleton(SendgoAlimtalkSender::class);
        $this->app->singleton(PhoneVerificationSender::class, SendgoPhoneVerificationSender::class);

        $this->app->afterResolving(EntityRegistry::class, function (EntityRegistry $registry): void {
            $registry->registerClass([SendgoTemplateEntity::class]);
        });

        if ($this->app->resolved(EntityRegistry::class)) {
            $this->app->make(EntityRegistry::class)->registerClass([SendgoTemplateEntity::class]);
        }

        $this->loadJsonTranslationsFrom(__DIR__.'/../resources/lang');
        Locale::registerPath(__DIR__.'/../resources/lang');
    }

    public function boot(): void
    {
        $this->registerSendgoConfigGroup();

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        app(ConfigBridge::class)->apply();

        $this->registerRoutes();

        Orbit::registerSection('integrations', 'bs.send', __('Integrations'), 4500);

        Orbit::registerPermission(
            ItemPermission::group(__('SendGo'))
                ->addPermission('sendgo.dashboard', __('SendGo Hub'))
                ->addPermission('sendgo.campaigns', __('Campaign records'))
                ->addPermission('sendgo.senders', __('Senders & profiles'))
        );

        $this->commands([
            SyncTemplatesCommand::class,
            MigrateAuthSendgoConfigCommand::class,
        ]);

        $this->app->booted(function (): void {
            $this->registerSendgoMenu();
        });
    }

    protected function registerRoutes(): void
    {
        Route::domain($this->resolveOrbitDomain())
            ->prefix($this->resolveOrbitPrefix())
            ->as('orbit.')
            ->middleware(config('orbit.middleware.private'))
            ->group(__DIR__.'/../routes/orbit.php');
    }

    protected function registerSendgoMenu(): void
    {
        $hubUrl = Route::has('orbit.sendgo.index') ? route('orbit.sendgo.index') : '#';

        Orbit::registerMenuElement(
            Menu::make(__('SendGo'))
                ->icon('bs.send')
                ->url($hubUrl)
                ->sort(4520)
                ->set('section', __('Integrations'))
                ->set('sectionKey', 'integrations')
                ->set('permission', 'sendgo.dashboard')
                ->list([
                    Menu::make(__('Overview'))
                        ->icon('bs.grid')
                        ->url($hubUrl)
                        ->active([
                            $hubUrl,
                            $hubUrl.'?*',
                        ]),

                    Menu::make(__('Templates'))
                        ->icon('bs.file-earmark-richtext')
                        ->url($this->sendgoRoute('orbit.entities.sendgo-templates.index')),

                    Menu::make(__('SMS campaigns'))
                        ->icon('bs.chat-dots')
                        ->url($this->sendgoRoute('orbit.sendgo.messages.index')),

                    Menu::make(__('AlimTalk campaigns'))
                        ->icon('bs.chat-square-text')
                        ->url($this->sendgoRoute('orbit.sendgo.notices.index')),

                    Menu::make(__('FriendTalk campaigns'))
                        ->icon('bs.chat-heart')
                        ->url($this->sendgoRoute('orbit.sendgo.friends.index')),

                    Menu::make(__('Senders'))
                        ->icon('bs.telephone')
                        ->url($this->sendgoRoute('orbit.sendgo.senders.index')),

                    Menu::make(__('Kakao profiles'))
                        ->icon('bs.person-badge')
                        ->url($this->sendgoRoute('orbit.sendgo.kakao-senders.index')),
                ])
        );
    }

    protected function registerSendgoConfigGroup(): void
    {
        OrbitConfig::registerGroup('SendGo', 520, [
            'icon' => 'bs.send',
            'title' => __('SendGo'),
            'description' => __('SendGo API credentials, sender keys, and phone verification settings.'),
            'hubSection' => 'api',
        ]);

        OrbitConfig::registerSection('SendGo', 'credentials', [
            'title' => __('Connection'),
            'priority' => 10,
        ]);

        OrbitConfig::registerSection('SendGo', 'phone', [
            'title' => __('Phone verification'),
            'priority' => 20,
        ]);

        $settings = fn (): SendgoSettings => app(SendgoSettings::class);
        $lockDescription = ' '.__('When managed by .env, this field is locked and masked.');

        OrbitConfig::registerItem('SendGo', 'sendgo.url', 'input', 'https://api.sendgo.io', 'credentials', [
            'title' => __('SendGo API URL'),
            'description' => __('Base URL for the SendGo API.').$lockDescription,
            'display' => fn ($value = null, $item = null) => $settings()->displayValue('url'),
            'persist' => fn ($item = null) => ! $settings()->isManagedByEnvironment('url'),
            'field' => fn ($field, $item = null) => $settings()->isManagedByEnvironment('url')
                ? $field->disabled()->readonly()
                : $field,
        ]);

        OrbitConfig::registerItem('SendGo', 'sendgo.access_key', 'secret', null, 'credentials', [
            'title' => __('SendGo Access Key'),
            'encrypted' => true,
            'description' => __('API access key from the SendGo console.').$lockDescription,
            'display' => fn ($value = null, $item = null) => $settings()->displayValue('access_key'),
            'persist' => fn ($item = null) => ! $settings()->isManagedByEnvironment('access_key'),
            'field' => function ($field, $item = null) use ($settings) {
                $field->type('password')->autocomplete('new-password');

                if ($settings()->isManagedByEnvironment('access_key')) {
                    $field->disabled()->readonly();
                }

                return $field;
            },
        ]);

        OrbitConfig::registerItem('SendGo', 'sendgo.secret_key', 'secret', null, 'credentials', [
            'title' => __('SendGo Secret Key'),
            'encrypted' => true,
            'description' => __('API secret key from the SendGo console.').$lockDescription,
            'display' => fn ($value = null, $item = null) => $settings()->displayValue('secret_key'),
            'persist' => fn ($item = null) => ! $settings()->isManagedByEnvironment('secret_key'),
            'field' => function ($field, $item = null) use ($settings) {
                $field->type('password')->autocomplete('new-password');

                if ($settings()->isManagedByEnvironment('secret_key')) {
                    $field->disabled()->readonly();
                }

                return $field;
            },
        ]);

        OrbitConfig::registerItem('SendGo', 'sendgo.sms_sender_key', 'input', null, 'credentials', [
            'title' => __('SMS sender key'),
            'description' => __('Approved SMS sender UUID from SendGo.').$lockDescription,
            'display' => fn ($value = null, $item = null) => $settings()->displayValue('sms_sender_key'),
            'persist' => fn ($item = null) => ! $settings()->isManagedByEnvironment('sms_sender_key'),
            'field' => fn ($field, $item = null) => $settings()->isManagedByEnvironment('sms_sender_key')
                ? $field->disabled()->readonly()
                : $field,
        ]);

        OrbitConfig::registerItem('SendGo', 'sendgo.kakao_sender_key', 'input', null, 'credentials', [
            'title' => __('Kakao sender key'),
            'description' => __('Kakao channel sender key from SendGo.').$lockDescription,
            'display' => fn ($value = null, $item = null) => $settings()->displayValue('kakao_sender_key'),
            'persist' => fn ($item = null) => ! $settings()->isManagedByEnvironment('kakao_sender_key'),
            'field' => fn ($field, $item = null) => $settings()->isManagedByEnvironment('kakao_sender_key')
                ? $field->disabled()->readonly()
                : $field,
        ]);

        OrbitConfig::registerItem('SendGo', 'sendgo.api_version', 'select', 'v2', 'credentials', [
            'title' => __('API version'),
            'options' => [
                'v1' => 'v1',
                'v2' => 'v2',
            ],
            'display' => fn ($value = null, $item = null) => $settings()->displayValue('api_version'),
            'persist' => fn ($item = null) => ! $settings()->isManagedByEnvironment('api_version'),
            'field' => fn ($field, $item = null) => $settings()->isManagedByEnvironment('api_version')
                ? $field->disabled()->readonly()
                : $field,
        ]);

        OrbitConfig::registerItem('SendGo', 'sendgo.phone_verification_template_code', 'input', null, 'phone', [
            'title' => __('Phone verification AlimTalk template'),
            'description' => __('Template code used when phone verification channel is AlimTalk.'),
            'visibleWhen' => [
                'auth_methods.phone.enabled' => true,
            ],
        ]);
    }
}
