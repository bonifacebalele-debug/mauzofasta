@extends('layouts.vertical', ['title' => $order->order_number])

@section('content')
    @include('layouts.shared.page-title', ['page_title' => $order->order_number, 'sub_title' => 'Mauzo'])

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Bidhaa</span>
                    <span class="badge bg-secondary-subtle text-secondary">{{ $order->status }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Bidhaa</th>
                                    <th>Bei</th>
                                    <th>Idadi</th>
                                    <th>Jumla</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->items as $item)
                                    <tr>
                                        <td>{{ $item->product_name_snapshot }}</td>
                                        <td>{{ money($item->unit_price) }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>{{ money($item->line_total) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between"><span>Jumla Ndogo</span><span>{{ money($order->subtotal) }}</span></div>
                    @if ($order->discount_amount > 0)
                        <div class="d-flex justify-content-between text-danger"><span>Punguzo</span><span>-{{ money($order->discount_amount) }}</span></div>
                    @endif
                    @if ($order->tax_amount > 0)
                        <div class="d-flex justify-content-between"><span>Kodi</span><span>{{ money($order->tax_amount) }}</span></div>
                    @endif
                    @if ($order->delivery_fee > 0)
                        <div class="d-flex justify-content-between"><span>Uwasilishaji</span><span>{{ money($order->delivery_fee) }}</span></div>
                    @endif
                    <div class="d-flex justify-content-between fw-bold fs-16 mt-2"><span>Jumla</span><span>{{ money($order->total) }}</span></div>
                    <div class="d-flex justify-content-between"><span>Alicholipa</span><span>{{ money($order->amount_paid) }}</span></div>
                    <div class="d-flex justify-content-between {{ $order->balance() > 0 ? 'text-danger' : 'text-success' }}"><span>Deni</span><span>{{ money($order->balance()) }}</span></div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">Historia ya Hali</div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach ($order->statusHistory as $history)
                            <li class="list-group-item d-flex justify-content-between">
                                <span>{{ $history->from_status ?? 'Mpya' }} → {{ $history->to_status }}
                                    @if ($history->note) <small class="text-muted">({{ $history->note }})</small> @endif
                                </span>
                                <small class="text-muted">{{ $history->created_at->format('d M Y H:i') }}</small>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">Mteja</div>
                <div class="card-body">
                    <p class="mb-1"><a href="{{ route('customers.show', $order->customer) }}">{{ $order->customer->name }}</a></p>
                    <p class="text-muted mb-0">{{ $order->customer->phone }}</p>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">Malipo</div>
                <div class="card-body">
                    @forelse ($order->payments as $payment)
                        <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                            <span>{{ $payment->method }}</span>
                            <span>{{ money($payment->amount) }}</span>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Hakuna malipo bado.</p>
                    @endforelse
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body d-grid gap-2">
                    @can('update', $order)
                        @if ($order->status === 'paid')
                            <form method="POST" action="{{ route('orders.complete', $order) }}">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">Kamilisha Agizo</button>
                            </form>
                        @endif
                    @endcan

                    @can('cancel', $order)
                        @if ($order->isCancellable())
                            <form method="POST" action="{{ route('orders.cancel', $order) }}"
                                onsubmit="return confirm('Una hakika unataka kughairi agizo hili?')">
                                @csrf
                                <input type="hidden" name="reason" value="Imeghairiwa na mfanyakazi">
                                <button type="submit" class="btn btn-outline-danger w-100">Ghairi Agizo</button>
                            </form>
                        @endif
                    @endcan
                </div>
            </div>
        </div>
    </div>
@endsection
