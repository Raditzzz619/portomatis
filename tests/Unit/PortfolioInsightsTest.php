<?php

namespace Tests\Unit;

use App\Services\PortfolioInsights;
use PHPUnit\Framework\TestCase;

class PortfolioInsightsTest extends TestCase
{
    public function test_empty_and_sparse_portfolios_are_not_normalized_to_a_perfect_score(): void
    {
        $service = new PortfolioInsights;
        $this->assertSame(0.0, $service->analyze([])['assessment']['score']);
        $sparse = $service->analyze(['personal' => ['name' => 'Ayu', 'role' => 'Developer']]);
        $this->assertSame(4.0, $sparse['assessment']['score']);
        $this->assertNotEmpty($sparse['assessment']['suggestions']);
        $this->assertEmpty($sparse['jobs']);
    }

    public function test_saw_uses_weighted_normalization_against_the_ideal_and_reports_missing_criteria(): void
    {
        $draft = [
            'personal' => ['name' => 'Ayu', 'role' => 'Web Developer', 'bio' => str_repeat('Membangun website. ', 8), 'email' => 'a@example.com', 'phone' => '081234', 'location' => 'Bandung'],
            'skills' => ['HTML', 'CSS', 'JavaScript'],
            'education' => [['school' => 'Universitas', 'degree' => 'Informatika', 'period' => '2020–2024']],
            'experience' => [['company' => 'Studio', 'role' => 'Developer', 'period' => '2024', 'description' => str_repeat('Mengembangkan website dengan HTML dan CSS. ', 3)]],
            'projects' => [['title' => 'Website', 'description' => str_repeat('Saya membangun website dengan HTML dan CSS untuk meningkatkan hasil penjualan. ', 3), 'url' => 'https://example.com']],
        ];
        $service = new PortfolioInsights;
        $report = $service->analyze($draft)['assessment'];
        $this->assertSame(100.0, $report['score']);
        $this->assertEmpty($report['suggestions']);
        unset($draft['personal']['email'], $draft['projects'][0]['url']);
        $report = $service->analyze($draft)['assessment'];
        $this->assertSame(88.0, $report['score']); // 20*(4/5) + 40*(5/5) + 40*(4/5).
        $this->assertCount(2, $report['suggestions']);
    }

    public function test_jobs_combine_all_three_sources_and_do_not_infer_mastery_or_match_partial_words(): void
    {
        $report = (new PortfolioInsights)->analyze([
            'skills' => ['Canva', 'Figma', 'HTML', 'CSS', 'JavaScript'],
            'experience' => [['role' => 'Mengelola website', 'description' => 'Membangun antarmuka dengan HTML dan CSS.']],
            'projects' => [['title' => 'Website toko', 'description' => 'Saya membuat website dengan JavaScript dan Figma.']],
        ]);
        $this->assertSame('Front-end Developer', $report['jobs'][0]['job']);
        $this->assertSame(3, $report['jobs'][0]['sources']);
        $this->assertArrayHasKey('Pengalaman', $report['jobs'][0]['evidence']);
        $this->assertArrayHasKey('Proyek', $report['jobs'][0]['evidence']);
        $none = (new PortfolioInsights)->analyze(['skills' => ['reactive', 'webbing', 'figmatic']]);
        $this->assertEmpty($none['jobs']);
    }
}
