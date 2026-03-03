# Emailit Laravel

[![Tests](https://img.shields.io/github/actions/workflow/status/emailit/emailit-laravel/tests.yml?label=tests&style=for-the-badge&labelColor=111827)](https://github.com/emailit/emailit-laravel/actions)
[![Packagist Version](https://img.shields.io/packagist/v/emailit/emailit-laravel?style=for-the-badge&labelColor=111827)](https://packagist.org/packages/emailit/emailit-laravel)
[![License](https://img.shields.io/github/license/emailit/emailit-laravel?style=for-the-badge&labelColor=111827)](https://github.com/emailit/emailit-laravel/blob/main/LICENSE)

Laravel integration for the [Emailit](https://emailit.com) Email API. Provides a mail transport, a Facade, and full access to the [Emailit PHP SDK](https://github.com/emailit/emailit-php).

## Requirements

- PHP 8.1+
- Laravel 10, 11, or 12

## Installation

```bash
composer require emailit/emailit-laravel
```

The package auto-discovers its service provider — no manual registration needed.

## Configuration

Add your API key to `.env`:

```env
EMAILIT_API_KEY=your_api_key
```

Set Emailit as your mail transport in `.env`:

```env
MAIL_MAILER=emailit
```

Add the `emailit` mailer to your `config/mail.php` mailers array:

```php
'mailers' => [
    // ...

    'emailit' => [
        'transport' => 'emailit',
    ],
],
```

### Publish Config (optional)

```bash
php artisan vendor:publish --tag=emailit-config
```

This publishes `config/emailit.php` where you can customize the API base URL if needed.

## Usage

### Using Laravel Mail (recommended)

Once configured as your mail transport, all of Laravel's mail features work out of the box:

```php
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail;

Mail::to('user@example.com')->send(new WelcomeEmail($user));
```

With a Mailable:

```php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to Our App',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome',
        );
    }
}
```

### Using the Facade

The `Emailit` facade gives you direct access to the full [Emailit PHP SDK](https://github.com/emailit/emailit-php):

```php
use Emailit\Laravel\Facades\Emailit;

// Send an email via the API directly
$email = Emailit::emails->send([
    'from'    => 'hello@yourdomain.com',
    'to'      => ['user@example.com'],
    'subject' => 'Hello from Emailit',
    'html'    => '<h1>Welcome!</h1>',
]);

echo $email->id;
echo $email->status;
```

### Send with a Template

```php
use Emailit\Laravel\Facades\Emailit;

$email = Emailit::emails->send([
    'from'      => 'hello@yourdomain.com',
    'to'        => 'user@example.com',
    'template'  => 'welcome_email',
    'variables' => [
        'name'    => 'John Doe',
        'company' => 'Acme Inc',
    ],
]);
```

### Manage Domains

```php
use Emailit\Laravel\Facades\Emailit;

$domain = Emailit::domains->create(['name' => 'example.com']);
$domains = Emailit::domains->list();
```

### Manage Contacts

```php
use Emailit\Laravel\Facades\Emailit;

$contact = Emailit::contacts->create([
    'email' => 'user@example.com',
    'first_name' => 'John',
]);

$contacts = Emailit::contacts->list();
```

### Verify Email Addresses

```php
use Emailit\Laravel\Facades\Emailit;

$result = Emailit::emailVerifications->verify([
    'email' => 'test@example.com',
]);

echo $result->status; // valid
echo $result->risk;   // low
```

### All Available Services

The Facade exposes every service from the PHP SDK:

| Service | Property | Description |
|---------|----------|-------------|
| Emails | `Emailit::emails` | Send, list, get, cancel, retry emails |
| Domains | `Emailit::domains` | Create, verify, list, manage sending domains |
| API Keys | `Emailit::apiKeys` | Create, list, manage API keys |
| Audiences | `Emailit::audiences` | Create, list, manage audiences |
| Subscribers | `Emailit::subscribers` | Add, list, manage subscribers |
| Templates | `Emailit::templates` | Create, list, publish email templates |
| Suppressions | `Emailit::suppressions` | Create, list, manage suppressed addresses |
| Email Verifications | `Emailit::emailVerifications` | Verify email addresses |
| Email Verification Lists | `Emailit::emailVerificationLists` | Bulk email verification |
| Webhooks | `Emailit::webhooks` | Create, list, manage webhooks |
| Contacts | `Emailit::contacts` | Create, list, manage contacts |
| Events | `Emailit::events` | List and retrieve events |

## Error Handling

```php
use Emailit\Exceptions\AuthenticationException;
use Emailit\Exceptions\RateLimitException;
use Emailit\Exceptions\ApiErrorException;
use Emailit\Laravel\Facades\Emailit;

try {
    Emailit::emails->send([...]);
} catch (AuthenticationException $e) {
    // Invalid API key (401)
} catch (RateLimitException $e) {
    // Too many requests (429)
} catch (ApiErrorException $e) {
    // Any other API error
    echo $e->getHttpStatus();
}
```

## Dependency Injection

You can also inject the client directly instead of using the Facade:

```php
use Emailit\EmailitClient;

class EmailController extends Controller
{
    public function send(EmailitClient $emailit)
    {
        $email = $emailit->emails->send([
            'from'    => 'hello@yourdomain.com',
            'to'      => ['user@example.com'],
            'subject' => 'Hello',
            'html'    => '<p>Hi there!</p>',
        ]);

        return response()->json(['id' => $email->id]);
    }
}
```

## License

MIT — see [LICENSE](LICENSE) for details.
