<?php

namespace App\Notifications;

use App\Models\SelectionBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BatchFailedNotification extends Notification
{
    use Queueable;

    public function __construct(public SelectionBatch $batch)
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
            'type' => 'batch_failed',
            'batch_id' => $this->batch->id,
            'label' => $this->batch->label,
            'error_summary' => $this->batch->error_summary,
            'message' => "Batch \"{$this->batch->label}\" gagal diproses.",
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject("Batch Seleksi \"{$this->batch->label}\" Gagal")
            ->greeting('Halo '.$notifiable->name.',')
            ->line("Batch seleksi \"{$this->batch->label}\" gagal diproses.")
            ->line('Silakan cek halaman riwayat batch untuk detail error.');
    }
}
