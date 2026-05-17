<?php

namespace Tests\Unit;

use App\Services\SuratKeluarIsiSanitizer;
use Tests\TestCase;

class SuratKeluarIsiSanitizerTest extends TestCase
{
    public function test_sanitize_allows_basic_formatting_and_tables(): void
    {
        $sanitizer = app(SuratKeluarIsiSanitizer::class);

        $html = '<p><strong>Penting</strong></p><table><tr><td>A</td><td>B</td></tr></table><script>alert(1)</script>';

        $clean = $sanitizer->sanitize($html);

        $this->assertStringContainsString('<strong>Penting</strong>', $clean);
        $this->assertStringContainsString('<table>', $clean);
        $this->assertStringNotContainsString('<script>', $clean);
    }

    public function test_plain_text_length_ignores_html_tags(): void
    {
        $sanitizer = app(SuratKeluarIsiSanitizer::class);

        $length = $sanitizer->plainTextLength('<p>'.str_repeat('Ab ', 10).'</p>');

        $this->assertGreaterThanOrEqual(20, $length);
    }

    public function test_for_display_converts_legacy_plain_text(): void
    {
        $sanitizer = app(SuratKeluarIsiSanitizer::class);

        $display = $sanitizer->forDisplay("Baris satu\nBaris dua");

        $this->assertStringContainsString('<br', $display);
        $this->assertStringNotContainsString('<script>', $display);
    }
}
