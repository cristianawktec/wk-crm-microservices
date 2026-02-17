<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use App\Models\User;

class LoginNotificationMail extends Mailable
{
    use Queueable;

    public function __construct(
        private readonly User $user,
        private readonly string $ipAddress,
        private readonly string $userAgent,
        private readonly string $loginTime = ''
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[WK CRM] Novo Login - ' . $this->user->name . ' (' . $this->user->email . ')',
        );
    }

    public function content(): Content
    {
        $parsedUserAgent = $this->parseUserAgent($this->userAgent);

        return new Content(
            view: 'emails.login-notification',
            with: [
                'user_name' => $this->user->name,
                'user_email' => $this->user->email,
                'ip_address' => $this->ipAddress,
                'browser' => $parsedUserAgent['browser'] ?? 'Desconhecido',
                'platform' => $parsedUserAgent['platform'] ?? 'Desconhecido',
                'device' => $parsedUserAgent['device'] ?? 'Desconhecido',
                'login_time' => $this->loginTime ?: now()->format('d/m/Y H:i:s'),
                'timestamp' => now()->toDateTimeString(),
            ],
        );
    }

    private function parseUserAgent(string $userAgent): array
    {
        $browser = 'Desconhecido';
        $platform = 'Desconhecido';
        $device = 'Desktop';

        if (preg_match('/Chrome\/(\d+)/', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Firefox\/(\d+)/', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Safari\/(\d+)/', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/Edge\/(\d+)/', $userAgent)) {
            $browser = 'Edge';
        }

        if (preg_match('/Windows/', $userAgent)) {
            $platform = 'Windows';
        } elseif (preg_match('/Macintosh|Mac OS/', $userAgent)) {
            $platform = 'macOS';
        } elseif (preg_match('/Linux/', $userAgent)) {
            $platform = 'Linux';
        } elseif (preg_match('/iPhone|iPad/', $userAgent)) {
            $platform = 'iOS';
            $device = 'Mobile';
        } elseif (preg_match('/Android/', $userAgent)) {
            $platform = 'Android';
            $device = 'Mobile';
        }

        return [
            'browser' => $browser,
            'platform' => $platform,
            'device' => $device,
        ];
    }
}
