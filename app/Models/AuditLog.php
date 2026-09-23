<?php

namespace App\Models;

use App\Support\Tenancy\CurrentBusiness;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'business_id',
        'actor_id',
        'actor_type',
        'action',
        'entity_type',
        'entity_id',
        'ip_address',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public static function record(string $action, array $attributes = []): self
    {
        $user = auth()->user();

        return static::create(array_merge([
            'business_id' => app(CurrentBusiness::class)->id(),
            'actor_id' => $user?->id,
            'actor_type' => 'user',
            'action' => $action,
            'ip_address' => request()?->ip(),
        ], $attributes));
    }
}
