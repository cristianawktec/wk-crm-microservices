<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $titleText;
    public string $bodyText;
    public ?string $actionUrl;
    public string $createdAtText;

    public function __construct(string $titleText, string $bodyText, ?string $actionUrl, string $createdAtText)
    {
        $this->titleText = $titleText;
        $this->bodyText = $bodyText;
        $this->actionUrl = $actionUrl;
        $this->createdAtText = $createdAtText;
    }

    public function envelope(): \Illuminate\Mail\Mailables\Envelope
    {
        $subject = $this->titleText ?: 'Notificação WK CRM';
        return new \Illuminate\Mail\Mailables\Envelope(
            subject: $subject,
        );
    }

    public function content(): \Illuminate\Mail\Mailables\Content
    {
        return new \Illuminate\Mail\Mailables\Content(
            view: 'emails.notification',
            with: [
                'title' => $this->titleText,
                'message' => $this->bodyText,
                'action_url' => $this->actionUrl,
                'created_at' => $this->createdAtText,
            ],
        );
    }
}
