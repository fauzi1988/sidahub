<?php

namespace App\Notifications;

use App\Models\SuratMasuk;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SuratMasukStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        public SuratMasuk $surat,
        public string $message,
        public ?string $context = null,
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
        $url = route('persuratan-masuk.show', [
            'surat_masuk' => $this->surat,
            'context' => $this->context ?? 'sekretariat',
        ]);

        return [
            'surat_masuk_id' => $this->surat->id_surat_masuk,
            'nomor_agenda' => $this->surat->nomor_agenda,
            'perihal' => $this->surat->perihal,
            'status' => $this->surat->status,
            'status_label' => $this->surat->statusLabel(),
            'message' => $this->message,
            'url' => $url,
        ];
    }
}
