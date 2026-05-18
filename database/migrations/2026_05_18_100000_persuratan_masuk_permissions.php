<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $map = [
            'kepegawaian.persuratan.approve_kadis' => 'kepegawaian.persuratan_masuk.approve_kadis',
            'kepegawaian.persuratan.approve_kabid' => 'kepegawaian.persuratan_masuk.approve_kabid',
        ];

        foreach ($map as $legacy => $newKey) {
            $userIds = DB::table('user_permissions')
                ->where('permission_key', $legacy)
                ->pluck('user_id')
                ->unique();

            foreach ($userIds as $userId) {
                $exists = DB::table('user_permissions')
                    ->where('user_id', $userId)
                    ->where('permission_key', $newKey)
                    ->exists();

                if (! $exists) {
                    DB::table('user_permissions')->insert([
                        'user_id' => $userId,
                        'permission_key' => $newKey,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        DB::table('user_permissions')
            ->whereIn('permission_key', [
                'kepegawaian.persuratan_masuk.approve_kadis',
                'kepegawaian.persuratan_masuk.approve_kabid',
            ])
            ->delete();
    }
};
