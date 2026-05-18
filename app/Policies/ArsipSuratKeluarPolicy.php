<?php

namespace App\Policies;

use App\Models\User;

class ArsipSuratKeluarPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        foreach ([
            'kepegawaian.persuratan.arsip_surat_keluar',
            'kepegawaian.persuratan.surat_keluar',
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
