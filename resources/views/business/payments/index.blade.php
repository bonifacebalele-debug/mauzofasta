@extends('layouts.vertical', ['title' => 'Malipo'])

@section('content')
    @include('layouts.shared.page-title', ['page_title' => 'Malipo'])

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-end mb-3">
                @can('create', \App\Models\Payment::class)
                    <a href="{{ route('payments.create') }}" class="btn btn-primary">
                        <i class="ri-bank-card-line me-1"></i> Rekodi Malipo
                    </a>
                @endcan
            </div>

            @if ($payments->isEmpty())
                <div class="text-center py-5">
                    <p class="text-muted">Bado hakuna malipo yaliyorekodiwa.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Tarehe</th>
                                <th>Agizo</th>
                                <th>Mteja</th>
                                <th>Njia</th>
                                <th>Kiasi</th>
                                <th>Hali</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($payments as $payment)
                                <tr>
                                    <td>{{ $payment->paid_at->format('d M Y H:i') }}</td>
                                    <td><a href="{{ route('orders.show', $payment->order) }}">{{ $payment->order->order_number }}</a></td>
                                    <td>{{ $payment->order->customer->name }}</td>
                                    <td>{{ $payment->method }}</td>
                                    <td>{{ money($payment->amount) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $payment->status === 'confirmed' ? 'success' : 'secondary' }}-subtle text-{{ $payment->status === 'confirmed' ? 'success' : 'secondary' }}">
                                            {{ $payment->status }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        @can('refund', $payment)
                                            @if ($payment->status === 'confirmed')
                                                <form method="POST" action="{{ route('payments.refund', $payment) }}"
                                                    onsubmit="return confirm('Una hakika unataka kurejesha malipo haya?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Rejesha</button>
                                                </form>
                                            @endif
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">{{ $payments->links() }}</div>
            @endif
        </div>
    </div>
@endsection
