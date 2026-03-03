<?php

use Emailit\EmailitClient;
use Emailit\Laravel\Facades\Emailit;
use Emailit\Service\DomainService;
use Emailit\Service\EmailService;

it('resolves to the EmailitClient', function () {
    $resolved = Emailit::getFacadeRoot();

    expect($resolved)->toBeInstanceOf(EmailitClient::class);
});

it('returns the correct facade accessor', function () {
    $accessor = (new class extends Emailit {
        public static function testAccessor(): string
        {
            return static::getFacadeAccessor();
        }
    })::testAccessor();

    expect($accessor)->toBe(EmailitClient::class);
});

it('proxies service property access via method-style calls', function () {
    expect(Emailit::emails())->toBeInstanceOf(EmailService::class)
        ->and(Emailit::domains())->toBeInstanceOf(DomainService::class);
});

it('returns the same service instance on repeated calls', function () {
    $first = Emailit::emails();
    $second = Emailit::emails();

    expect($first)->toBe($second);
});
