<?php

namespace App\Notifications;

use App\Models\Access;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * NOT queued — OTP must be sent immediately so the user can use it within minutes.
 * Also: only mail channel; do NOT store the code in the database.
 */
class AccessOtpRequested extends Notification
{
    use Queueable;

    public function __construct(public string $code, public Access $access)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Código de seguridad para revelar credencial · '.$this->access->code)
            ->view('emails.accesses.otp', [
                'code' => $this->code,
                'access' => $this->access,
                'recipient' => $notifiable,
                'minutes' => \App\Services\AccessService::OTP_VALIDITY_MINUTES,
            ]);
    }
}
