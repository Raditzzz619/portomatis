<?php

namespace Tests\Feature;

use App\Support\DomicileRegions;
use Tests\TestCase;

class DomicileTest extends TestCase
{
    public function test_domicile_selection_covers_all_provinces_and_regencies_without_a_provider_request(): void
    {
        \Illuminate\Support\Facades\Http::preventStrayRequests();
        $provinces = DomicileRegions::provinces();
        $this->assertCount(38, $provinces);
        $this->assertCount(514, array_merge(...array_column($provinces, 'regencies')));
        $this->assertContains('Papua Barat Daya', array_column($provinces, 'name'));
        foreach ($provinces as $province) {
            foreach ($province['regencies'] as $regency) {
                $this->assertLessThanOrEqual(120, mb_strlen(DomicileRegions::label($province, $regency)));
                $this->assertStringStartsWith($province['id'].'.', $regency['id']);
            }
        }

        $this->get('/builder/personal')->assertOk()
            ->assertSee('Domisili')->assertSee('Provinsi')->assertSee('Kabupaten / kota')
            ->assertSee('Papua Barat Daya')->assertSee('Kota Bandung')
            ->assertSee('name="personal[location]"', false)
            ->assertDontSee('id="personal-location" name="personal[location]" type="text"', false);
    }

    public function test_a_saved_regency_is_restored_and_optional_domicile_can_be_cleared(): void
    {
        $location = 'Kota Bandung, Jawa Barat, Indonesia';
        $this->withSession(['builder.draft' => ['personal' => ['name' => 'Ayu', 'role' => 'Designer', 'location' => $location]]])
            ->get('/builder/personal')->assertOk()
            ->assertSee('value="'.$location.'" data-regency="32.73" selected', false);

        $this->post('/builder/personal', ['personal' => ['name' => 'Ayu', 'role' => 'Designer', 'location' => '']])
            ->assertSessionHasNoErrors()->assertSessionHas('builder.draft.personal', fn ($personal) => empty($personal['location']));
    }

    public function test_legacy_domicile_is_preserved_and_escaped_until_replaced(): void
    {
        $legacyLocation = 'Jakarta <script>alert(1)</script>';
        $this->withSession(['builder.draft' => ['personal' => ['location' => $legacyLocation]]])
            ->get('/builder/personal')->assertOk()->assertSee('Domisili tersimpan:')
            ->assertSee('Jakarta &lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee($legacyLocation, false);

        $location = 'Kota Bandung, Jawa Barat, Indonesia';
        $this->post('/builder/personal', ['personal' => ['name' => 'Ayu', 'role' => 'Designer', 'location' => $location]])
            ->assertSessionHasNoErrors()->assertSessionHas('builder.draft.personal.location', $location)
            ->assertSessionMissing('builder.draft.personal.latitude')->assertSessionMissing('builder.draft.personal.longitude');
    }

    public function test_validation_failure_restores_the_submitted_domicile_choice(): void
    {
        $location = 'Kota Bandung, Jawa Barat, Indonesia';
        $this->from('/builder/personal')->post('/builder/personal', ['personal' => ['name' => '', 'role' => 'Designer', 'location' => $location]])
            ->assertSessionHasErrors('personal.name');
        $this->get('/builder/personal')->assertOk()
            ->assertSee('value="'.$location.'" data-regency="32.73" selected', false);
    }
}
