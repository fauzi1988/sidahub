<?php

namespace App\Services;

use App\Models\SuratKeluar;
use App\Models\SuratKeluarLog;
use App\Models\User;
use App\Notifications\SuratKeluarStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use InvalidArgumentException;

class SuratKeluarWorkflowService
{
    public function __construct(
        private readonly SuratKeluarNomorService $nomorService,
    ) {}

    public function submit(SuratKeluar $surat, Request $request, ?string $note = null): void
    {
        $this->transition($surat, $request, 'submit_ke_kabid', ['draft', 'revisi_substansi', 'revisi_admin'], 'menunggu_review_substansi', [
            'submitted_at' => now(),
        ], $note, 'kepegawaian.persuratan.approve_kabid');
    }

    public function kabidApprove(SuratKeluar $surat, Request $request, ?string $note = null): void
    {
        $this->transition($surat, $request, 'approve_kabid', ['menunggu_review_substansi'], 'menunggu_verifikasi', [
            'reviewed_at' => now(),
            'reviewed_by_kabid_user_id' => $request->user()?->id,
        ], $note, 'kepegawaian.persuratan.approve_sekretariat');
    }

    public function kabidRevise(SuratKeluar $surat, Request $request, string $note): void
    {
        $this->transition($surat, $request, 'revisi_kabid', ['menunggu_review_substansi'], 'revisi_substansi', [], $note, 'kepegawaian.persuratan.surat_keluar');
    }

    public function sekretariatForward(SuratKeluar $surat, Request $request, ?string $note = null): void
    {
        $this->transition($surat, $request, 'teruskan_sekretariat_ke_kadis', ['menunggu_verifikasi'], 'menunggu_ttd', [
            'verified_at' => now(),
            'forwarded_to_kadis_at' => now(),
            'verified_by_sekretariat_user_id' => $request->user()?->id,
        ], $note, 'kepegawaian.persuratan.approve_kadis');
    }

    public function sekretariatRevise(SuratKeluar $surat, Request $request, string $note): void
    {
        $this->transition($surat, $request, 'revisi_sekretariat', ['menunggu_verifikasi'], 'revisi_admin', [], $note, 'kepegawaian.persuratan.surat_keluar');
    }

    public function kadisSign(SuratKeluar $surat, Request $request, array $ttdData, ?string $note = null): void
    {
        if ($surat->status !== 'menunggu_ttd') {
            throw new InvalidArgumentException('Status surat tidak valid untuk tanda tangan Kepala Dinas.');
        }

        $from = $surat->status;
        $surat->update([
            'status' => 'disetujui',
            'signed_at' => now(),
            'signed_by_kadis_user_id' => $request->user()?->id,
            'id_pegawai_penandatangan' => $request->user()?->id_pegawai ?: $surat->id_pegawai_penandatangan,
            'jenis_ttd' => $ttdData['jenis_ttd'],
            'ttd_management_id' => $ttdData['ttd_management_id'],
        ]);

        $logNote = $note ?: ('Jenis TTD: '.$ttdData['jenis_ttd']);
        $this->writeLog($surat, $request, 'approve_kadis', $from, 'disetujui', $logNote);
        $this->notifyRole('kepegawaian.persuratan.approve_sekretariat', $surat, 'Surat menunggu penomoran Sekretariat');
    }

    public function kadisRevise(SuratKeluar $surat, Request $request, string $note): void
    {
        $this->transition($surat, $request, 'revisi_kadis', ['menunggu_ttd'], 'menunggu_verifikasi', [
            'signed_at' => null,
            'signed_by_kadis_user_id' => null,
            'jenis_ttd' => null,
            'ttd_management_id' => null,
        ], $note, 'kepegawaian.persuratan.approve_sekretariat');
    }

    public function sekretariatNumberAndSend(SuratKeluar $surat, Request $request, string $nomorSurat, ?string $tanggalKirim, ?string $note = null): void
    {
        if ($surat->status !== 'disetujui') {
            throw new InvalidArgumentException('Status surat tidak valid untuk penomoran Sekretariat.');
        }

        $from = $surat->status;
        $surat->update([
            'nomor_surat' => $nomorSurat,
            'tanggal_kirim' => $tanggalKirim ?? now()->toDateString(),
            'status' => 'dikirim',
            'verification_code' => $this->nomorService->generateVerificationCode(),
        ]);

        $this->writeLog($surat, $request, 'sekretariat_nomor_dan_kirim', $from, 'dikirim', $note);
        $this->notifyRole('kepegawaian.persuratan.surat_keluar', $surat, 'Surat telah dinomori dan dikirim');
    }

    public function archive(SuratKeluar $surat, Request $request, ?string $note = null): void
    {
        $this->transition($surat, $request, 'arsipkan', ['dikirim'], 'diarsipkan', [
            'archived_at' => now(),
        ], $note, null);
    }

    public function cancel(SuratKeluar $surat, Request $request, string $alasan): void
    {
        $allowed = ['draft', 'revisi_substansi', 'revisi_admin', 'menunggu_review_substansi', 'menunggu_verifikasi'];
        if (! in_array($surat->status, $allowed, true)) {
            throw new InvalidArgumentException('Surat tidak dapat dibatalkan pada status ini.');
        }

        $from = $surat->status;
        $surat->update([
            'status' => 'dibatalkan',
            'cancelled_at' => now(),
            'alasan_batal' => $alasan,
        ]);

        $this->writeLog($surat, $request, 'batalkan', $from, 'dibatalkan', $alasan);
    }

    public function logContentEdit(SuratKeluar $surat, Request $request): void
    {
        $this->writeLog($surat, $request, 'edit_konten', $surat->status, $surat->status, 'Konten surat diperbarui.');
    }

    /**
     * @param  list<string>  $fromStatuses
     * @param  array<string, mixed>  $extra
     */
    private function transition(
        SuratKeluar $surat,
        Request $request,
        string $action,
        array $fromStatuses,
        string $toStatus,
        array $extra,
        ?string $note,
        ?string $notifyPermission
    ): void {
        if (! in_array($surat->status, $fromStatuses, true)) {
            throw new InvalidArgumentException('Status surat tidak valid untuk aksi ini.');
        }

        $from = $surat->status;
        $surat->update(array_merge(['status' => $toStatus], $extra));
        $this->writeLog($surat, $request, $action, $from, $toStatus, $note);

        if ($notifyPermission) {
            $this->notifyRole($notifyPermission, $surat, SuratKeluar::statusOptions()[$toStatus] ?? $toStatus);
        }
    }

    private function writeLog(
        SuratKeluar $surat,
        Request $request,
        string $action,
        ?string $fromStatus,
        ?string $toStatus,
        ?string $note
    ): void {
        SuratKeluarLog::create([
            'surat_keluar_id' => $surat->id_surat_keluar,
            'user_id' => $request->user()?->id,
            'action' => $action,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'note' => $note,
        ]);
    }

    private function notifyRole(string $permission, SuratKeluar $surat, string $message): void
    {
        $users = User::query()
            ->where('is_super_admin', true)
            ->orWhereHas('permissions', fn ($q) => $q->where('permission_key', $permission))
            ->get();

        if ($users->isNotEmpty()) {
            Notification::send($users, new SuratKeluarStatusNotification($surat, $message));
        }
    }
}
