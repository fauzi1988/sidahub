<?php

namespace App\Policies;

use App\Models\JabatanPegawai;
use App\Models\SuratMasuk;
use App\Models\SuratMasukDisposisi;
use App\Models\User;
use App\Support\PersuratanMasukPermissions;

class SuratMasukPolicy
{
    public function viewDaftar(User $user): bool
    {
        return PersuratanMasukPermissions::canSekretariat($user);
    }

    public function viewAny(User $user): bool
    {
        return PersuratanMasukPermissions::canSekretariat($user)
            || PersuratanMasukPermissions::canKadis($user)
            || PersuratanMasukPermissions::canKabid($user);
    }

    public function view(User $user, SuratMasuk $suratMasuk): bool
    {
        if ($user->is_super_admin || PersuratanMasukPermissions::canSekretariat($user)) {
            return true;
        }

        if (PersuratanMasukPermissions::canKadis($user)) {
            return in_array($suratMasuk->status, [
                'menunggu_disposisi_kadis',
                'disposisi_ke_unit',
                'proses',
                'selesai',
                'diarsipkan',
                'dibatalkan',
            ], true);
        }

        if (PersuratanMasukPermissions::canKabid($user)) {
            return $this->userHasDisposisiAccess($user, $suratMasuk);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return PersuratanMasukPermissions::canSekretariat($user);
    }

    public function update(User $user, SuratMasuk $suratMasuk): bool
    {
        if (! $suratMasuk->isEditable()) {
            return false;
        }

        return PersuratanMasukPermissions::canSekretariat($user);
    }

    public function delete(User $user, SuratMasuk $suratMasuk): bool
    {
        return $suratMasuk->status === 'tercatat'
            && PersuratanMasukPermissions::canSekretariat($user);
    }

    public function forwardToKadis(User $user, SuratMasuk $suratMasuk): bool
    {
        return in_array($suratMasuk->status, ['tercatat', 'agenda'], true)
            && PersuratanMasukPermissions::canSekretariat($user);
    }

    public function kadisDispose(User $user, SuratMasuk $suratMasuk): bool
    {
        return $suratMasuk->status === 'menunggu_disposisi_kadis'
            && PersuratanMasukPermissions::canKadis($user);
    }

    public function process(User $user, SuratMasuk $suratMasuk): bool
    {
        if (! in_array($suratMasuk->status, ['disposisi_ke_unit', 'proses'], true)) {
            return false;
        }

        return PersuratanMasukPermissions::canKabid($user)
            && $this->userHasActiveUnitDisposisi($user, $suratMasuk);
    }

    public function completeDisposisi(User $user, SuratMasuk $suratMasuk, SuratMasukDisposisi $disposisi): bool
    {
        if ($disposisi->surat_masuk_id !== $suratMasuk->id_surat_masuk || $disposisi->status !== 'aktif') {
            return false;
        }

        return $this->process($user, $suratMasuk)
            && $this->disposisiMatchesUser($user, $disposisi);
    }

    public function archive(User $user, SuratMasuk $suratMasuk): bool
    {
        return $suratMasuk->status === 'selesai'
            && PersuratanMasukPermissions::canSekretariat($user);
    }

    public function cancel(User $user, SuratMasuk $suratMasuk): bool
    {
        if (in_array($suratMasuk->status, ['diarsipkan', 'dibatalkan'], true)) {
            return false;
        }

        return PersuratanMasukPermissions::canSekretariat($user)
            || PersuratanMasukPermissions::canKadis($user);
    }

    public function export(User $user): bool
    {
        return PersuratanMasukPermissions::canSekretariat($user);
    }

    private function userHasDisposisiAccess(User $user, SuratMasuk $suratMasuk): bool
    {
        if (! in_array($suratMasuk->status, ['disposisi_ke_unit', 'proses', 'selesai', 'diarsipkan'], true)) {
            return false;
        }

        $idPegawai = (int) ($user->id_pegawai ?? 0);
        $unit = $idPegawai ? $this->resolveUnitKerja($idPegawai) : null;

        return $suratMasuk->disposisi()
            ->where(function ($q) use ($idPegawai, $unit) {
                if ($idPegawai) {
                    $q->where('to_pegawai_id', $idPegawai);
                }
                if ($unit) {
                    $q->orWhere('to_unit_kerja', $unit);
                }
            })
            ->exists();
    }

    private function userHasActiveUnitDisposisi(User $user, SuratMasuk $suratMasuk): bool
    {
        $idPegawai = (int) ($user->id_pegawai ?? 0);
        $unit = $idPegawai ? $this->resolveUnitKerja($idPegawai) : null;

        return $suratMasuk->disposisi()
            ->where('status', 'aktif')
            ->where(function ($q) use ($idPegawai, $unit) {
                if ($idPegawai) {
                    $q->where('to_pegawai_id', $idPegawai);
                }
                if ($unit) {
                    $q->orWhere('to_unit_kerja', $unit);
                }
            })
            ->exists();
    }

    private function disposisiMatchesUser(User $user, SuratMasukDisposisi $disposisi): bool
    {
        $idPegawai = (int) ($user->id_pegawai ?? 0);
        if ($idPegawai && (int) $disposisi->to_pegawai_id === $idPegawai) {
            return true;
        }

        $unit = $idPegawai ? $this->resolveUnitKerja($idPegawai) : null;

        return $unit && $disposisi->to_unit_kerja === $unit;
    }

    private function resolveUnitKerja(int $idPegawai): ?string
    {
        return JabatanPegawai::query()
            ->where('id_pegawai', $idPegawai)
            ->where('status_jabatan', 'Aktif')
            ->orderByDesc('tmt')
            ->value('unit_kerja');
    }
}
