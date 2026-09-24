<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use BelongsToBusiness, HasUuid;

    protected $fillable = [
        'business_id',
        'order_id',
        'customer_id',
        'invoice_number',
        'issue_date',
        'due_date',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'delivery_fee',
        'total',
        'amount_paid',
        'status',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class);
    }

    public function balance(): int
    {
        return max(0, $this->total - $this->amount_paid);
    }

    public function syncFromOrder(Order $order): void
    {
        $status = match (true) {
            $order->amount_paid <= 0 => 'unpaid',
            $order->amount_paid < $order->total => 'partially_paid',
            default => 'paid',
        };

        $this->update([
            'amount_paid' => $order->amount_paid,
            'status' => $status,
        ]);
    }
}
