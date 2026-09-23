<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\RegisterBusinessRequest;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\BusinessProfile;
use App\Models\BusinessSettings;
use App\Models\BusinessUser;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class RegistrationController extends Controller
{
    public function create()
    {
        return view('business.register');
    }

    public function store(RegisterBusinessRequest $request)
    {
        $data = $request->validated();

        $business = DB::transaction(function () use ($data, $request) {
            $user = User::create([
                'name' => $data['owner_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make($data['password']),
            ]);

            $business = Business::create([
                'name' => $data['business_name'],
                'slug' => $this->uniqueSlug($data['business_name']),
                'owner_user_id' => $user->id,
                'phone' => $data['phone'],
                'whatsapp_number' => $data['phone'],
                'email' => $data['email'],
                'category' => $data['category'],
                'region' => $data['region'],
                'district' => $data['district'],
                'ward' => $data['ward'] ?? null,
                'area' => $data['area'] ?? null,
                'logo_path' => $request->file('logo')?->store('business-logos', 'public'),
                'status' => 'trial',
                'trial_ends_at' => now()->addDays((int) SystemSetting::get('trial_days', config('services.trial_days', 30))),
            ]);

            BusinessProfile::create([
                'business_id' => $business->id,
                'description' => $data['description'] ?? null,
            ]);

            BusinessSettings::create([
                'business_id' => $business->id,
            ]);

            $ownerRole = Role::findOrCreate('owner', 'web');

            BusinessUser::create([
                'business_id' => $business->id,
                'user_id' => $user->id,
                'role_id' => $ownerRole->id,
                'is_owner' => true,
                'status' => 'active',
            ]);

            event(new Registered($user));

            AuditLog::create([
                'business_id' => $business->id,
                'actor_id' => $user->id,
                'actor_type' => 'user',
                'action' => 'business.registered',
                'entity_type' => Business::class,
                'entity_id' => $business->id,
                'ip_address' => $request->ip(),
            ]);

            Auth::login($user);

            return $business;
        });

        $request->session()->put('current_business_id', $business->id);

        return redirect()->route('dashboard.index');
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'biashara';
        $slug = $base;
        $suffix = 1;

        while (Business::withoutTrashed()->where('slug', $slug)->exists()) {
            $suffix++;
            $slug = "{$base}-{$suffix}";
        }

        return $slug;
    }
}
