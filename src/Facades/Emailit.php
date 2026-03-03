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
 * @property-read EmailService $emails
 * @property-read DomainService $domains
 * @property-read ApiKeyService $apiKeys
 * @property-read AudienceService $audiences
 * @property-read SubscriberService $subscribers
 * @property-read TemplateService $templates
 * @property-read SuppressionService $suppressions
 * @property-read EmailVerificationService $emailVerifications
 * @property-read EmailVerificationListService $emailVerificationLists
 * @property-read WebhookService $webhooks
 * @property-read ContactService $contacts
 * @property-read EventService $events
 *
 * @see EmailitClient
 */
class Emailit extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return EmailitClient::class;
    }
}
