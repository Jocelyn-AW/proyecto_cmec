<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Services\MailService;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    protected string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable)
    {
        // $frontendUrl = config('app.frontend_url') . '/reset-password';

        // $resetUrl = $frontendUrl . '/' . $this->token .
        //     '?email=' . urlencode($notifiable->email);

        $resetUrl = route('password.reset', $this->token);


        $mailService = app(MailService::class);

        return $mailService
            ->make(
                subject: 'Recuperación de contraseña',
                viewName: 'emails.auth.reset-password',
                viewData: [
                    'headerTitle' => 'Recuperación de contraseña',
                    'resetUrl' => $resetUrl,
                    'logo' => config('app.logo_url'),
                    'footerAddress' => config('app.address'),
                ]
            )
            ->to($notifiable->email);
    }
}
