<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Business extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'owner_user_id',
        'phone',
        'whatsapp_number',
        'email',
        'category',
        'region',
        'district',
        'ward',
        'area',
        'logo_path',
        'status',
        'trial_ends_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function profile(): HasOne
    {
        return $this->hasOne(BusinessProfile::class);
    }

    public function settings(): HasOne
    {
        return $this->hasOne(BusinessSettings::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function businessHours(): HasMany
    {
        return $this->hasMany(BusinessHour::class);
    }

    public function businessUsers(): HasMany
    {
        return $this->hasMany(BusinessUser::class);
    }

    public function isOnTrial(): bool
    {
        return $this->status === 'trial' && $this->trial_ends_at?->isFuture();
    }

    public function trialHasExpired(): bool
    {
        return $this->status === 'trial' && $this->trial_ends_at?->isPast();
    }

    /**
     * Dashboard/topbar alert feed (spec §21). Pending payments, overdue
     * invoices, and pending deliveries are added here once
     * Payments/Invoices/Delivery exist (Phases 5, 7).
     *
     * @return list<array{level: string, text: string}>
     */
    public function activeAlerts(): array
    {
        $alerts = [];

        if ($this->trialHasExpired()) {
            $alerts[] = [
                'level' => 'danger',
                'text' => 'Kipindi chako cha majaribio kimeisha. Chagua mpango wa malipo kuendelea.',
            ];
        } elseif ($this->isOnTrial() && now()->diffInDays($this->trial_ends_at, false) <= 5) {
            $alerts[] = [
                'level' => 'warning',
                'text' => 'Kipindi chako cha majaribio kinaisha '.$this->trial_ends_at->translatedFormat('d M Y').'.',
            ];
        }

        foreach ($this->lowStockProducts() as $product) {
            $alerts[] = [
                'level' => 'warning',
                'text' => "⚠️ {$product->name} imebaki {$product->stock_quantity} tu.",
            ];
        }

        return $alerts;
    }

    /**
     * @return Collection<int, Product>
     */
    protected function lowStockProducts()
    {
        return Product::query()
            ->where('status', 'active')
            ->where('has_variants', false)
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->orderBy('stock_quantity')
            ->limit(3)
            ->get();
    }
}
