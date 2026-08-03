<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_admin_can_login_and_open_dashboard(): void
    {
        $this->withoutVite();
        $user = User::factory()->create(['is_admin' => true, 'password' => 'Secret123!']);
        $this->post('/login', ['email' => $user->email, 'password' => 'Secret123!'])->assertRedirect(route('dashboard'));
        $this->actingAs($user)->get('/')->assertOk()->assertSee('Resumen de tu biblioteca musical');
    }

    public function test_regular_user_can_login_and_open_dashboard(): void
    {
        $this->withoutVite();
        $user = User::factory()->create(['is_admin' => false, 'password' => 'Secret123!']);
        $this->post('/login', ['email' => $user->email, 'password' => 'Secret123!'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->get('/')->assertOk();
    }
}
