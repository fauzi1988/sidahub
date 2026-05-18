<?php

namespace App\Services;

use App\Models\SuratMasuk;
use Illuminate\Support\Facades\DB;

class SuratMasukNomorAgendaService
{
    public function generateNext(?int $year = null): string
    {
        $year = $year ?: (int) now()->format('Y');
        $prefix = sprintf('SM/DISHUB/%02d/', $year % 100);

        $last = SuratMasuk::query()
            ->where('nomor_agenda', 'like', $prefix.'%')
            ->orderByDesc('id_surat_masuk')
            ->value('nomor_agenda');

        $seq = 1;
        if ($last && preg_match('/(\d+)$/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public function assignAgenda(SuratMasuk $surat, ?int $year = null): string
    {
        return DB::transaction(function () use ($surat, $year) {
            if ($surat->nomor_agenda) {
                return $surat->nomor_agenda;
            }

            $nomor = $this->generateNext($year);
            $surat->update([
                'nomor_agenda' => $nomor,
                'agenda_at' => now(),
                'status' => $surat->status === 'tercatat' ? 'agenda' : $surat->status,
            ]);

            return $nomor;
        });
    }
}
