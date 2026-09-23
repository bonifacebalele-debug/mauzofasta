<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessProfile extends Model
{
    protected $fillable = [
        'business_id',
        'description',
        'website',
        'social_links',
        'registration_number',
        'tax_enabled',
        'tax_number',
        'tax_rate',
    ];

    protected $casts = [
        'social_links' => 'array',
        'tax_enabled' => 'boolean',
        'tax_rate' => 'decimal:2',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
