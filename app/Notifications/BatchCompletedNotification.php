<?php

namespace App\Notifications;

use App\Models\SelectionBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BatchCompletedNotification extends Notification
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
            'type' => 'batch_completed',
            'batch_id' => $this->batch->id,
            'label' => $this->batch->label,
            'total_eligible' => $this->batch->total_eligible,
            'total_ineligible' => $this->batch->total_ineligible,
            'message' => "Batch \"{$this->batch->label}\" selesai diproses.",
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject("Batch Seleksi \"{$this->batch->label}\" Selesai")
            ->greeting('Halo '.$notifiable->name.',')
            ->line("Batch seleksi \"{$this->batch->label}\" telah selesai diproses.")
            ->line("Eligible: {$this->batch->total_eligible} kandidat. Tidak memenuhi syarat: {$this->batch->total_ineligible} kandidat.")
            ->action('Lihat ranking', url('/admin/selection/'.$this->batch->id));
    }
}
