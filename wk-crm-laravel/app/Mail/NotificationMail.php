<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NotificationMail extends Mailable
{
    use Queueable;

    public function __construct(
        private readonly string $titleText,
        private readonly string $bodyText,
        private readonly ?string $actionUrl = null,
        private readonly string $createdAtText = ''
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->titleText ?? 'Notificação WK CRM',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.notification',
            with: [
                'title' => $this->titleText ?? 'Notificação WK CRM',
                'body' => $this->bodyText ?? '',
                'action_url' => $this->actionUrl ?? '',
                'created_at' => $this->createdAtText ?? now()->toDateTimeString(),
            ],
        );
    }
}
