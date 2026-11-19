<?php

namespace Tests\Concerns;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

trait AuthenticatedBuilder
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }
}
