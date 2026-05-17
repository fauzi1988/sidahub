<?php

namespace Tests\Feature;

use App\Models\SuratKeluar;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuratKeluarWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('Workflow feature tests require MySQL (full migration chain).');
        }

        parent::setUp();
    }

    public function test_operator_cannot_set_status_directly_on_update(): void
    {
        $user = $this->userWithPermission('kepegawaian.persuratan.surat_keluar');
        $surat = SuratKeluar::create($this->suratPayload(['status' => 'draft', 'created_by_user_id' => $user->id]));

        $response = $this->actingAs($user)->put(route('persuratan-surat-keluar.update', $surat), [
            'tanggal_surat' => now()->toDateString(),
            'perihal' => 'Uji perihal surat dinas',
            'tujuan_surat' => 'Dinas PU',
            'jenis_surat' => 'surat_dinas',
            'sifat_surat' => 'biasa',
            'prioritas' => 'normal',
            'isi_surat' => str_repeat('Isi surat uji coba workflow. ', 3),
        ]);

        $response->assertRedirect();
        $surat->refresh();
        $this->assertSame('draft', $surat->status);
    }

    public function test_full_workflow_until_dikirim(): void
    {
        $operator = $this->userWithPermission('kepegawaian.persuratan.surat_keluar');
        $kabid = $this->userWithPermission('kepegawaian.persuratan.approve_kabid');
        $sekretariat = $this->userWithPermission('kepegawaian.persuratan.approve_sekretariat');
        $kadis = $this->userWithPermission('kepegawaian.persuratan.approve_kadis');

        $surat = SuratKeluar::create($this->suratPayload([
            'status' => 'draft',
            'created_by_user_id' => $operator->id,
        ]));

        $this->actingAs($operator)->post(route('persuratan-surat-keluar.submit', $surat));
        $surat->refresh();
        $this->assertSame('menunggu_review_substansi', $surat->status);

        $this->actingAs($kabid)->post(route('persuratan-surat-keluar.kabid-approve', $surat));
        $surat->refresh();
        $this->assertSame('menunggu_verifikasi', $surat->status);

        $this->actingAs($sekretariat)->post(route('persuratan-surat-keluar.sekretariat-forward', $surat));
        $surat->refresh();
        $this->assertSame('menunggu_ttd', $surat->status);

        $ttdId = \App\Models\ManajemenTtd::create([
            'nama_ttd' => 'TTD Kadis',
            'jenis_ttd' => 'elektronik',
            'pemilik_ttd' => 'Kadis',
            'jabatan_pemilik' => 'Kepala Dinas',
            'is_active' => true,
        ])->id_ttd;

        $this->actingAs($kadis)->post(route('persuratan-surat-keluar.kadis-sign', $surat), [
            'jenis_ttd' => 'elektronik',
            'ttd_management_id' => $ttdId,
        ]);
        $surat->refresh();
        $this->assertSame('disetujui', $surat->status);

        $this->actingAs($sekretariat)->post(route('persuratan-surat-keluar.sekretariat-number-send', $surat), [
            'nomor_surat' => '001/DISHUB/'.date('Y'),
            'tanggal_kirim' => now()->toDateString(),
        ]);
        $surat->refresh();
        $this->assertSame('dikirim', $surat->status);
        $this->assertNotNull($surat->verification_code);
        $this->assertTrue($surat->canBePrinted());
    }

    public function test_destroy_blocked_after_submit(): void
    {
        $user = $this->userWithPermission('kepegawaian.persuratan.surat_keluar');
        $surat = SuratKeluar::create($this->suratPayload([
            'status' => 'menunggu_review_substansi',
            'created_by_user_id' => $user->id,
        ]));

        $this->actingAs($user)
            ->delete(route('persuratan-surat-keluar.destroy', $surat))
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
            'perihal' => 'Uji perihal surat',
            'tujuan_surat' => 'Instansi X',
            'jenis_surat' => 'surat_dinas',
            'sifat_surat' => 'biasa',
            'prioritas' => 'normal',
            'status' => 'draft',
            'isi_surat' => str_repeat('Isi surat pengujian workflow modul. ', 4),
        ], $overrides);
    }

    private function userWithPermission(string $key): User
    {
        $user = User::factory()->create(['is_super_admin' => false]);
        UserPermission::create(['user_id' => $user->id, 'permission_key' => $key]);

        return $user;
    }
}
