<?php

namespace Tests\Feature\Auth;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\CreatesBusiness;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use CreatesBusiness, RefreshDatabase;

    public function test_forgot_password_screen_can_be_rendered(): void
    {
        $this->get(route('password.request'))->assertOk();
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        [, $owner] = $this->createBusinessWithOwner();

        $this->post(route('password.email'), ['email' => $owner->email]);

        Notification::assertSentTo($owner, ResetPassword::class);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        [, $owner] = $this->createBusinessWithOwner();

        $this->post(route('password.email'), ['email' => $owner->email]);

        Notification::assertSentTo($owner, ResetPassword::class, function ($notification) {
            $this->get(route('password.reset', $notification->token))->assertOk();

            return true;
        });
    }

    public function test_password_can_be_reset_with_a_valid_token(): void
    {
        Notification::fake();

        [, $owner] = $this->createBusinessWithOwner();

        $this->post(route('password.email'), ['email' => $owner->email]);

        Notification::assertSentTo($owner, ResetPassword::class, function ($notification) use ($owner) {
            $response = $this->post(route('password.update'), [
                'token' => $notification->token,
                'email' => $owner->email,
                'password' => 'new-password123',
                'password_confirmation' => 'new-password123',
            ]);

            $response->assertSessionHasNoErrors();
            $response->assertRedirect(route('login'));

            return true;
        });
    }
}
