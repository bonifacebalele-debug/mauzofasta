@csrf

<div class="row">
    <div class="col-md-8 mb-3">
        <label for="name" class="form-label">Jina la Bidhaa</label>
        <input class="form-control" type="text" name="name" id="name" value="{{ old('name', $product->name ?? '') }}" required autofocus>
    </div>
    <div class="col-md-4 mb-3">
        <label for="sku" class="form-label">SKU (si lazima)</label>
        <input class="form-control" type="text" name="sku" id="sku" value="{{ old('sku', $product->sku ?? '') }}">
    </div>

    <div class="col-md-4 mb-3">
        <label for="category_id" class="form-label">Kundi</label>
        <select class="form-select" name="category_id" id="category_id">
            <option value="">Hakuna</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id ?? null) == $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label for="selling_price" class="form-label">Bei ya Kuuza (Tsh)</label>
        <input class="form-control" type="number" min="0" name="selling_price" id="selling_price" value="{{ old('selling_price', $product->selling_price ?? '') }}" required>
    </div>
    <div class="col-md-4 mb-3">
        <label for="cost_price" class="form-label">Bei ya Gharama (Tsh, si lazima)</label>
        <input class="form-control" type="number" min="0" name="cost_price" id="cost_price" value="{{ old('cost_price', $product->cost_price ?? 0) }}">
    </div>

    @unless (isset($product))
        <div class="col-md-4 mb-3">
            <label for="stock_quantity" class="form-label">Hisa ya Awali</label>
            <input class="form-control" type="number" min="0" name="stock_quantity" id="stock_quantity" value="{{ old('stock_quantity', 0) }}">
        </div>
    @endunless
    <div class="col-md-4 mb-3">
        <label for="low_stock_threshold" class="form-label">Kiwango cha Onyo la Hisa Ndogo</label>
        <input class="form-control" type="number" min="0" name="low_stock_threshold" id="low_stock_threshold"
            value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? $defaultLowStockThreshold ?? 5) }}">
    </div>
    <div class="col-md-4 mb-3">
        <label for="image" class="form-label">Picha ya Bidhaa</label>
        <input class="form-control" type="file" name="image" id="image" accept="image/*">
    </div>

    <div class="col-md-8 mb-3">
        <label for="description" class="form-label">Maelezo (si lazima)</label>
        <textarea class="form-control" name="description" id="description" rows="3">{{ old('description', $product->description ?? '') }}</textarea>
    </div>

    <div class="col-md-4 mb-3">
        <label for="status" class="form-label">Hali</label>
        <select class="form-select" name="status" id="status">
            <option value="active" @selected(old('status', $product->status ?? 'active') === 'active')>Inatumika</option>
            <option value="archived" @selected(old('status', $product->status ?? 'active') === 'archived')>Imefichwa</option>
        </select>
    </div>

    <div class="col-12 mb-3">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" value="1"
                @checked(old('is_featured', $product->is_featured ?? false))>
            <label class="form-check-label" for="is_featured">Bidhaa Maalum (Featured)</label>
        </div>
    </div>
</div>

<button type="submit" class="btn btn-primary">{{ isset($product) ? 'Hifadhi Mabadiliko' : 'Ongeza Bidhaa' }}</button>
<a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Ghairi</a>
