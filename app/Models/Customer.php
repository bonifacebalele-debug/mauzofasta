<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'business_id',
        'name',
        'phone',
        'email',
        'region',
        'district',
        'area',
        'status',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(CustomerNote::class)->latest();
    }

    public function totalSpent(): int
    {
        return (int) $this->orders()->whereNotIn('status', ['draft', 'cancelled'])->sum('amount_paid');
    }

    public function outstandingBalance(): int
    {
        return (int) $this->orders()
            ->whereNotIn('status', ['draft', 'cancelled', 'refunded'])
            ->get()
            ->sum(fn (Order $order) => max(0, $order->total - $order->amount_paid));
    }
}
