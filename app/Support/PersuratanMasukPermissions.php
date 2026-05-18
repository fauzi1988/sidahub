<?php

namespace App\Support;

use App\Models\User;

final class PersuratanMasukPermissions
{
    public const SURAT_MASUK = 'kepegawaian.persuratan.surat_masuk';

    public const KADIS = 'kepegawaian.persuratan_masuk.approve_kadis';

    public const KABID = 'kepegawaian.persuratan_masuk.approve_kabid';

    /** @deprecated Legacy key — kept for backward compatibility */
    public const KADIS_LEGACY = 'kepegawaian.persuratan.approve_kadis';

    /** @deprecated Legacy key — kept for backward compatibility */
    public const KABID_LEGACY = 'kepegawaian.persuratan.approve_kabid';

    public static function canSekretariat(User $user): bool
    {
        return $user->is_super_admin || $user->hasPermission(self::SURAT_MASUK);
    }

    public static function canKadis(User $user): bool
    {
        return $user->is_super_admin
            || $user->hasPermission(self::KADIS)
            || $user->hasPermission(self::KADIS_LEGACY);
    }

    public static function canKabid(User $user): bool
    {
        return $user->is_super_admin
            || $user->hasPermission(self::KABID)
            || $user->hasPermission(self::KABID_LEGACY);
    }

    /**
     * @return list<string>
     */
    public static function kadisPermissionKeys(): array
    {
        return [self::KADIS, self::KADIS_LEGACY];
    }

    /**
     * @return list<string>
     */
    public static function kabidPermissionKeys(): array
    {
        return [self::KABID, self::KABID_LEGACY];
    }
}
