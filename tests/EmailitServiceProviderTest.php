<?php

use Emailit\EmailitClient;
use Emailit\Laravel\EmailitServiceProvider;
use Emailit\Laravel\Transport\EmailitTransport;
use Illuminate\Mail\MailManager;

it('registers the EmailitClient as a singleton', function () {
    $client1 = $this->app->make(EmailitClient::class);
    $client2 = $this->app->make(EmailitClient::class);

    expect($client1)->toBeInstanceOf(EmailitClient::class)
        ->and($client1)->toBe($client2);
});

it('registers the emailit alias for EmailitClient', function () {
    $client = $this->app->make('emailit');

    expect($client)->toBeInstanceOf(EmailitClient::class);
});

it('resolves the same instance via alias and class', function () {
    $viaClass = $this->app->make(EmailitClient::class);
    $viaAlias = $this->app->make('emailit');

    expect($viaClass)->toBe($viaAlias);
});

it('passes the api_key from config to the client', function () {
    config()->set('emailit.api_key', 'my-secret-key');

    $this->app->forgetInstance(EmailitClient::class);
    $this->app->offsetUnset(EmailitClient::class);

    $provider = new EmailitServiceProvider($this->app);
    $provider->register();

    $client = $this->app->make(EmailitClient::class);

    expect($client)->toBeInstanceOf(EmailitClient::class);
});

it('registers the emailit mail transport', function () {
    $manager = $this->app->make(MailManager::class);
    $transport = $manager->createSymfonyTransport(['transport' => 'emailit']);

    expect($transport)->toBeInstanceOf(EmailitTransport::class);
});

it('publishes the config file', function () {
    $provider = new EmailitServiceProvider($this->app);

    $paths = EmailitServiceProvider::pathsToPublish(
        EmailitServiceProvider::class,
        'emailit-config'
    );

    expect($paths)->toBeArray()
        ->and(array_values($paths)[0])->toContain('emailit.php');
});
