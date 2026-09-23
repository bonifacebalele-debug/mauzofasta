<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessSettings extends Model
{
    protected $table = 'business_settings';

    protected $fillable = [
        'business_id',
        'currency',
        'timezone',
        'locale',
        'invoice_prefix',
        'receipt_prefix',
        'order_prefix',
        'next_invoice_number',
        'next_receipt_number',
        'next_order_number',
        'low_stock_default_threshold',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
