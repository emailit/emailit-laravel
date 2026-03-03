<?php

use Emailit\EmailitClient;
use Emailit\EmailitObject;
use Emailit\Laravel\Transport\EmailitTransport;
use Emailit\Service\EmailService;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

function mockEmailResponse(array $values = ['id' => 'msg_123']): EmailitObject
{
    return new EmailitObject($values);
}

function createTransportWithMock(Closure $expectations): EmailitTransport
{
    $emailService = Mockery::mock(EmailService::class);
    $expectations($emailService);

    $client = Mockery::mock(EmailitClient::class);
    $client->emails = $emailService;

    return new EmailitTransport($client);
}

it('converts to string as emailit', function () {
    $client = Mockery::mock(EmailitClient::class);
    $transport = new EmailitTransport($client);

    expect((string) $transport)->toBe('emailit');
});

it('sends a basic email with html body', function () {
    $transport = createTransportWithMock(function ($emailService) {
        $emailService->shouldReceive('send')
            ->once()
            ->withArgs(function (array $payload) {
                return $payload['from'] === 'sender@example.com'
                    && $payload['to'] === ['recipient@example.com']
                    && $payload['subject'] === 'Test Subject'
                    && $payload['html'] === '<h1>Hello</h1>'
                    && ! isset($payload['attachments']);
            })
            ->andReturn(mockEmailResponse());
    });

    $email = (new Email())
        ->from('sender@example.com')
        ->to('recipient@example.com')
        ->subject('Test Subject')
        ->html('<h1>Hello</h1>');

    $transport->send($email);
});

it('sends a text-only email', function () {
    $transport = createTransportWithMock(function ($emailService) {
        $emailService->shouldReceive('send')
            ->once()
            ->withArgs(function (array $payload) {
                return $payload['text'] === 'Hello plain'
                    && ! isset($payload['html']);
            })
            ->andReturn(mockEmailResponse());
    });

    $email = (new Email())
        ->from('sender@example.com')
        ->to('recipient@example.com')
        ->subject('Plain Text')
        ->text('Hello plain');

    $transport->send($email);
});

it('sends both html and text body', function () {
    $transport = createTransportWithMock(function ($emailService) {
        $emailService->shouldReceive('send')
            ->once()
            ->withArgs(function (array $payload) {
                return $payload['html'] === '<p>HTML</p>'
                    && $payload['text'] === 'Text';
            })
            ->andReturn(mockEmailResponse());
    });

    $email = (new Email())
        ->from('sender@example.com')
        ->to('recipient@example.com')
        ->subject('Both')
        ->html('<p>HTML</p>')
        ->text('Text');

    $transport->send($email);
});

it('includes cc recipients', function () {
    $transport = createTransportWithMock(function ($emailService) {
        $emailService->shouldReceive('send')
            ->once()
            ->withArgs(function (array $payload) {
                return $payload['cc'] === ['cc@example.com'];
            })
            ->andReturn(mockEmailResponse());
    });

    $email = (new Email())
        ->from('sender@example.com')
        ->to('recipient@example.com')
        ->cc('cc@example.com')
        ->subject('With CC')
        ->text('body');

    $transport->send($email);
});

it('includes bcc recipients', function () {
    $transport = createTransportWithMock(function ($emailService) {
        $emailService->shouldReceive('send')
            ->once()
            ->withArgs(function (array $payload) {
                return $payload['bcc'] === ['bcc@example.com'];
            })
            ->andReturn(mockEmailResponse());
    });

    $email = (new Email())
        ->from('sender@example.com')
        ->to('recipient@example.com')
        ->bcc('bcc@example.com')
        ->subject('With BCC')
        ->text('body');

    $transport->send($email);
});

it('includes reply-to address as array', function () {
    $transport = createTransportWithMock(function ($emailService) {
        $emailService->shouldReceive('send')
            ->once()
            ->withArgs(function (array $payload) {
                return $payload['reply_to'] === ['reply@example.com'];
            })
            ->andReturn(mockEmailResponse());
    });

    $email = (new Email())
        ->from('sender@example.com')
        ->to('recipient@example.com')
        ->replyTo('reply@example.com')
        ->subject('With Reply-To')
        ->text('body');

    $transport->send($email);
});

it('formats address with name', function () {
    $transport = createTransportWithMock(function ($emailService) {
        $emailService->shouldReceive('send')
            ->once()
            ->withArgs(function (array $payload) {
                return $payload['from'] === 'John Doe <john@example.com>';
            })
            ->andReturn(mockEmailResponse());
    });

    $email = (new Email())
        ->from(new Address('john@example.com', 'John Doe'))
        ->to('recipient@example.com')
        ->subject('Named From')
        ->text('body');

    $transport->send($email);
});

it('formats multiple to addresses', function () {
    $transport = createTransportWithMock(function ($emailService) {
        $emailService->shouldReceive('send')
            ->once()
            ->withArgs(function (array $payload) {
                return $payload['to'] === [
                    'Alice <alice@example.com>',
                    'bob@example.com',
                ];
            })
            ->andReturn(mockEmailResponse());
    });

    $email = (new Email())
        ->from('sender@example.com')
        ->to(new Address('alice@example.com', 'Alice'))
        ->addTo('bob@example.com')
        ->subject('Multi To')
        ->text('body');

    $transport->send($email);
});

it('sends attachments', function () {
    $transport = createTransportWithMock(function ($emailService) {
        $emailService->shouldReceive('send')
            ->once()
            ->withArgs(function (array $payload) {
                return isset($payload['attachments'])
                    && count($payload['attachments']) === 1
                    && $payload['attachments'][0]['filename'] === 'test.txt'
                    && $payload['attachments'][0]['content'] === base64_encode('file content')
                    && str_contains($payload['attachments'][0]['content_type'], 'text/plain');
            })
            ->andReturn(mockEmailResponse());
    });

    $email = (new Email())
        ->from('sender@example.com')
        ->to('recipient@example.com')
        ->subject('With Attachment')
        ->text('body')
        ->attach('file content', 'test.txt', 'text/plain');

    $transport->send($email);
});

it('sends multiple attachments', function () {
    $transport = createTransportWithMock(function ($emailService) {
        $emailService->shouldReceive('send')
            ->once()
            ->withArgs(function (array $payload) {
                return isset($payload['attachments'])
                    && count($payload['attachments']) === 2;
            })
            ->andReturn(mockEmailResponse());
    });

    $email = (new Email())
        ->from('sender@example.com')
        ->to('recipient@example.com')
        ->subject('Multi Attachments')
        ->text('body')
        ->attach('content1', 'file1.txt', 'text/plain')
        ->attach('content2', 'file2.pdf', 'application/pdf');

    $transport->send($email);
});

it('excludes attachments key when no attachments', function () {
    $transport = createTransportWithMock(function ($emailService) {
        $emailService->shouldReceive('send')
            ->once()
            ->withArgs(function (array $payload) {
                return ! array_key_exists('attachments', $payload);
            })
            ->andReturn(mockEmailResponse());
    });

    $email = (new Email())
        ->from('sender@example.com')
        ->to('recipient@example.com')
        ->subject('No Attachments')
        ->text('body');

    $transport->send($email);
});

it('excludes cc when not set', function () {
    $transport = createTransportWithMock(function ($emailService) {
        $emailService->shouldReceive('send')
            ->once()
            ->withArgs(function (array $payload) {
                return ! array_key_exists('cc', $payload);
            })
            ->andReturn(mockEmailResponse());
    });

    $email = (new Email())
        ->from('sender@example.com')
        ->to('recipient@example.com')
        ->subject('No CC')
        ->text('body');

    $transport->send($email);
});

it('excludes bcc when not set', function () {
    $transport = createTransportWithMock(function ($emailService) {
        $emailService->shouldReceive('send')
            ->once()
            ->withArgs(function (array $payload) {
                return ! array_key_exists('bcc', $payload);
            })
            ->andReturn(mockEmailResponse());
    });

    $email = (new Email())
        ->from('sender@example.com')
        ->to('recipient@example.com')
        ->subject('No BCC')
        ->text('body');

    $transport->send($email);
});

it('excludes reply_to when not set', function () {
    $transport = createTransportWithMock(function ($emailService) {
        $emailService->shouldReceive('send')
            ->once()
            ->withArgs(function (array $payload) {
                return ! array_key_exists('reply_to', $payload);
            })
            ->andReturn(mockEmailResponse());
    });

    $email = (new Email())
        ->from('sender@example.com')
        ->to('recipient@example.com')
        ->subject('No Reply-To')
        ->text('body');

    $transport->send($email);
});

it('adds X-Emailit-ID header when response has id', function () {
    $transport = createTransportWithMock(function ($emailService) {
        $emailService->shouldReceive('send')
            ->once()
            ->andReturn(mockEmailResponse(['id' => 'msg_header_123']));
    });

    $email = (new Email())
        ->from('sender@example.com')
        ->to('recipient@example.com')
        ->subject('Header Test')
        ->text('body');

    $sentMessage = $transport->send($email);

    expect($sentMessage->getOriginalMessage()->getHeaders()->get('X-Emailit-ID')?->getBodyAsString())
        ->toBe('msg_header_123');
});

it('does not add header when response has no id', function () {
    $transport = createTransportWithMock(function ($emailService) {
        $emailService->shouldReceive('send')
            ->once()
            ->andReturn(mockEmailResponse([]));
    });

    $email = (new Email())
        ->from('sender@example.com')
        ->to('recipient@example.com')
        ->subject('No Header Test')
        ->text('body');

    $sentMessage = $transport->send($email);

    expect($sentMessage->getOriginalMessage()->getHeaders()->get('X-Emailit-ID'))->toBeNull();
});

it('sends multiple reply-to addresses as array', function () {
    $transport = createTransportWithMock(function ($emailService) {
        $emailService->shouldReceive('send')
            ->once()
            ->withArgs(function (array $payload) {
                return $payload['reply_to'] === [
                    'reply1@example.com',
                    'reply2@example.com',
                ];
            })
            ->andReturn(mockEmailResponse());
    });

    $email = (new Email())
        ->from('sender@example.com')
        ->to('recipient@example.com')
        ->replyTo('reply1@example.com')
        ->addReplyTo('reply2@example.com')
        ->subject('Multi Reply-To')
        ->text('body');

    $transport->send($email);
});

it('sends multiple reply-to addresses with names', function () {
    $transport = createTransportWithMock(function ($emailService) {
        $emailService->shouldReceive('send')
            ->once()
            ->withArgs(function (array $payload) {
                return $payload['reply_to'] === [
                    'Support <support@example.com>',
                    'sales@example.com',
                ];
            })
            ->andReturn(mockEmailResponse());
    });

    $email = (new Email())
        ->from('sender@example.com')
        ->to('recipient@example.com')
        ->replyTo(new Address('support@example.com', 'Support'))
        ->addReplyTo('sales@example.com')
        ->subject('Multi Named Reply-To')
        ->text('body');

    $transport->send($email);
});

it('formats single reply-to with name as array', function () {
    $transport = createTransportWithMock(function ($emailService) {
        $emailService->shouldReceive('send')
            ->once()
            ->withArgs(function (array $payload) {
                return $payload['reply_to'] === ['Support <support@example.com>'];
            })
            ->andReturn(mockEmailResponse());
    });

    $email = (new Email())
        ->from('sender@example.com')
        ->to('recipient@example.com')
        ->replyTo(new Address('support@example.com', 'Support'))
        ->subject('Named Reply-To')
        ->text('body');

    $transport->send($email);
});

it('includes custom headers in payload', function () {
    $transport = createTransportWithMock(function ($emailService) {
        $emailService->shouldReceive('send')
            ->once()
            ->withArgs(function (array $payload) {
                return isset($payload['headers'])
                    && $payload['headers']['X-Custom-Header'] === 'custom-value'
                    && $payload['headers']['X-Another'] === 'another-value';
            })
            ->andReturn(mockEmailResponse());
    });

    $email = (new Email())
        ->from('sender@example.com')
        ->to('recipient@example.com')
        ->subject('With Headers')
        ->text('body');

    $email->getHeaders()->addTextHeader('X-Custom-Header', 'custom-value');
    $email->getHeaders()->addTextHeader('X-Another', 'another-value');

    $transport->send($email);
});

it('excludes headers key when no custom headers', function () {
    $transport = createTransportWithMock(function ($emailService) {
        $emailService->shouldReceive('send')
            ->once()
            ->withArgs(function (array $payload) {
                return ! array_key_exists('headers', $payload);
            })
            ->andReturn(mockEmailResponse());
    });

    $email = (new Email())
        ->from('sender@example.com')
        ->to('recipient@example.com')
        ->subject('No Custom Headers')
        ->text('body');

    $transport->send($email);
});

it('skips standard headers from custom headers', function () {
    $transport = createTransportWithMock(function ($emailService) {
        $emailService->shouldReceive('send')
            ->once()
            ->withArgs(function (array $payload) {
                if (! isset($payload['headers'])) {
                    return false;
                }

                $headerKeys = array_map('strtolower', array_keys($payload['headers']));

                $skipped = ['from', 'to', 'cc', 'bcc', 'subject', 'content-type', 'sender', 'reply-to'];
                foreach ($skipped as $name) {
                    if (in_array($name, $headerKeys, true)) {
                        return false;
                    }
                }

                return isset($payload['headers']['X-Keep-This']);
            })
            ->andReturn(mockEmailResponse());
    });

    $email = (new Email())
        ->from('sender@example.com')
        ->to('recipient@example.com')
        ->cc('cc@example.com')
        ->bcc('bcc@example.com')
        ->replyTo('reply@example.com')
        ->subject('Headers Filter')
        ->text('body');

    $email->getHeaders()->addTextHeader('X-Keep-This', 'yes');

    $transport->send($email);
});

it('sends a full email with all fields populated', function () {
    $transport = createTransportWithMock(function ($emailService) {
        $emailService->shouldReceive('send')
            ->once()
            ->withArgs(function (array $payload) {
                return $payload['from'] === 'Sender <sender@example.com>'
                    && $payload['to'] === ['recipient@example.com']
                    && $payload['subject'] === 'Full Email'
                    && $payload['html'] === '<p>HTML</p>'
                    && $payload['text'] === 'Text'
                    && $payload['cc'] === ['cc@example.com']
                    && $payload['bcc'] === ['bcc@example.com']
                    && $payload['reply_to'] === ['reply@example.com']
                    && count($payload['attachments']) === 1;
            })
            ->andReturn(mockEmailResponse(['id' => 'msg_full']));
    });

    $email = (new Email())
        ->from(new Address('sender@example.com', 'Sender'))
        ->to('recipient@example.com')
        ->cc('cc@example.com')
        ->bcc('bcc@example.com')
        ->replyTo('reply@example.com')
        ->subject('Full Email')
        ->html('<p>HTML</p>')
        ->text('Text')
        ->attach('data', 'file.txt', 'text/plain');

    $transport->send($email);
});
