@extends('layouts.vertical', ['title' => 'Hariri Bidhaa'])

@section('content')
    @include('layouts.shared.page-title', ['page_title' => 'Hariri Bidhaa', 'sub_title' => 'Bidhaa'])

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">
                @method('PUT')
                @include('business.products._form')
            </form>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-1">Hisa ya Sasa: {{ $product->stock_quantity }}</h6>
                <p class="text-muted mb-0 fs-13">Stock hubadilishwa kupitia "Rekebisha Stock", si hapa (spec §24).</p>
            </div>
            <a href="{{ route('products.stock.edit', $product) }}" class="btn btn-outline-primary">Rekebisha Stock</a>
        </div>
    </div>
@endsection
