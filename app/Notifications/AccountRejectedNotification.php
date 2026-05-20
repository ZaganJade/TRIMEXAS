<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(public string $reason)
    {
        $this->onQueue('notifications');
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'account_rejected',
            'message' => 'Pendaftaran Anda ditolak.',
            'reason' => $this->reason,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Pendaftaran Trimexas Anda Ditolak')
            ->greeting('Halo '.$notifiable->name.',')
            ->line('Pendaftaran akun Anda di Trimexas tidak dapat disetujui.')
            ->line('Alasan: '.$this->reason)
            ->line('Anda dapat menghubungi admin untuk klarifikasi lebih lanjut.');
    }
}
