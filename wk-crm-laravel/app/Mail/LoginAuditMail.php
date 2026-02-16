<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Collection;

class LoginAuditMail extends Mailable
{
    use Queueable;

    public function __construct(
        private readonly Collection $audits,
        private readonly string $adminEmail,
        private readonly string $triggeredBy
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[WK CRM] Relatório de Acessos ao Sistema - ' . now()->format('d/m/Y H:i'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.login-audit',
            with: [
                'audits' => $this->audits,
                'admin_email' => $this->adminEmail,
                'triggered_by' => $this->triggeredBy,
                'timestamp' => now()->toDateTimeString(),
            ],
        );
    }
}
