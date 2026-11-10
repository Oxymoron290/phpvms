<?php

declare(strict_types=1);

namespace App\Notifications\Messages;

use App\Contracts\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AdminPilotEmail extends Notification
{
    public function __construct(
        private readonly string $subject,
        private readonly string $message,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject($this->subject)
            ->line($this->message);
    }
}
