<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\BuilderPortfolio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountsAndAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_and_members_cannot_access_admin_or_private_drafts(): void
    {
        $this->get('/builder')->assertRedirect('/login');
        $this->get('/admin')->assertRedirect('/login');
        $this->get('/builder/download/pdf')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
        $this->get('/portfolio')->assertForbidden();
        $other = User::factory()->create();
        $portfolio = $other->portfolios()->create(['content' => ['personal' => ['name' => 'Rahasia']]]);
        $this->get('/builder/resume/'.$portfolio->id)->assertNotFound();
    }

    public function test_registration_ignores_admin_role_hashes_password_and_requires_consent(): void
    {
        $data = ['name' => 'Ayu', 'email' => 'AYU@example.com', 'password' => 'rahasia123', 'password_confirmation' => 'rahasia123', 'role' => 'admin'];
        $this->post('/register', $data)->assertSessionHasErrors('consent');
        $this->assertDatabaseMissing('users', ['email' => 'ayu@example.com']);
        $this->post('/register', $data + ['consent' => '1'])->assertRedirect('/builder');
        $user = User::where('email', 'ayu@example.com')->firstOrFail();
        $this->assertSame('user', $user->role);
        $this->assertTrue(Hash::check('rahasia123', $user->password));
        $this->assertDatabaseHas('user_activities', ['user_id' => $user->id, 'action' => 'registered']);
        $this->get('/admin')->assertForbidden();
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_seeded_admin_can_login_and_seeding_again_preserves_the_password(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->firstOrFail();
        $this->post('/login', ['email' => $admin->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->post('/login', ['email' => $admin->email, 'password' => 'admin123'])->assertRedirect('/admin');
        $this->get('/admin')->assertOk()->assertSee('Aktivitas & pengguna', false);
        $this->get('/builder')->assertForbidden();
        $admin->update(['password' => 'different123']);
        $this->seed();
        $this->assertTrue(Hash::check('different123', $admin->fresh()->password));
        $this->assertDatabaseCount('users', 1);
    }

    public function test_drafts_survive_logout_email_fields_stay_separate_and_new_users_get_no_previous_data(): void
    {
        $user = User::factory()->create(['email' => 'login@example.com', 'password' => 'rahasia123']);
        $this->actingAs($user)->post('/builder/personal', ['personal' => ['name' => 'Ayu Portfolio', 'role' => 'Developer', 'email' => 'portfolio@example.com']])->assertRedirect();
        $this->post('/builder/projects', ['skills' => 'HTML, CSS, JavaScript'])->assertRedirect('/builder/preview');
        $this->assertDatabaseCount('builder_portfolios', 1);
        $this->assertSame('login@example.com', $user->fresh()->email);
        $this->post('/logout');
        $this->post('/login', ['email' => 'login@example.com', 'password' => 'rahasia123'])->assertRedirect('/builder');
        $this->get('/builder/preview')->assertOk()->assertSee('Ayu Portfolio')->assertSee('PENILAIAN PORTOFOLIO PDF');
        $this->post('/builder/new')->assertRedirect('/builder/template')->assertSessionHas('builder.draft', ['template' => 'minimalist']);
        $this->assertDatabaseCount('builder_portfolios', 2);
        $this->post('/logout');
        $this->post('/login', ['email' => 'login@example.com', 'password' => 'rahasia123']);
        $this->get('/builder/template')->assertOk()->assertViewHas('draft', ['template' => 'minimalist']);
        $this->get('/builder/preview')->assertRedirect('/builder/personal');
        $this->post('/logout');
        $other = User::factory()->create(['password' => 'rahasia123']);
        $this->post('/login', ['email' => $other->email, 'password' => 'rahasia123']);
        $this->get('/builder/personal')->assertOk()->assertDontSee('Ayu Portfolio')->assertDontSee('portfolio@example.com');
        $this->actingAs(User::where('role', 'admin')->first())->get('/admin')->assertOk()
            ->assertSee('login@example.com')->assertSee('portfolio@example.com');
    }

    public function test_admin_searches_account_and_portfolio_fields_without_leaking_other_users(): void
    {
        $ayu = User::factory()->create(['name' => 'Ayu Account', 'email' => 'ayu-login@example.com']);
        $ayu->portfolios()->create(['content' => ['personal' => ['name' => 'Ayu Portfolio', 'role' => 'Web developer', 'email' => 'work-contact@example.com'],
            'skills' => ['Figma'], 'experience' => [['company' => 'Studio A', 'role' => 'Designer', 'description' => 'Rahasia & <script>']],
            'projects' => [['title' => 'Proyek A', 'url' => 'https://example.com']]]]);
        $budi = User::factory()->create(['name' => 'Budi Different', 'email' => 'budi@example.com']);
        $budi->portfolios()->create(['content' => ['personal' => ['name' => 'Budi Portfolio', 'email' => 'other-contact@example.com']]]);
        $this->actingAs(User::where('role', 'admin')->first());
        foreach (['Ayu Account', 'ayu-login', 'Ayu Portfolio', 'work-contact'] as $term) {
            $this->get('/admin?q='.urlencode($term))->assertOk()->assertSee('ayu-login@example.com')->assertSee('work-contact@example.com')->assertDontSee('other-contact@example.com');
        }
        foreach (['nonexistent', '%', '_', "' OR 1=1 --"] as $term) {
            $this->get('/admin?q='.urlencode($term))->assertOk()->assertSee('Pengguna yang dicari tidak ada.');
        }
        $this->get('/admin/users/'.$ayu->id)->assertOk()->assertSee('Data login pengguna')->assertSee('Email portofolio')
            ->assertSee('Studio A')->assertSee('Proyek A')->assertSee('Figma')->assertSee('Rahasia &amp; &lt;script&gt;', false)
            ->assertDontSee('other-contact@example.com')->assertDontSee($ayu->password);
    }

    public function test_calendar_counts_use_unique_users_timezone_boundaries_and_exclude_admin(): void
    {
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-11-16 12:00:00', 'Asia/Singapore'));
        $first = User::factory()->create();
        $second = User::factory()->create();
        $admin = User::where('role', 'admin')->first();
        foreach (['2026-11-16 04:00:00', '2026-11-16 03:00:00'] as $time) {
            $first->activities()->create(['action' => 'login', 'created_at' => $time]);
        }
        // 23:59 on Sunday locally: month and year only, not the new week/day.
        $second->activities()->create(['action' => 'login', 'created_at' => '2026-11-15 15:59:00']);
        $admin->activities()->create(['action' => 'login', 'created_at' => now()]);
        $this->actingAs($admin)->get('/admin')->assertOk()->assertViewHas('stats', [
            'Hari ini' => 1, 'Minggu ini' => 1, 'Bulan ini' => 2, 'Tahun ini' => 2,
        ])->assertViewHas('online', 1);
        $this->travelBack();
    }

    public function test_heartbeat_is_rate_limited_and_does_not_duplicate_users(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/activity/heartbeat')->assertNoContent();
        $this->post('/activity/heartbeat')->assertNoContent();
        $this->assertSame(1, $user->activities()->count());
    }
}
