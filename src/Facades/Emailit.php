<?php

namespace Emailit\Laravel\Facades;

use Emailit\EmailitClient;
use Emailit\Service\ApiKeyService;
use Emailit\Service\AudienceService;
use Emailit\Service\ContactService;
use Emailit\Service\DomainService;
use Emailit\Service\EmailService;
use Emailit\Service\EmailVerificationListService;
use Emailit\Service\EmailVerificationService;
use Emailit\Service\EventService;
use Emailit\Service\SubscriberService;
use Emailit\Service\SuppressionService;
use Emailit\Service\TemplateService;
use Emailit\Service\WebhookService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static EmailService emails()
 * @method static DomainService domains()
 * @method static ApiKeyService apiKeys()
 * @method static AudienceService audiences()
 * @method static SubscriberService subscribers()
 * @method static TemplateService templates()
 * @method static SuppressionService suppressions()
 * @method static EmailVerificationService emailVerifications()
 * @method static EmailVerificationListService emailVerificationLists()
 * @method static WebhookService webhooks()
 * @method static ContactService contacts()
 * @method static EventService events()
 *
 * @see EmailitClient
 */
class Emailit extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return EmailitClient::class;
    }

    public static function __callStatic($method, $args)
    {
        $instance = static::getFacadeRoot();

        if (! $instance) {
            throw new \RuntimeException('A facade root has not been set.');
        }

        if (method_exists($instance, $method) || method_exists($instance, '__call')) {
            return $instance->$method(...$args);
        }

        // Fallback to property access for older SDK versions using __get
        if (empty($args)) {
            return $instance->$method;
        }

        return $instance->$method(...$args);
    }
}
