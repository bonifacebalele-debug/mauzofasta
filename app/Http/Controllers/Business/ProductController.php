<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\StoreProductRequest;
use App\Http\Requests\Business\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\Inventory\StockService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(protected StockService $stock) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Product::class);

        $products = Product::query()
            ->with('category')
            ->when($request->search, fn ($q, $search) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%"))
            ->when($request->category_id, fn ($q, $categoryId) => $q->where('category_id', $categoryId))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status), fn ($q) => $q->where('status', 'active'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('business.products.index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        $this->authorize('create', Product::class);

        return view('business.products.create', [
            'categories' => Category::orderBy('name')->get(),
            'defaultLowStockThreshold' => current_business()->settings->low_stock_default_threshold ?? 5,
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();
        $stockQuantity = (int) ($data['stock_quantity'] ?? 0);
        unset($data['stock_quantity']);

        $data['image_path'] = $request->file('image')?->store('products', 'public');
        $data['low_stock_threshold'] ??= current_business()->settings->low_stock_default_threshold ?? 5;

        $product = Product::create($data);

        $this->stock->recordOpeningStock($product, $stockQuantity, $request->user()->id);

        return redirect()->route('products.index')->with('status', 'Bidhaa imeongezwa.');
    }

    public function edit(Product $product)
    {
        $this->authorize('update', $product);

        return view('business.products.edit', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return redirect()->route('products.index')->with('status', 'Bidhaa imesasishwa.');
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        $product->update(['status' => 'archived']);
        $product->delete();

        return redirect()->route('products.index')->with('status', 'Bidhaa imefutwa.');
    }
}
