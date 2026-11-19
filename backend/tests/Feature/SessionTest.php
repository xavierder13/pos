<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckSessionActivity;
use App\Models\SessionSetting;
use App\Models\User;
use App\Support\Rbac;
use Illuminate\Support\Facades\Cache;

// Session Settings (idle time / session duration) and "require password change".
class SessionTest extends ApiTestCase
{
    private function signIn(?User $user = null): array
    {
        $user ??= User::factory()->create(['password' => 'secret-password']);
        $token = $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'secret-password'])
            ->assertOk()->assertJsonStructure(['session' => ['idle_timeout_minutes', 'session_lifetime_minutes']])
            ->json('access_token');

        return [$user, $token];
    }

    public function test_idle_session_ends_with_its_message(): void
    {
        SessionSetting::current()->update(['idle_timeout_minutes' => 10]);
        [$user, $token] = $this->signIn();

        $this->travel(9)->minutes();
        $this->withToken($token)->getJson('/api/auth/ping')->assertOk();   // activity: the idle clock restarts

        $this->travel(9)->minutes();
        $this->withToken($token)->getJson('/api/auth/ping')->assertOk();

        $this->travel(11)->minutes();
        $this->withToken($token)->getJson('/api/auth/init')
            ->assertStatus(401)->assertJsonPath('message', CheckSessionActivity::IDLE_MESSAGE);
        $this->assertSame(0, $user->tokens()->count(), 'the token is deleted');
    }

    public function test_passive_calls_do_not_keep_the_session_alive(): void
    {
        SessionSetting::current()->update(['idle_timeout_minutes' => 10]);
        [, $token] = $this->signIn();

        $this->travel(6)->minutes();
        $this->withToken($token)->withHeader('X-Session-Check', 'passive')->getJson('/api/notifications/unread-count')->assertOk();
        $this->travel(6)->minutes();

        $this->withToken($token)->getJson('/api/auth/init')->assertStatus(401);
    }

    public function test_session_duration_ends_even_when_active(): void
    {
        SessionSetting::current()->update(['idle_timeout_minutes' => 30, 'session_lifetime_minutes' => 60]);
        [, $token] = $this->signIn();

        foreach (range(1, 3) as $i) {
            $this->travel(20)->minutes();
            $response = $this->withToken($token)->getJson('/api/auth/ping');
        }
        $response->assertStatus(401)->assertJsonPath('message', CheckSessionActivity::LIFETIME_MESSAGE);
    }

    public function test_settings_are_editable_without_code_and_validated(): void
    {
        $this->actingAsAdmin();

        $this->getJson('/api/session-settings')->assertOk()->assertJsonPath('data.idle_timeout_minutes', 30);

        $this->putJson('/api/session-settings', ['idle_timeout_minutes' => 60, 'session_lifetime_minutes' => 30])
            ->assertStatus(422)->assertJsonValidationErrors('session_lifetime_minutes');

        $this->putJson('/api/session-settings', ['idle_timeout_minutes' => 15, 'session_lifetime_minutes' => 240])
            ->assertOk()->assertJsonPath('data.idle_timeout_minutes', 15);

        // applied at once (the cached values were refreshed)
        $this->assertSame(15, SessionSetting::values()['idle_timeout_minutes']);
        $this->getJson('/api/audit-trail?record=Session%20Settings')->assertOk()->assertJsonPath('meta.total', 1);
    }

    public function test_session_settings_need_their_permission(): void
    {
        $this->actingAsUserWith(['user-list']);

        $this->getJson('/api/session-settings')->assertStatus(403);
    }

    public function test_required_password_change_blocks_everything_else_until_done(): void
    {
        $user = User::factory()->create(['password' => 'secret-password', 'must_change_password' => true]);
        $user->assignRole(Rbac::ADMINISTRATOR);
        [, $token] = $this->signIn($user);

        $this->withToken($token)->getJson('/api/auth/init')->assertOk()->assertJsonPath('user.must_change_password', true);
        $this->withToken($token)->getJson('/api/users')
            ->assertStatus(403)->assertJsonPath('code', 'password_change_required');

        $this->withToken($token)->putJson('/api/auth/profile', [
            'name' => $user->name, 'current_password' => 'secret-password',
            'password' => 'brand-new-pass', 'password_confirmation' => 'brand-new-pass',
        ])->assertOk()->assertJsonPath('user.must_change_password', false);

        $this->withToken($token)->getJson('/api/users')->assertOk();
    }

    public function test_logout_forgets_the_activity(): void
    {
        [$user, $token] = $this->signIn();
        $id = $user->tokens()->first()->id;
        $this->assertTrue(Cache::has("session_activity:{$id}"));

        $this->withToken($token)->postJson('/api/auth/logout')->assertOk();

        $this->assertFalse(Cache::has("session_activity:{$id}"));
    }
}
