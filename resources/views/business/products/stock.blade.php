@extends('layouts.vertical', ['title' => 'Rekebisha Stock'])

@section('content')
    @include('layouts.shared.page-title', ['page_title' => 'Rekebisha Stock: '.$product->name, 'sub_title' => 'Bidhaa'])

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('products.stock.update', $product) }}">
                @csrf
                @method('PUT')

                @if ($product->variants->isNotEmpty())
                    <div class="mb-3">
                        <label for="variant_id" class="form-label">Aina (si lazima)</label>
                        <select class="form-select" name="variant_id" id="variant_id">
                            <option value="">Bidhaa kuu (Hisa: {{ $product->stock_quantity }})</option>
                            @foreach ($product->variants as $variant)
                                <option value="{{ $variant->id }}">{{ $variant->label() }} (Hisa: {{ $variant->stock_quantity }})</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <p class="text-muted">Hisa ya sasa: <strong>{{ $product->stock_quantity }}</strong></p>
                @endif

                <div class="mb-3">
                    <label for="new_quantity" class="form-label">Hisa Mpya</label>
                    <input class="form-control" type="number" min="0" name="new_quantity" id="new_quantity" required>
                </div>

                <div class="mb-3">
                    <label for="reason" class="form-label">Sababu</label>
                    <input class="form-control" type="text" name="reason" id="reason" placeholder="mfano: Ununuzi mpya, Uharibifu, Hesabu ya kila mwezi" required>
                </div>

                <button type="submit" class="btn btn-primary">Hifadhi</button>
                <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-secondary">Rudi</a>
            </form>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">Historia ya Hisa</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Tarehe</th>
                            <th>Aina</th>
                            <th>Kiasi</th>
                            <th>Maelezo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($product->stockMovements()->latest()->limit(20)->get() as $movement)
                            <tr>
                                <td>{{ $movement->created_at->format('d M Y H:i') }}</td>
                                <td>{{ $movement->type }}</td>
                                <td class="{{ $movement->quantity >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $movement->quantity >= 0 ? '+' : '' }}{{ $movement->quantity }}
                                </td>
                                <td>{{ $movement->note }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">Hakuna historia bado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
