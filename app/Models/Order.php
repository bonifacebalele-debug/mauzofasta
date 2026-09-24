<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    /** Spec §26 — the full order status machine. */
    public const STATUSES = [
        'draft', 'pending_payment', 'paid', 'processing', 'ready',
        'assigned', 'out_for_delivery', 'delivered', 'completed',
        'cancelled', 'refunded',
    ];

    /** Statuses a cancellation restocks items from (nothing shipped yet). */
    public const CANCELLABLE_STATUSES = ['pending_payment', 'paid', 'processing', 'ready'];

    protected $fillable = [
        'business_id',
        'customer_id',
        'order_number',
        'source',
        'subtotal',
        'discount_amount',
        'discount_type',
        'tax_amount',
        'delivery_fee',
        'total',
        'amount_paid',
        'status',
        'notes',
        'created_by',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->latest();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function balance(): int
    {
        return max(0, $this->total - $this->amount_paid);
    }

    public function isFullyPaid(): bool
    {
        return $this->amount_paid >= $this->total;
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, self::CANCELLABLE_STATUSES, true);
    }

    /**
     * Writes the transition to order_status_history — every status change
     * is logged (spec §26), never a silent column update.
     */
    public function transitionTo(string $status, ?int $userId = null, ?string $note = null): void
    {
        $from = $this->status;

        $this->update(['status' => $status]);

        $this->statusHistory()->create([
            'from_status' => $from,
            'to_status' => $status,
            'changed_by' => $userId,
            'note' => $note,
        ]);
    }
}
