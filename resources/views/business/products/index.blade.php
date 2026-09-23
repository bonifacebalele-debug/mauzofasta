@extends('layouts.vertical', ['title' => 'Bidhaa'])

@section('content')
    @include('layouts.shared.page-title', ['page_title' => 'Bidhaa'])

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <form method="GET" class="d-flex flex-wrap gap-2">
                    <input type="search" name="search" class="form-control" placeholder="Tafuta bidhaa au SKU..."
                        value="{{ request('search') }}" style="max-width: 220px;">
                    <select name="category_id" class="form-select" style="max-width: 180px;" onchange="this.form.submit()">
                        <option value="">Makundi Yote</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-outline-secondary">Tafuta</button>
                </form>

                @can('create', \App\Models\Product::class)
                    <a href="{{ route('products.create') }}" class="btn btn-primary">
                        <i class="ri-add-line me-1"></i> Ongeza Bidhaa
                    </a>
                @endcan
            </div>

            @if ($products->isEmpty())
                <div class="text-center py-5">
                    <p class="text-muted">Bado huna bidhaa.</p>
                    @can('create', \App\Models\Product::class)
                        <a href="{{ route('products.create') }}" class="btn btn-primary">+ Ongeza Bidhaa ya Kwanza</a>
                    @endcan
                </div>
            @else
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
                    @foreach ($products as $product)
                        <div class="col">
                            <div class="card h-100">
                                @if ($product->image_path)
                                    <img src="{{ asset('storage/'.$product->image_path) }}" class="card-img-top" alt="{{ $product->name }}" style="height:160px;object-fit:cover;">
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center" style="height:160px;">
                                        <i class="ri-image-line fs-24 text-muted"></i>
                                    </div>
                                @endif
                                <div class="card-body">
                                    <h6 class="mb-1">{{ $product->name }}</h6>
                                    <p class="text-muted mb-1 fs-13">{{ $product->category?->name ?? '—' }}</p>
                                    <p class="fw-bold mb-2">{{ money($product->selling_price) }}</p>

                                    @if ($product->isOutOfStock())
                                        <span class="badge bg-danger-subtle text-danger">Hisa Imeisha</span>
                                    @elseif ($product->isLowStock())
                                        <span class="badge bg-warning-subtle text-warning">Hisa Ndogo: {{ $product->stock_quantity }}</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success">Hisa: {{ $product->stock_quantity }}</span>
                                    @endif

                                    <div class="d-flex gap-2 mt-3">
                                        @can('update', $product)
                                            <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-secondary flex-fill">Hariri</a>
                                        @endcan
                                        @can('delete', $product)
                                            <form method="POST" action="{{ route('products.destroy', $product) }}"
                                                onsubmit="return confirm('Una hakika unataka kufuta {{ $product->name }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-3">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
