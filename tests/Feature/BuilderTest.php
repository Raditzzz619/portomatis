<?php

namespace Tests\Feature;

use App\Models\PublishedPortfolio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_builder_steps_and_required_personal_data(): void
    {
        $this->get('/builder')->assertRedirect('/builder/template');
        foreach (['template', 'personal', 'experience', 'projects'] as $step) {
            $this->get('/builder/'.$step)->assertOk()->assertSee('Simpan & lanjut', false);
        }
        $this->get('/builder/preview')->assertRedirect('/builder/personal');
        $this->get('/builder/unknown')->assertNotFound();
        $this->post('/builder/personal', ['personal' => ['name' => '', 'role' => '']])
            ->assertSessionHasErrors(['personal.name', 'personal.role']);
        $this->post('/builder/template', ['template' => 'unknown'])->assertSessionHasErrors('template');
    }

    public function test_complete_flow_exports_actual_content_and_publication_is_explicit(): void
    {
        $this->post('/builder/template', ['template' => 'tech'])->assertRedirect('/builder/personal');
        $this->post('/builder/personal', ['personal' => [
            'name' => 'Ayu Putri', 'role' => 'Product Designer', 'bio' => 'Membuat produk yang bermakna.',
            'email' => 'ayu@example.com',
        ]])->assertRedirect('/builder/experience');
        $this->post('/builder/experience', [
            'education' => [['school' => 'Universitas Indonesia', 'degree' => 'Desain', 'period' => '2020 - 2024'], ['school' => '']],
            'experience' => [['company' => 'Studio Kreatif', 'role' => 'Designer', 'period' => '2024', 'description' => 'Meningkatkan konversi.']],
        ])->assertRedirect('/builder/projects')->assertSessionHas('builder.draft.education', fn ($rows) => count($rows) === 1);
        $this->post('/builder/projects', ['skills' => 'Figma, CSS, Figma', 'projects' => [
            ['title' => 'Aplikasi Ruang', 'description' => 'Riset dan desain.', 'url' => 'https://example.com'],
        ]])->assertRedirect('/builder/preview')->assertSessionHas('builder.draft.skills', ['Figma', 'CSS']);
        $this->get('/builder/preview')->assertOk()->assertSee('Ayu Putri');
        $download = $this->get('/builder/download');
        $download->assertOk()->assertHeader('Content-Disposition', 'attachment; filename="ayu-putri-portofolio.html"')
            ->assertSee('theme-tech')->assertSee('Aplikasi Ruang')->assertSee('Universitas Indonesia')->assertSee('Studio Kreatif')->assertSee('Figma');
        $this->assertDatabaseCount('published_portfolios', 0);
        $this->post('/builder/publish')->assertRedirect('/builder/preview')->assertSessionHas('builder.share_url');
        $portfolio = PublishedPortfolio::firstOrFail();
        $this->get('/p/'.$portfolio->token)->assertOk()->assertSee('Ayu Putri');
        $this->post('/builder/personal', ['personal' => ['name' => 'Nama Baru', 'role' => 'Designer']]);
        $this->get('/p/'.$portfolio->token)->assertSee('Ayu Putri')->assertDontSee('Nama Baru');
        $this->post('/builder/publish')->assertRedirect('/builder/preview');
        $this->assertDatabaseCount('published_portfolios', 1);
        $this->get('/p/'.$portfolio->token)->assertSee('Nama Baru');
    }

    public function test_unsafe_links_and_incomplete_rows_are_rejected(): void
    {
        $this->post('/builder/personal', ['personal' => ['name' => 'Ayu', 'role' => 'Designer', 'website' => 'javascript:alert(1)']])
            ->assertSessionHasErrors('personal.website');
        $this->post('/builder/projects', ['projects' => [['title' => 'Test', 'url' => 'data:text/html,test']]])
            ->assertSessionHasErrors('projects.0.url');
        $this->post('/builder/experience', ['education' => [['degree' => 'Design']]])
            ->assertSessionHasErrors('education.0.school');
        $this->post('/builder/projects', ['projects' => [['description' => 'Some project']]])
            ->assertSessionHasErrors('projects.0.title');
        $this->get('/builder/download')->assertStatus(422);
        $this->post('/builder/publish')->assertStatus(422);
        $this->get('/p/00000000-0000-4000-8000-000000000000')->assertNotFound();
    }

    public function test_user_content_is_escaped_in_exports_and_templates_are_distinct(): void
    {
        foreach (['minimalist', 'modern', 'tech'] as $template) {
            $this->withSession(['builder.draft' => ['template' => $template, 'personal' => [
                'name' => '<script>alert(1)</script>', 'role' => 'Designer',
            ]]])->get('/builder/download')->assertOk()->assertSee('theme-'.$template)
                ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        }
    }
}
