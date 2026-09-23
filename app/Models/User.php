<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_super_admin' => 'boolean',
    ];

    public function businessUsers(): HasMany
    {
        return $this->hasMany(BusinessUser::class);
    }

    public function businesses(): BelongsToMany
    {
        return $this->belongsToMany(Business::class, 'business_users')
            ->withPivot(['role_id', 'is_owner', 'status'])
            ->withTimestamps();
    }

    public function activeBusinesses(): BelongsToMany
    {
        return $this->businesses()->wherePivot('status', 'active');
    }

    /**
     * The staff-assignment row linking this user to the currently resolved
     * business (see App\Support\Tenancy\CurrentBusiness), or null if there
     * is no current business or the user has no active role in it.
     */
    public function currentBusinessUser(): ?BusinessUser
    {
        $business = current_business();

        if (! $business) {
            return null;
        }

        return $this->businessUsers()
            ->where('business_id', $business->id)
            ->where('status', 'active')
            ->first();
    }

    /**
     * Permission check scoped to the current business's role (spec §11).
     * Super admins hold no business role by design — they act through the
     * separate Super Admin console, never through tenant permissions.
     */
    public function hasBusinessPermission(string $permission): bool
    {
        return $this->currentBusinessUser()?->role?->hasPermissionTo($permission) ?? false;
    }

    public function isOwnerOfCurrentBusiness(): bool
    {
        return (bool) $this->currentBusinessUser()?->is_owner;
    }
}
