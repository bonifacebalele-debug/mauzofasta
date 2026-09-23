<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'sku',
        'attributes',
        'price',
        'stock_quantity',
        'low_stock_threshold',
    ];

    protected $casts = [
        'attributes' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function sellingPrice(): int
    {
        return $this->price ?? $this->product->selling_price;
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->low_stock_threshold;
    }

    /**
     * Human-readable label built from the attributes bag, e.g. "Size: M, Color: Black".
     */
    public function label(): string
    {
        return collect($this->attributes)
            ->map(fn ($value, $key) => "{$key}: {$value}")
            ->implode(', ');
    }
}
