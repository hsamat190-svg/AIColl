<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LabTest extends TestCase
{
    use RefreshDatabase;

    public function test_lab_redirects_guests(): void
    {
        $this->get(route('lab.index'))->assertRedirect(route('login'));
    }

    public function test_lab_displays_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('lab.index'))->assertOk();
    }
}
