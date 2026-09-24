@extends('layouts.vertical', ['title' => $receipt->receipt_number])

@section('content')
    @include('layouts.shared.page-title', ['page_title' => $receipt->receipt_number, 'sub_title' => 'Risiti'])

    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('receipts.pdf', $receipt) }}" target="_blank" class="btn btn-primary">
            <i class="ri-download-2-line me-1"></i> Pakua PDF
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <p class="mb-1"><strong>Mteja:</strong> {{ $receipt->customer->name }}</p>
            <p class="mb-1"><strong>Agizo:</strong> {{ $receipt->payment->order->order_number }}</p>
            <p class="mb-1"><strong>Kiasi:</strong> {{ money($receipt->amount) }}</p>
            <p class="mb-1"><strong>Njia ya Malipo:</strong> {{ $receipt->method }}</p>
            @if ($receipt->transaction_reference)
                <p class="mb-1"><strong>Kumbukumbu:</strong> {{ $receipt->transaction_reference }}</p>
            @endif
            <p class="mb-0"><strong>Tarehe:</strong> {{ $receipt->issued_at->format('d M Y H:i') }}</p>
        </div>
    </div>
@endsection
