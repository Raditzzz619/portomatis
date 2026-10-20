<?php

namespace Tests\Feature;

use App\Services\PortfolioPdf;
use Tests\TestCase;

class PdfExportTest extends TestCase
{
    private function draft(): array
    {
        return [
            'template' => 'tech',
            'personal' => [
                'name' => 'Ayu Putri', 'role' => 'Product Designer',
                'bio' => 'Mendesain produk yang mudah digunakan.',
                'location' => 'Bandung, Jawa Barat', 'email' => 'ayu@example.com',
                'phone' => '+62 812 3456 7890', 'website' => 'https://example.com/ayu',
                'linkedin' => 'https://linkedin.com/in/ayu',
            ],
            'education' => [['school' => 'Universitas Indonesia', 'degree' => 'Desain', 'period' => '2020 - 2024']],
            'experience' => [['company' => 'Studio Kreatif', 'role' => 'Designer', 'description' => 'Meningkatkan kemudahan penggunaan.', 'period' => '2024 - sekarang']],
            'skills' => ['Figma', 'Riset pengguna'],
            'projects' => [['title' => 'Aplikasi Ruang', 'description' => 'Riset dan desain ruang kerja.', 'url' => 'https://example.com/projects/ruang']],
        ];
    }

    public function test_preview_and_download_return_actual_pdf_with_correct_disposition(): void
    {
        foreach (['builder.pdf' => 'inline', 'builder.download.pdf' => 'attachment'] as $route => $disposition) {
            $response = $this->withSession(['builder.draft' => $this->draft()])->get(route($route));
            $response->assertOk()->assertHeader('Content-Type', 'application/pdf')
                ->assertHeader('Content-Disposition', $disposition.'; filename="ayu-putri-portofolio.pdf"')
                ->assertHeader('X-Content-Type-Options', 'nosniff');
            $this->assertStringStartsWith('%PDF-', $response->getContent());
            $this->assertStringContainsString('%%EOF', $response->getContent());
            $this->assertStringContainsString('/MediaBox [0.000 0.000 595.280 841.890]', $response->getContent());
            $this->assertStringContainsString('/URI (https://example.com/projects/ruang)', $response->getContent());
            $this->assertStringContainsString('/URI (https://example.com/ayu)', $response->getContent());
            $this->assertStringContainsString('/URI (https://linkedin.com/in/ayu)', $response->getContent());
        }
    }

    public function test_pdf_endpoints_require_a_completed_personal_draft(): void
    {
        $this->get(route('builder.pdf'))->assertStatus(422);
        $this->get(route('builder.download.pdf'))->assertStatus(422);
    }

    public function test_pdf_layout_contains_complete_content_and_visible_link_references(): void
    {
        $html = app(PortfolioPdf::class)->html($this->draft());
        foreach (['Ayu Putri', 'Product Designer', 'Bandung, Jawa Barat', 'ayu@example.com', '+62 812 3456 7890',
            'Universitas Indonesia', 'Desain', 'Studio Kreatif', 'Meningkatkan kemudahan penggunaan.',
            'Figma', 'Riset pengguna', 'Aplikasi Ruang', 'Riset dan desain ruang kerja.'] as $content) {
            $this->assertStringContainsString($content, $html);
        }
        $this->assertStringContainsString('Referensi karya: <a href="https://example.com/projects/ruang">https://example.com/projects/ruang</a>', $html);
    }

    public function test_pdf_escapes_content_and_rejects_external_images_and_unsafe_links(): void
    {
        $draft = $this->draft();
        $draft['personal']['name'] = '<script>alert(1)</script>';
        $draft['personal']['photo'] = 'http://127.0.0.1/private-photo.png';
        $draft['personal']['website'] = 'javascript:alert(1)';
        $draft['projects'][0]['description'] = '<img src="file:///etc/passwd">';
        $draft['projects'][0]['url'] = 'data:text/html,<script>alert(2)</script>';
        $html = app(PortfolioPdf::class)->html($draft);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
        $this->assertStringContainsString('&lt;img src=&quot;file:///etc/passwd&quot;&gt;', $html);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('<img ', $html);
        $this->assertStringNotContainsString('href="javascript:', $html);
        $this->assertStringNotContainsString('href="data:', $html);
        $response = $this->withSession(['builder.draft' => $draft])->get(route('builder.pdf'))->assertOk();
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->assertStringNotContainsString('/JavaScript', $response->getContent());
    }

    public function test_long_content_flows_to_multiple_pages(): void
    {
        $draft = $this->draft();
        $draft['projects'] = array_map(static fn ($index) => [
            'title' => 'Proyek '.$index,
            'description' => str_repeat('Penjelasan hasil riset dan dampak proyek kepada pengguna. ', 30),
            'url' => 'https://example.com/projects/'.$index,
        ], range(1, 20));
        $response = $this->withSession(['builder.draft' => $draft])->get(route('builder.pdf'))->assertOk();
        preg_match_all('/\/Type\s*\/Page\b/', $response->getContent(), $pages);
        $this->assertGreaterThan(1, count($pages[0]));
        $this->assertStringContainsString('/URI (https://example.com/projects/1)', $response->getContent());
        $this->assertStringContainsString('/URI (https://example.com/projects/20)', $response->getContent());
    }

    public function test_uploaded_raster_photo_can_be_embedded_in_the_pdf(): void
    {
        $draft = $this->draft();
        $draft['personal']['photo'] = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAgAAAAICAIAAABLbSncAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAAFElEQVQImWMUbbBlwAaYsIoOWgkAqUwA4uT35ZIAAAAASUVORK5CYII=';
        $response = $this->withSession(['builder.draft' => $draft])->get(route('builder.pdf'))->assertOk();
        $this->assertStringContainsString('/Subtype /Image', $response->getContent());
    }
}
