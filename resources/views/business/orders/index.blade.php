@extends('layouts.vertical', ['title' => 'Mauzo'])

@section('content')
    @include('layouts.shared.page-title', ['page_title' => 'Mauzo'])

    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <form method="GET" class="d-flex flex-wrap gap-2">
                    <input type="search" name="search" class="form-control" placeholder="Tafuta namba au mteja..."
                        value="{{ request('search') }}" style="max-width: 220px;">
                    <select name="status" class="form-select" style="max-width: 180px;" onchange="this.form.submit()">
                        <option value="">Hali Zote</option>
                        @foreach (\App\Models\Order::STATUSES as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-outline-secondary">Tafuta</button>
                </form>

                @can('create', \App\Models\Order::class)
                    <a href="{{ route('orders.quickSale') }}" class="btn btn-primary">
                        <i class="ri-shopping-cart-2-line me-1"></i> Uza
                    </a>
                @endcan
            </div>

            @if ($orders->isEmpty())
                <div class="text-center py-5">
                    <p class="text-muted">Bado hujafanya mauzo.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Namba</th>
                                <th>Mteja</th>
                                <th>Tarehe</th>
                                <th>Jumla</th>
                                <th>Deni</th>
                                <th>Hali</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $order)
                                <tr>
                                    <td><a href="{{ route('orders.show', $order) }}">{{ $order->order_number }}</a></td>
                                    <td>{{ $order->customer->name }}</td>
                                    <td>{{ $order->created_at->format('d M Y H:i') }}</td>
                                    <td>{{ money($order->total) }}</td>
                                    <td class="{{ $order->balance() > 0 ? 'text-danger' : '' }}">{{ money($order->balance()) }}</td>
                                    <td><span class="badge bg-secondary-subtle text-secondary">{{ $order->status }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">{{ $orders->links() }}</div>
            @endif
        </div>
    </div>
@endsection
