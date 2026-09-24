@extends('layouts.vertical', ['title' => $customer->name])

@section('content')
    @include('layouts.shared.page-title', ['page_title' => $customer->name, 'sub_title' => 'Wateja'])

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="row g-3 mb-1">
        <div class="col-6 col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Maagizo</p>
                    <h4 class="mb-0">{{ $customer->orders()->count() }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Jumla Aliyotumia</p>
                    <h4 class="mb-0">{{ money($customer->totalSpent()) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Agizo la Mwisho</p>
                    <h4 class="mb-0">{{ optional($customer->orders()->latest()->first())->created_at?->format('d M Y') ?? '—' }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Deni</p>
                    <h4 class="mb-0 {{ $customer->outstandingBalance() > 0 ? 'text-danger' : '' }}">{{ money($customer->outstandingBalance()) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">Historia ya Maagizo</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Namba</th>
                                    <th>Tarehe</th>
                                    <th>Jumla</th>
                                    <th>Hali</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($orders as $order)
                                    <tr>
                                        <td><a href="{{ route('orders.show', $order) }}">{{ $order->order_number }}</a></td>
                                        <td>{{ $order->created_at->format('d M Y') }}</td>
                                        <td>{{ money($order->total) }}</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">{{ $order->status }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">Bado hakuna maagizo.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="mt-3">{{ $orders->links() }}</div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">Maelezo Kuhusu Mteja</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('customers.notes.store', $customer) }}" class="mb-3">
                        @csrf
                        <textarea class="form-control mb-2" name="note" rows="2" placeholder="Ongeza maelezo..." required></textarea>
                        <button type="submit" class="btn btn-sm btn-primary">Hifadhi</button>
                    </form>

                    @forelse ($customer->notes as $note)
                        <div class="border-top pt-2 mt-2">
                            <p class="mb-1">{{ $note->note }}</p>
                            <small class="text-muted">{{ $note->created_at->format('d M Y H:i') }} — {{ $note->createdBy?->name }}</small>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Bado hakuna maelezo.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
