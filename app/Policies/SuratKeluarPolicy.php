<?php

namespace App\Policies;

use App\Models\SuratKeluar;
use App\Models\User;

class SuratKeluarPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasAnyPersuratanPermission($user);
    }

    public function view(User $user, SuratKeluar $suratKeluar): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->is_super_admin || $user->hasPermission('kepegawaian.persuratan.surat_keluar');
    }

    public function update(User $user, SuratKeluar $suratKeluar): bool
    {
        if (! $suratKeluar->isEditable()) {
            return false;
        }

        if ($user->is_super_admin) {
            return true;
        }

        if ($user->hasPermission('kepegawaian.persuratan.surat_keluar')) {
            return $this->ownsSurat($user, $suratKeluar) || $user->hasPermission('kepegawaian.persuratan.approve_sekretariat');
        }

        return false;
    }

    public function delete(User $user, SuratKeluar $suratKeluar): bool
    {
        if (! $suratKeluar->isDeletable()) {
            return false;
        }

        return $user->is_super_admin
            || ($user->hasPermission('kepegawaian.persuratan.surat_keluar') && $this->ownsSurat($user, $suratKeluar));
    }

    public function submit(User $user, SuratKeluar $suratKeluar): bool
    {
        return ($user->is_super_admin || $user->hasPermission('kepegawaian.persuratan.surat_keluar'))
            && in_array($suratKeluar->status, ['draft', 'revisi_substansi', 'revisi_admin'], true)
            && $this->ownsSurat($user, $suratKeluar);
    }

    public function workflow(User $user): bool
    {
        return $user->is_super_admin
            || $user->hasPermission('kepegawaian.persuratan.approve_kabid')
            || $user->hasPermission('kepegawaian.persuratan.approve_sekretariat')
            || $user->hasPermission('kepegawaian.persuratan.approve_kadis');
    }

    public function cancel(User $user, SuratKeluar $suratKeluar): bool
    {
        $allowed = ['draft', 'revisi_substansi', 'revisi_admin', 'menunggu_review_substansi', 'menunggu_verifikasi'];
        if (! in_array($suratKeluar->status, $allowed, true)) {
            return false;
        }

        if ($user->is_super_admin) {
            return true;
        }

        if ($user->hasPermission('kepegawaian.persuratan.approve_sekretariat')
            || $user->hasPermission('kepegawaian.persuratan.approve_kabid')) {
            return true;
        }

        return $user->hasPermission('kepegawaian.persuratan.surat_keluar') && $this->ownsSurat($user, $suratKeluar);
    }

    public function archive(User $user, SuratKeluar $suratKeluar): bool
    {
        return $suratKeluar->status === 'dikirim'
            && ($user->is_super_admin || $user->hasPermission('kepegawaian.persuratan.approve_sekretariat'));
    }

    private function ownsSurat(User $user, SuratKeluar $suratKeluar): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        if ($suratKeluar->created_by_user_id && (int) $suratKeluar->created_by_user_id === (int) $user->id) {
            return true;
        }

        return $user->id_pegawai
            && (int) $suratKeluar->id_pegawai_pengusul === (int) $user->id_pegawai;
    }

    private function hasAnyPersuratanPermission(User $user): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        foreach ([
            'kepegawaian.persuratan.surat_keluar',
            'kepegawaian.persuratan.approve_kabid',
            'kepegawaian.persuratan.approve_sekretariat',
            'kepegawaian.persuratan.approve_kadis',
        ] as $key) {
            if ($user->hasPermission($key)) {
                return true;
            }
        }

        return false;
    }
}
