<?php

namespace App\Services\Inventory;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * The only place that changes a product/variant's cached stock_quantity.
 * Every change writes an immutable App\Models\StockMovement row first
 * (spec §24: "Never silently change stock") — the cached column exists
 * purely as a fast read, never as its own source of truth.
 */
class StockService
{
    public function recordMovement(
        Product $product,
        string $type,
        int $quantityDelta,
        ?ProductVariant $variant = null,
        ?Model $reference = null,
        ?string $note = null,
        ?int $userId = null,
    ): StockMovement {
        return DB::transaction(function () use ($product, $type, $quantityDelta, $variant, $reference, $note, $userId) {
            $movement = StockMovement::create([
                'business_id' => $product->business_id,
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'type' => $type,
                'quantity' => $quantityDelta,
                'reference_type' => $reference?->getMorphClass(),
                'reference_id' => $reference?->getKey(),
                'note' => $note,
                'created_by' => $userId,
            ]);

            if ($variant) {
                $variant->increment('stock_quantity', $quantityDelta);
            } else {
                $product->increment('stock_quantity', $quantityDelta);
            }

            return $movement;
        });
    }

    public function recordOpeningStock(Product $product, int $quantity, ?int $userId = null): ?StockMovement
    {
        if ($quantity === 0) {
            return null;
        }

        return $this->recordMovement($product, 'opening', $quantity, note: 'Hisa ya awali', userId: $userId);
    }

    public function adjustTo(
        Product $product,
        int $newQuantity,
        string $reason,
        ?ProductVariant $variant = null,
        ?int $userId = null,
    ): StockAdjustment {
        return DB::transaction(function () use ($product, $newQuantity, $reason, $variant, $userId) {
            $before = $variant ? $variant->stock_quantity : $product->stock_quantity;
            $delta = $newQuantity - $before;

            $adjustment = StockAdjustment::create([
                'business_id' => $product->business_id,
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'before_quantity' => $before,
                'after_quantity' => $newQuantity,
                'reason' => $reason,
                'created_by' => $userId,
            ]);

            if ($delta !== 0) {
                $this->recordMovement($product, 'adjustment', $delta, $variant, $adjustment, $reason, $userId);
            }

            return $adjustment;
        });
    }
}
