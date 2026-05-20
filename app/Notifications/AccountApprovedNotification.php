<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(public User $approvedBy)
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
            'type' => 'account_approved',
            'message' => 'Akun Anda telah disetujui. Silakan login.',
            'approved_by' => $this->approvedBy->name,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Akun Trimexas Anda Disetujui')
            ->greeting('Halo '.$notifiable->name.',')
            ->line('Akun Anda di Trimexas telah disetujui oleh admin.')
            ->action('Masuk sekarang', url('/login'))
            ->line('Terima kasih telah mendaftar.');
    }
}
