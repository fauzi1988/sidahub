<?php

namespace App\Services;

use App\Models\SuratMasuk;
use App\Models\SuratMasukDisposisi;
use App\Models\SuratMasukLog;
use App\Models\User;
use App\Notifications\SuratMasukStatusNotification;
use App\Support\PersuratanMasukPermissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use InvalidArgumentException;

class SuratMasukWorkflowService
{
    public function __construct(
        private readonly SuratMasukNomorAgendaService $nomorAgendaService,
    ) {}

    public function setAgenda(SuratMasuk $surat, Request $request, ?string $note = null): void
    {
        if (! in_array($surat->status, ['tercatat', 'agenda'], true)) {
            throw new InvalidArgumentException('Status surat tidak valid untuk agenda.');
        }

        $from = $surat->status;
        $this->nomorAgendaService->assignAgenda($surat->fresh());
        $surat->refresh();

        $this->writeLog($surat, $request, 'agenda', $from, $surat->status, $note);
    }

    public function forwardToKadis(SuratMasuk $surat, Request $request, ?string $note = null): void
    {
        $this->transition($surat, $request, 'teruskan_kadis', ['tercatat', 'agenda'], 'menunggu_disposisi_kadis', [
            'forwarded_to_kadis_at' => now(),
        ], $note);

        if (! $surat->nomor_agenda) {
            $this->nomorAgendaService->assignAgenda($surat->fresh());
            $surat->refresh();
        }

        $this->notifyByPermissions(
            PersuratanMasukPermissions::kadisPermissionKeys(),
            $surat,
            'Surat masuk menunggu disposisi Kadis: '.$surat->perihal,
            'kadis',
        );
    }

    /**
     * @param  array<int, array{to_pegawai_id?: int|null, to_unit_kerja?: string|null, instruksi: string, batas_waktu?: string|null}>  $items
     */
    public function kadisDispose(SuratMasuk $surat, Request $request, array $items, ?string $note = null): void
    {
        if ($surat->status !== 'menunggu_disposisi_kadis') {
            throw new InvalidArgumentException('Surat harus menunggu disposisi Kadis.');
        }

        if ($items === []) {
            throw new InvalidArgumentException('Minimal satu disposisi ke Kabid/Unit.');
        }

        DB::transaction(function () use ($surat, $request, $items, $note) {
            $from = $surat->status;

            foreach ($items as $item) {
                SuratMasukDisposisi::create([
                    'surat_masuk_id' => $surat->id_surat_masuk,
                    'from_user_id' => $request->user()?->id,
                    'to_pegawai_id' => $item['to_pegawai_id'] ?? null,
                    'to_unit_kerja' => $item['to_unit_kerja'] ?? null,
                    'tingkat' => 'unit',
                    'instruksi' => $item['instruksi'],
                    'batas_waktu' => $item['batas_waktu'] ?? null,
                    'status' => 'aktif',
                ]);
            }

            $surat->update([
                'status' => 'disposisi_ke_unit',
                'disposed_at' => now(),
            ]);

            $this->writeLog($surat, $request, 'disposisi_kabid_unit', $from, 'disposisi_ke_unit', $note);
        });

        $surat->refresh()->load('disposisi');

        foreach ($surat->disposisi->where('status', 'aktif') as $disposisi) {
            $this->notifyDisposisiTarget($surat, $disposisi);
        }

        $this->notifyByPermissions(
            [PersuratanMasukPermissions::SURAT_MASUK],
            $surat,
            'Disposisi Kadis telah dikirim ke unit terkait.',
            'sekretariat',
        );
    }

    public function startProcess(SuratMasuk $surat, Request $request, ?string $note = null): void
    {
        $this->transition($surat, $request, 'tindak_lanjut', ['disposisi_ke_unit'], 'proses', [], $note);
    }

    public function completeDisposisi(
        SuratMasuk $surat,
        SuratMasukDisposisi $disposisi,
        Request $request,
        ?string $note = null,
    ): void {
        if ($disposisi->surat_masuk_id !== $surat->id_surat_masuk) {
            throw new InvalidArgumentException('Disposisi tidak sesuai dengan surat.');
        }

        if ($disposisi->status !== 'aktif') {
            throw new InvalidArgumentException('Disposisi ini sudah tidak aktif.');
        }

        if (! in_array($surat->status, ['disposisi_ke_unit', 'proses'], true)) {
            throw new InvalidArgumentException('Status surat tidak valid untuk penyelesaian disposisi.');
        }

        DB::transaction(function () use ($surat, $disposisi, $request, $note) {
            $from = $surat->status;

            $disposisi->update([
                'status' => 'selesai',
                'selesai_at' => now(),
                'catatan_tindak_lanjut' => $note,
            ]);

            $stillActive = $surat->disposisi()->where('status', 'aktif')->exists();

            if ($stillActive) {
                if ($surat->status === 'disposisi_ke_unit') {
                    $surat->update(['status' => 'proses']);
                }
                $this->writeLog($surat, $request, 'selesai_disposisi', $from, $surat->fresh()->status, $note);

                return;
            }

            $surat->update([
                'status' => 'selesai',
                'completed_at' => now(),
            ]);

            $this->writeLog($surat, $request, 'selesai', $from, 'selesai', $note);
        });

        $surat->refresh();

        if ($surat->status === 'selesai') {
            $this->notifyByPermissions(
                [PersuratanMasukPermissions::SURAT_MASUK],
                $surat,
                'Semua disposisi selesai. Surat masuk siap diarsipkan: '.$surat->perihal,
                'sekretariat',
            );
        }
    }

    public function archive(SuratMasuk $surat, Request $request, ?string $note = null): void
    {
        $this->transition($surat, $request, 'arsipkan', ['selesai'], 'diarsipkan', [
            'archived_at' => now(),
        ], $note);
    }

    public function cancel(SuratMasuk $surat, Request $request, string $alasan): void
    {
        if (in_array($surat->status, ['diarsipkan', 'dibatalkan'], true)) {
            throw new InvalidArgumentException('Surat tidak dapat dibatalkan.');
        }

        DB::transaction(function () use ($surat, $request, $alasan) {
            $from = $surat->status;

            $surat->disposisi()->where('status', 'aktif')->update([
                'status' => 'dibatalkan',
                'selesai_at' => now(),
            ]);

            $surat->update([
                'status' => 'dibatalkan',
                'cancelled_at' => now(),
                'alasan_batal' => $alasan,
            ]);

            $this->writeLog($surat, $request, 'batalkan', $from, 'dibatalkan', $alasan);
        });
    }

    public function logEdit(SuratMasuk $surat, Request $request): void
    {
        $this->writeLog($surat, $request, 'edit', $surat->status, $surat->status, 'Perubahan data surat masuk');
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function transition(
        SuratMasuk $surat,
        Request $request,
        string $action,
        array $allowedFrom,
        string $toStatus,
        array $extra = [],
        ?string $note = null,
    ): void {
        if (! in_array($surat->status, $allowedFrom, true)) {
            throw new InvalidArgumentException('Status surat tidak valid untuk aksi ini.');
        }

        $from = $surat->status;
        $surat->update(array_merge(['status' => $toStatus], $extra));
        $this->writeLog($surat, $request, $action, $from, $toStatus, $note);
    }

    private function writeLog(
        SuratMasuk $surat,
        Request $request,
        string $action,
        ?string $from,
        ?string $to,
        ?string $note,
    ): void {
        SuratMasukLog::create([
            'surat_masuk_id' => $surat->id_surat_masuk,
            'user_id' => $request->user()?->id,
            'action' => $action,
            'from_status' => $from,
            'to_status' => $to,
            'note' => $note,
        ]);
    }

    /**
     * @param  list<string>  $permissionKeys
     */
    private function notifyByPermissions(array $permissionKeys, SuratMasuk $surat, string $message, string $context): void
    {
        $users = User::query()
            ->where(function ($q) use ($permissionKeys) {
                $q->where('is_super_admin', true);
                foreach ($permissionKeys as $key) {
                    $q->orWhereHas('permissions', fn ($sub) => $sub->where('permission_key', $key));
                }
            })
            ->get();

        if ($users->isNotEmpty()) {
            Notification::send($users, new SuratMasukStatusNotification($surat, $message, $context));
        }
    }

    private function notifyDisposisiTarget(SuratMasuk $surat, SuratMasukDisposisi $disposisi): void
    {
        $users = collect();

        if ($disposisi->to_pegawai_id) {
            $pegawaiUser = User::query()->where('id_pegawai', $disposisi->to_pegawai_id)->get();
            $users = $users->merge($pegawaiUser);
        }

        if ($disposisi->to_unit_kerja) {
            $unitUsers = User::query()
                ->whereHas('pegawai.jabatanPegawai', function ($q) use ($disposisi) {
                    $q->where('unit_kerja', $disposisi->to_unit_kerja)
                        ->where('status_jabatan', 'Aktif');
                })
                ->where(function ($q) {
                    $q->where('is_super_admin', true);
                    foreach (PersuratanMasukPermissions::kabidPermissionKeys() as $key) {
                        $q->orWhereHas('permissions', fn ($sub) => $sub->where('permission_key', $key));
                    }
                })
                ->get();
            $users = $users->merge($unitUsers);
        }

        $users = $users->unique('id');

        if ($users->isNotEmpty()) {
            Notification::send(
                $users,
                new SuratMasukStatusNotification(
                    $surat,
                    'Disposisi surat masuk untuk Anda: '.$surat->perihal,
                    'unit',
                ),
            );
        }
    }
}
