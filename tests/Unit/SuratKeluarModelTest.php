<?php

namespace Tests\Unit;

use App\Models\SuratKeluar;
use PHPUnit\Framework\TestCase;

class SuratKeluarModelTest extends TestCase
{
    public function test_editable_statuses(): void
    {
        $surat = new SuratKeluar(['status' => 'draft']);
        $this->assertTrue($surat->isEditable());
        $this->assertTrue($surat->isDeletable());

        $surat->status = 'dikirim';
        $this->assertFalse($surat->isEditable());
        $this->assertTrue($surat->canBePrinted());
    }

    public function test_isi_templates_exist_for_each_jenis(): void
    {
        $templates = SuratKeluar::isiTemplates();
        foreach (array_keys(SuratKeluar::jenisSuratOptions()) as $jenis) {
            $this->assertArrayHasKey($jenis, $templates);
            $this->assertNotEmpty($templates[$jenis]);
        }
    }
}
