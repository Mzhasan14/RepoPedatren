<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;

class ResetPasswordNotification extends BaseResetPassword
{
    public function toMail($notifiable)
    {
        $frontendUrl = config('app.url') . '/reset-password';

        $resetUrl = $frontendUrl
            . '?token=' . $this->token
            . '&email=' . urlencode($notifiable->getEmailForPasswordReset());

        return (new MailMessage)
            ->subject('Permintaan Reset Password')
            ->greeting('Halo ' . ($notifiable->name ?? 'Pengguna') . ',')
            ->line('Kami menerima permintaan untuk mereset password akun kamu.')
            ->action('Reset Password', $resetUrl)
            ->line('Link ini hanya berlaku selama 60 menit.')
            ->line('Jika kamu tidak mengajukan permintaan ini, abaikan email ini.')
            ->salutation('Salam hangat,')
            ->salutation(config('app.name'));
    }
}
