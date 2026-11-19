<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Rbac;

class AuthTest extends ApiTestCase
{
    public function test_login_returns_a_token_and_the_user_payload(): void
    {
        $user = User::factory()->create(['password' => 'secret-password']);
        $user->assignRole(Rbac::ADMINISTRATOR);

        $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'secret-password'])
            ->assertOk()
            ->assertJsonStructure(['access_token', 'user' => ['id', 'name', 'email'], 'user_roles', 'user_permissions'])
            ->assertJsonPath('user_roles', [Rbac::ADMINISTRATOR]);

        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_wrong_password_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'nope'])
            ->assertStatus(401)
            ->assertJsonPath('message', 'Invalid email or password.');
    }

    public function test_inactive_account_cannot_sign_in(): void
    {
        $user = User::factory()->inactive()->create(['password' => 'secret-password']);

        $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'secret-password'])->assertStatus(403);
    }

    public function test_init_needs_a_token(): void
    {
        $this->getJson('/api/auth/init')->assertStatus(401);
    }

    public function test_a_guest_without_a_json_accept_header_gets_401_not_a_login_redirect(): void
    {
        // a browser tab sends Accept: text/html; there is no `login` route to redirect to
        $this->get('/api/users', ['Accept' => 'text/html'])
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_init_and_logout_with_a_real_token(): void
    {
        $user = User::factory()->create(['password' => 'secret-password']);
        $token = $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'secret-password'])->json('access_token');

        $this->withToken($token)->getJson('/api/auth/init')->assertOk()->assertJsonPath('user.email', $user->email);
        $this->withToken($token)->postJson('/api/auth/logout')->assertOk();

        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_profile_password_change_needs_the_current_password(): void
    {
        $user = User::factory()->create(['password' => 'secret-password']);
        $token = $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'secret-password'])->json('access_token');

        $this->withToken($token)->putJson('/api/auth/profile', [
            'name' => 'New Name', 'password' => 'another-password', 'password_confirmation' => 'another-password',
        ])->assertStatus(422)->assertJsonValidationErrors('current_password');

        $this->withToken($token)->putJson('/api/auth/profile', [
            'name' => 'New Name', 'current_password' => 'secret-password',
            'password' => 'another-password', 'password_confirmation' => 'another-password',
        ])->assertOk()->assertJsonPath('user.name', 'New Name');
    }
}
