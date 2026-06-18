<?php

namespace App\Notifications;

use App\Models\SelectionBatch;
use App\Models\SelectionResult;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SelectionResultReadyNotification extends Notification
{
    use Queueable;

    public function __construct(
        public SelectionBatch $batch,
        public SelectionResult $result,
    ) {
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
        $eligible = (bool) $this->result->eligible;
        $scoreText = $this->result->score !== null
            ? number_format((float) $this->result->score, 2, '.', '')
            : null;

        $summary = $eligible
            ? sprintf(
                'Skor %.2f · kategori %s%s',
                (float) $this->result->score,
                str_replace('_', ' ', (string) $this->result->category),
                $this->result->rank ? " · peringkat #{$this->result->rank}" : '',
            )
            : 'Tidak memenuhi syarat seleksi.';

        return [
            'type' => 'selection_result_ready',
            'batch_id' => $this->batch->id,
            'batch_label' => $this->batch->label,
            'eligible' => $eligible,
            'score' => $scoreText,
            'category' => $this->result->category,
            'rank' => $this->result->rank,
            'message' => "Hasil analisa seleksi \"{$this->batch->label}\" sudah tersedia.",
            'summary' => $summary,
            'action_url' => route('mahasiswa.analysis.show', $this->batch),
            'action_label' => 'Lihat hasil analisa',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage())
            ->subject("Hasil Analisa Seleksi \"{$this->batch->label}\" Tersedia")
            ->greeting('Halo '.$notifiable->name.',')
            ->line("Proses analisa seleksi \"{$this->batch->label}\" telah selesai.")
            ->line($this->toArray($notifiable)['summary']);

        return $mail->action('Lihat hasil analisa', url('/mahasiswa/analysis/'.$this->batch->id));
    }
}
