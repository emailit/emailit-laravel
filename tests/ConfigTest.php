<?php

it('has default api_key as null', function () {
    config()->set('emailit.api_key', null);

    expect(config('emailit.api_key'))->toBeNull();
});

it('has default api_base url', function () {
    expect(config('emailit.api_base'))->toBe('https://api.emailit.com/v2');
});

it('allows overriding api_key', function () {
    config()->set('emailit.api_key', 'custom-key');

    expect(config('emailit.api_key'))->toBe('custom-key');
});

it('allows overriding api_base', function () {
    config()->set('emailit.api_base', 'https://custom.api.com/v1');

    expect(config('emailit.api_base'))->toBe('https://custom.api.com/v1');
});

it('merges config from package', function () {
    expect(config('emailit'))->toBeArray()
        ->toHaveKeys(['api_key', 'api_base']);
});
