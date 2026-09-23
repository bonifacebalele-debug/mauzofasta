<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesBusiness;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use CreatesBusiness, RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $this->get(route('login'))->assertOk();
    }

    public function test_users_can_authenticate_with_correct_credentials(): void
    {
        [, $owner] = $this->createBusinessWithOwner([], ['password' => bcrypt('password123')]);

        $response = $this->post(route('login'), [
            'email' => $owner->email,
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($owner);
        $response->assertRedirect(route('dashboard.index'));
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        [, $owner] = $this->createBusinessWithOwner([], ['password' => bcrypt('password123')]);

        $this->post(route('login'), [
            'email' => $owner->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        [, $owner] = $this->createBusinessWithOwner();

        $response = $this->actingAs($owner)->post(route('logout'));

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_owner_can_reach_dashboard(): void
    {
        [, $owner] = $this->createBusinessWithOwner();

        $this->actingAs($owner)
            ->get(route('dashboard.index'))
            ->assertOk();
    }

    public function test_guests_are_redirected_from_the_dashboard(): void
    {
        $this->get(route('dashboard.index'))->assertRedirect(route('login'));
    }
}
