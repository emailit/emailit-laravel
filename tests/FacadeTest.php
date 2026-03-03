<?php

use Emailit\EmailitClient;
use Emailit\Laravel\Facades\Emailit;

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
