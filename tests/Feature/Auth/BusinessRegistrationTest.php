<?php

namespace Tests\Feature\Auth;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BusinessRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function validPayload(array $overrides = []): array
    {
        return array_merge([
            'business_name' => 'MO Essentials',
            'owner_name' => 'Asha Mohamed',
            'phone' => '0712345678',
            'email' => 'asha@example.com',
            'category' => 'Rejareja',
            'region' => 'Dar es Salaam',
            'district' => 'Kinondoni',
            'ward' => 'Mikocheni',
            'area' => 'Mikocheni B',
            'description' => 'Perfumes and accessories',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $overrides);
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $this->get(route('business.register'))->assertOk();
    }

    public function test_a_business_and_its_owner_are_created_together(): void
    {
        $response = $this->post(route('business.register'), $this->validPayload());

        $response->assertRedirect(route('dashboard.index'));
        $this->assertAuthenticated();

        $user = User::where('email', 'asha@example.com')->first();
        $this->assertNotNull($user);

        $business = Business::where('name', 'MO Essentials')->first();
        $this->assertNotNull($business);
        $this->assertSame($user->id, $business->owner_user_id);
        $this->assertSame('trial', $business->status);
        $this->assertNotNull($business->trial_ends_at);

        $membership = $business->businessUsers()->where('user_id', $user->id)->first();
        $this->assertTrue($membership->is_owner);
        $this->assertSame('owner', $membership->role->name);

        $this->assertNotNull($business->profile);
        $this->assertNotNull($business->settings);
    }

    public function test_duplicate_email_is_rejected(): void
    {
        User::factory()->create(['email' => 'asha@example.com']);

        $response = $this->post(route('business.register'), $this->validPayload());

        $response->assertSessionHasErrors('email');
    }

    public function test_password_confirmation_is_required(): void
    {
        $response = $this->post(route('business.register'), $this->validPayload([
            'password_confirmation' => 'different',
        ]));

        $response->assertSessionHasErrors('password');
    }

    public function test_logo_upload_is_stored(): void
    {
        Storage::fake('public');

        $response = $this->post(route('business.register'), array_merge($this->validPayload(), [
            'logo' => UploadedFile::fake()->image('logo.png'),
        ]));

        $response->assertRedirect(route('dashboard.index'));

        $business = Business::where('name', 'MO Essentials')->first();
        Storage::disk('public')->assertExists($business->logo_path);
    }
}
