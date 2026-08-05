<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AccountManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_guest_can_register_and_verification_is_required(): void
    {
        Notification::fake();

        $response = $this->post(route('register.store'), [
            'name' => 'María Pérez',
            'email' => 'maria@example.com',
            'password' => 'ClaveSegura1',
            'password_confirmation' => 'ClaveSegura1',
        ]);

        $user = User::where('email', 'maria@example.com')->firstOrFail();
        $response->assertRedirect(route('verification.notice'));
        $this->assertAuthenticatedAs($user);
        $this->assertFalse($user->hasVerifiedEmail());
        $this->assertFalse($user->is_admin);
        $this->assertTrue($user->is_active);
        Notification::assertSentTo($user, VerifyEmailNotification::class, function (VerifyEmailNotification $notification) use ($user): bool {
            $mail = $notification->toMail($user);

            return $notification->locale === 'es'
                && $mail->subject === 'Verifica tu correo electrónico'
                && $mail->actionText === 'Verificar mi correo'
                && str_contains($mail->greeting, $user->name);
        });
        $this->get(route('songs.index'))->assertRedirect(route('verification.notice'));
    }

    public function test_user_can_verify_email_with_signed_link(): void
    {
        $user = User::factory()->unverified()->create();
        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(30), [
            'id' => $user->id,
            'hash' => sha1($user->email),
        ]);

        $this->actingAs($user)->get($url)->assertRedirect(route('dashboard'));
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_registration_rejects_duplicate_email_and_weak_password(): void
    {
        User::factory()->create(['email' => 'used@example.com']);

        $this->post(route('register.store'), [
            'name' => 'Usuario',
            'email' => 'used@example.com',
            'password' => 'weak',
            'password_confirmation' => 'weak',
        ])->assertSessionHasErrors(['email', 'password']);
    }

    public function test_user_can_request_and_complete_password_reset(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'reset@example.com']);
        $token = null;

        $this->post(route('password.email'), ['email' => $user->email])->assertSessionHas('status');
        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token): bool {
            $token = $notification->token;

            return true;
        });

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NuevaClave2',
            'password_confirmation' => 'NuevaClave2',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('NuevaClave2', $user->fresh()->password));
    }

    public function test_user_can_update_profile_and_password(): void
    {
        Notification::fake();
        $user = User::factory()->create(['password' => 'ClaveActual1']);

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Nombre actualizado',
            'email' => 'nuevo@example.com',
        ])->assertRedirect(route('verification.notice'));

        $user->refresh();
        $this->assertSame('Nombre actualizado', $user->name);
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmailNotification::class);

        $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'ClaveActual1',
            'password' => 'OtraClave3',
            'password_confirmation' => 'OtraClave3',
        ])->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('OtraClave3', $user->fresh()->password));
    }

    public function test_inactive_user_cannot_login_or_keep_using_a_session(): void
    {
        $user = User::factory()->create(['is_active' => false, 'password' => 'ClaveActual1']);

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'ClaveActual1'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->actingAs($user)->get(route('profile.edit'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_admin_can_deactivate_another_account_but_not_their_own(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $this->actingAs($admin)->put(route('admin.users.update', $user), ['is_active' => 0])->assertSessionHasNoErrors();
        $this->assertFalse($user->fresh()->is_active);

        $this->actingAs($admin)->put(route('admin.users.update', $admin), ['is_active' => 0])->assertSessionHasErrors('user');
        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_regular_user_cannot_manage_accounts(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
    }
}
