<?php

namespace App\Notifications;

use App\Models\SuratKeluar;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SuratKeluarStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        public SuratKeluar $surat,
        public string $message,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'surat_keluar_id' => $this->surat->id_surat_keluar,
            'perihal' => $this->surat->perihal,
            'status' => $this->surat->status,
            'status_label' => SuratKeluar::statusOptions()[$this->surat->status] ?? $this->surat->status,
            'message' => $this->message,
            'url' => route('persuratan-surat-keluar.show', $this->surat),
        ];
    }
}
