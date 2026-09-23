<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\StockAdjustmentRequest;
use App\Models\Product;
use App\Services\Inventory\StockService;

class StockController extends Controller
{
    public function __construct(protected StockService $stock) {}

    public function edit(Product $product)
    {
        $this->authorize('adjustStock', $product);

        return view('business.products.stock', [
            'product' => $product->load('variants'),
        ]);
    }

    public function update(StockAdjustmentRequest $request, Product $product)
    {
        $variant = $request->variant_id
            ? $product->variants()->findOrFail($request->variant_id)
            : null;

        $this->stock->adjustTo(
            $product,
            (int) $request->new_quantity,
            $request->reason,
            $variant,
            $request->user()->id,
        );

        return redirect()->route('products.stock.edit', $product)->with('status', 'Hisa imesasishwa.');
    }
}
