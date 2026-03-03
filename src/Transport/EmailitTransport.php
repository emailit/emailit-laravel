<?php

namespace Emailit\Laravel\Transport;

use Emailit\EmailitClient;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\MessageConverter;
use Symfony\Component\Mime\Part\DataPart;

class EmailitTransport extends AbstractTransport
{
    public function __construct(
        private readonly EmailitClient $client,
    ) {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $payload = [
            'from' => $this->formatAddress($email->getFrom()[0]),
            'to' => $this->formatAddresses($email->getTo()),
            'subject' => $email->getSubject(),
        ];

        if ($email->getHtmlBody()) {
            $payload['html'] = (string) $email->getHtmlBody();
        }

        if ($email->getTextBody()) {
            $payload['text'] = (string) $email->getTextBody();
        }

        if ($cc = $email->getCc()) {
            $payload['cc'] = $this->formatAddresses($cc);
        }

        if ($bcc = $email->getBcc()) {
            $payload['bcc'] = $this->formatAddresses($bcc);
        }

        if ($replyTo = $email->getReplyTo()) {
            $payload['reply_to'] = $this->formatAddresses($replyTo);
        }

        if ($headers = $this->getCustomHeaders($email)) {
            $payload['headers'] = $headers;
        }

        $payload['attachments'] = $this->getAttachments($email);

        if (empty($payload['attachments'])) {
            unset($payload['attachments']);
        }

        $response = $this->client->emails->send($payload);

        if (isset($response->id)) {
            $message->getOriginalMessage()->getHeaders()->addTextHeader('X-Emailit-Email-ID', $response->id);
        }
    }

    private function formatAddress(Address $address): string
    {
        if ($address->getName()) {
            return sprintf('%s <%s>', $address->getName(), $address->getAddress());
        }

        return $address->getAddress();
    }

    /**
     * @param Address[] $addresses
     * @return string[]
     */
    private function formatAddresses(array $addresses): array
    {
        return array_map(fn (Address $address) => $this->formatAddress($address), $addresses);
    }

    private const SKIP_HEADERS = [
        'from',
        'to',
        'cc',
        'bcc',
        'subject',
        'content-type',
        'sender',
        'reply-to',
    ];

    private function getCustomHeaders(Email $email): array
    {
        $headers = [];

        foreach ($email->getHeaders()->all() as $header) {
            $name = strtolower($header->getName());

            if (in_array($name, self::SKIP_HEADERS, true)) {
                continue;
            }

            $headers[$header->getName()] = $header->getBodyAsString();
        }

        return $headers;
    }

    private function getAttachments(Email $email): array
    {
        $attachments = [];

        foreach ($email->getAttachments() as $attachment) {
            $headers = $attachment->getPreparedHeaders();

            $attachments[] = [
                'filename' => $headers->getHeaderParameter('Content-Disposition', 'filename') ?? 'attachment',
                'content' => base64_encode($attachment->getBody()),
                'content_type' => $headers->get('Content-Type')?->getBodyAsString() ?? 'application/octet-stream',
            ];
        }

        return $attachments;
    }

    public function __toString(): string
    {
        return 'emailit';
    }
}
