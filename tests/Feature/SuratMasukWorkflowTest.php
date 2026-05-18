<?php

namespace Tests\Feature;

use App\Models\JabatanPegawai;
use App\Models\Pegawai;
use App\Models\SuratMasuk;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SuratMasukWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (($_ENV['DB_CONNECTION'] ?? getenv('DB_CONNECTION') ?: '') === 'sqlite') {
            $this->markTestSkipped('Workflow feature tests require MySQL (full migration chain).');
        }

        parent::setUp();
    }

    public function test_full_workflow_with_multi_disposisi_until_archived(): void
    {
        Notification::fake();

        $sekretariat = $this->userWithPermission('kepegawaian.persuratan.surat_masuk');
        $kadis = $this->userWithPermission('kepegawaian.persuratan_masuk.approve_kadis');

        $unitA = 'Bidang Lalu Lintas';
        $unitB = 'Bidang Perhubungan Darat';
        $kabidA = $this->userWithUnitPermission('kepegawaian.persuratan_masuk.approve_kabid', $unitA);
        $kabidB = $this->userWithUnitPermission('kepegawaian.persuratan_masuk.approve_kabid', $unitB);

        $surat = SuratMasuk::create($this->suratPayload([
            'status' => 'tercatat',
            'created_by_user_id' => $sekretariat->id,
        ]));

        $this->actingAs($sekretariat)->post(route('persuratan-masuk.forward-kadis', $surat));
        $surat->refresh();
        $this->assertSame('menunggu_disposisi_kadis', $surat->status);

        $this->actingAs($kadis)->post(route('persuratan-masuk.kadis-dispose', $surat), [
            'disposisi' => [
                [
                    'to_unit_kerja' => $unitA,
                    'instruksi' => 'Tindaklanjuti segera unit A',
                    'batas_waktu' => now()->addDays(3)->toDateString(),
                ],
                [
                    'to_unit_kerja' => $unitB,
                    'instruksi' => 'Koordinasi unit B',
                    'batas_waktu' => now()->addDays(5)->toDateString(),
                ],
            ],
        ]);

        $surat->refresh();
        $this->assertSame('disposisi_ke_unit', $surat->status);
        $this->assertCount(2, $surat->disposisi);

        $dispA = $surat->disposisi->firstWhere('to_unit_kerja', $unitA);
        $dispB = $surat->disposisi->firstWhere('to_unit_kerja', $unitB);

        $this->actingAs($kabidA)->post(route('persuratan-masuk.complete-disposisi', [$surat, $dispA]));
        $surat->refresh();
        $this->assertSame('proses', $surat->status);
        $this->assertSame('selesai', $dispA->fresh()->status);
        $this->assertSame('aktif', $dispB->fresh()->status);

        $this->actingAs($kabidB)->post(route('persuratan-masuk.complete-disposisi', [$surat, $dispB]));
        $surat->refresh();
        $this->assertSame('selesai', $surat->status);

        $this->actingAs($sekretariat)->post(route('persuratan-masuk.archive', $surat));
        $surat->refresh();
        $this->assertSame('diarsipkan', $surat->status);
    }

    public function test_cancel_marks_active_disposisi_as_dibatalkan(): void
    {
        $sekretariat = $this->userWithPermission('kepegawaian.persuratan.surat_masuk');
        $kadis = $this->userWithPermission('kepegawaian.persuratan_masuk.approve_kadis');

        $surat = SuratMasuk::create($this->suratPayload([
            'status' => 'menunggu_disposisi_kadis',
            'created_by_user_id' => $sekretariat->id,
        ]));

        $this->actingAs($kadis)->post(route('persuratan-masuk.kadis-dispose', $surat), [
            'disposisi' => [
                [
                    'to_unit_kerja' => 'Seksi Tata Usaha',
                    'instruksi' => 'Proses administrasi',
                ],
            ],
        ]);

        $this->actingAs($sekretariat)->post(route('persuratan-masuk.cancel', $surat), [
            'alasan' => 'Surat duplikat',
        ]);

        $surat->refresh();
        $this->assertSame('dibatalkan', $surat->status);
        $this->assertTrue(
            $surat->disposisi()->where('status', 'dibatalkan')->count() === $surat->disposisi()->count(),
        );
    }

    public function test_destroy_only_allowed_for_tercatat(): void
    {
        $sekretariat = $this->userWithPermission('kepegawaian.persuratan.surat_masuk');

        $surat = SuratMasuk::create($this->suratPayload([
            'status' => 'agenda',
            'created_by_user_id' => $sekretariat->id,
        ]));

        $this->actingAs($sekretariat)
            ->delete(route('persuratan-masuk.destroy', $surat))
            ->assertForbidden();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function suratPayload(array $overrides = []): array
    {
        return array_merge([
            'tanggal_surat' => now()->toDateString(),
            'tanggal_terima' => now()->toDateString(),
            'perihal' => 'Permohonan izin operasional',
            'pengirim' => 'PT Contoh Transport',
            'sifat_surat' => 'biasa',
            'status' => 'tercatat',
            'ringkasan' => 'Ringkasan surat masuk untuk pengujian workflow.',
        ], $overrides);
    }

    private function userWithPermission(string $key): User
    {
        $user = User::factory()->create(['is_super_admin' => false]);
        UserPermission::create(['user_id' => $user->id, 'permission_key' => $key]);

        return $user;
    }

    private function userWithUnitPermission(string $key, string $unitKerja): User
    {
        $pegawai = Pegawai::create([
            'nip' => '198001012020'.random_int(1000, 9999),
            'nik' => (string) random_int(1000000000000000, 9999999999999999),
            'nama_lengkap' => 'Kabid '.$unitKerja,
            'tempat_lahir' => 'Samarinda',
            'tanggal_lahir' => '1980-01-01',
            'jenis_kelamin' => 'L',
            'agama' => 'Islam',
            'status_kepegawaian' => 'PNS',
            'alamat_ktp' => 'Jl. Uji No. 1',
            'no_hp' => '081234567890',
        ]);

        JabatanPegawai::create([
            'id_pegawai' => $pegawai->id_pegawai,
            'jabatan' => 'Kepala Bidang',
            'unit_kerja' => $unitKerja,
            'tmt' => now()->toDateString(),
            'status_jabatan' => 'Aktif',
        ]);

        $user = User::factory()->create([
            'is_super_admin' => false,
            'id_pegawai' => $pegawai->id_pegawai,
        ]);

        UserPermission::create(['user_id' => $user->id, 'permission_key' => $key]);

        return $user;
    }
}
