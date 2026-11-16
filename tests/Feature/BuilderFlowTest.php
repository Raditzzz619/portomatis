<?php

namespace Tests\Feature;

use Tests\TestCase;

class BuilderFlowTest extends TestCase
{
    public function test_async_validation_preserves_the_previous_draft_and_returns_field_errors(): void
    {
        $draft = ['template' => 'modern', 'personal' => ['name' => 'Ayu', 'role' => 'Designer']];
        $this->withSession(['builder.draft' => $draft])->withHeaders([
            'Accept' => 'application/json, text/html;q=0.9',
            'X-Requested-With' => 'XMLHttpRequest',
        ])->post('/builder/personal', ['personal' => [
            'name' => 'Ayu Baru', 'role' => 'Designer', 'website' => 'ftp://example.com',
        ]])->assertUnprocessable()->assertJsonValidationErrors('personal.website')
            ->assertSessionHas('builder.draft', $draft);
    }

    public function test_async_save_redirects_to_a_renderable_next_step_with_the_saved_data(): void
    {
        $this->withHeaders([
            'Accept' => 'application/json, text/html;q=0.9',
            'X-Requested-With' => 'XMLHttpRequest',
        ])->post('/builder/personal', ['personal' => [
            'name' => 'Ayu', 'role' => 'Designer', 'location' => 'Kota Bandung, Jawa Barat, Indonesia',
        ]])->assertRedirect('/builder/experience')
            ->assertSessionHas('builder.draft.personal.location', 'Kota Bandung, Jawa Barat, Indonesia');
        $this->get('/builder/experience')->assertOk()
            ->assertSee('data-current-step="experience"', false)->assertDontSee('Selesai mengisi');
    }

    public function test_builder_loads_every_form_section_in_one_swipeable_page(): void
    {
        $this->get('/builder/template')->assertOk()
            ->assertSee('data-step-panel="template"', false)
            ->assertSee('data-step-panel="personal"', false)
            ->assertSee('data-step-panel="experience"', false)
            ->assertSee('data-step-panel="projects"', false)
            ->assertSee('data-step-panel="preview"', false)
            ->assertSee('data-step-viewport', false)
            ->assertSee('data-step-track', false)
            ->assertSee('data-carousel-prev', false)
            ->assertSee('data-carousel-next', false)
            ->assertSee('name="template"', false)
            ->assertDontSee('name="template_selection"', false)
            ->assertDontSee('data-step-next', false)
            ->assertDontSee('data-step-back', false)
            ->assertDontSee('template-continue', false);
    }

    public function test_preview_provides_website_and_pdf_formats_with_separate_downloads(): void
    {
        $this->withSession(['builder.draft' => [
            'template' => 'modern', 'personal' => ['name' => 'Ayu', 'role' => 'Designer'],
        ]])->get('/builder/preview')->assertOk()
            ->assertSee('data-builder-step="template"', false)
            ->assertSee('data-builder-step="personal"', false)
            ->assertSee('role="tablist"', false)
            ->assertSee('id="preview-website"', false)->assertSee('id="preview-pdf"', false)
            ->assertSee('data-pdf-src="'.route('builder.pdf').'"', false)
            ->assertSee(route('builder.download.pdf'))->assertSee(route('builder.download'))
            ->assertSee('alamat tautan tertulis');
    }
}
