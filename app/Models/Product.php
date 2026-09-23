<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'business_id',
        'category_id',
        'name',
        'sku',
        'description',
        'selling_price',
        'cost_price',
        'stock_quantity',
        'low_stock_threshold',
        'image_path',
        'is_featured',
        'has_variants',
        'status',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'has_variants' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function isLowStock(): bool
    {
        return ! $this->has_variants && $this->stock_quantity <= $this->low_stock_threshold;
    }

    public function isOutOfStock(): bool
    {
        return ! $this->has_variants && $this->stock_quantity <= 0;
    }
}
