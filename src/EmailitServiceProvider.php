<?php

namespace Emailit\Laravel;

use Emailit\EmailitClient;
use Emailit\Laravel\Transport\EmailitTransport;
use Illuminate\Mail\MailManager;
use Illuminate\Support\ServiceProvider;

class EmailitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/emailit.php', 'emailit');

        $this->app->singleton(EmailitClient::class, function ($app) {
            $config = $app['config']['emailit'];

            return new EmailitClient([
                'api_key' => $config['api_key'],
                'api_base' => $config['api_base'] ?? EmailitClient::DEFAULT_API_BASE,
            ]);
        });

        $this->app->alias(EmailitClient::class, 'emailit');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/emailit.php' => config_path('emailit.php'),
        ], 'emailit-config');

        $this->app->make(MailManager::class)->extend('emailit', function () {
            return new EmailitTransport(
                $this->app->make(EmailitClient::class),
            );
        });
    }
}
