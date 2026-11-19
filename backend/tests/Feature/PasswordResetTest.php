<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

class PasswordResetTest extends ApiTestCase
{
    public function test_forgot_password_emails_a_link_to_the_spa(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'reset@example.com']);

        $this->postJson('/api/auth/forgot-password', ['email' => 'reset@example.com'])->assertOk();

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $n) use ($user) {
            $url = $n->toMail($user)->actionUrl;

            return str_starts_with($url, config('app.frontend_url').'/reset-password?token=') && str_contains($url, 'email=reset%40example.com');
        });
    }

    public function test_unknown_email_gets_the_same_answer_and_no_mail(): void
    {
        Notification::fake();

        $this->postJson('/api/auth/forgot-password', ['email' => 'nobody@example.com'])
            ->assertOk()->assertJsonPath('message', 'If an account exists for that email, a reset link is on its way.');
        Notification::assertNothingSent();
    }

    public function test_reset_sets_the_password_and_signs_out_everywhere(): void
    {
        $user = User::factory()->create(['password' => 'old-password', 'must_change_password' => true]);
        $user->createToken('spa');
        $token = Password::createToken($user);

        $this->postJson('/api/auth/reset-password', [
            'token' => $token, 'email' => $user->email, 'password' => 'new-password-1', 'password_confirmation' => 'new-password-1',
        ])->assertOk();

        $this->assertSame(0, $user->tokens()->count());
        $this->assertFalse($user->fresh()->must_change_password);
        $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'new-password-1'])->assertOk();
    }

    public function test_bad_token_is_refused(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/auth/reset-password', [
            'token' => 'wrong', 'email' => $user->email, 'password' => 'new-password-1', 'password_confirmation' => 'new-password-1',
        ])->assertStatus(422)->assertJsonPath('message', 'This reset link is invalid or has expired. Ask for a new one.');
    }
}
