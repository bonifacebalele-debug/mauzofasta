@extends('layouts.vertical', ['title' => $invoice->invoice_number])

@section('content')
    @include('layouts.shared.page-title', ['page_title' => $invoice->invoice_number, 'sub_title' => 'Ankara'])

    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('invoices.pdf', $invoice) }}" target="_blank" class="btn btn-primary">
            <i class="ri-download-2-line me-1"></i> Pakua PDF
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <p class="mb-1 fw-bold">{{ $invoice->customer->name }}</p>
                    <p class="text-muted mb-0">{{ $invoice->customer->phone }}</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-1">Tarehe: {{ $invoice->issue_date->format('d M Y') }}</p>
                    <span class="badge bg-secondary-subtle text-secondary">{{ $invoice->status }}</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Bidhaa</th>
                            <th>Bei</th>
                            <th>Idadi</th>
                            <th>Jumla</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoice->items as $item)
                            <tr>
                                <td>{{ $item->description }}</td>
                                <td>{{ money($item->unit_price) }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ money($item->line_total) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                <div style="min-width: 260px;">
                    <div class="d-flex justify-content-between"><span>Jumla Ndogo</span><span>{{ money($invoice->subtotal) }}</span></div>
                    @if ($invoice->discount_amount > 0)
                        <div class="d-flex justify-content-between text-danger"><span>Punguzo</span><span>-{{ money($invoice->discount_amount) }}</span></div>
                    @endif
                    @if ($invoice->tax_amount > 0)
                        <div class="d-flex justify-content-between"><span>Kodi</span><span>{{ money($invoice->tax_amount) }}</span></div>
                    @endif
                    <div class="d-flex justify-content-between fw-bold fs-16 mt-2"><span>Jumla</span><span>{{ money($invoice->total) }}</span></div>
                    <div class="d-flex justify-content-between"><span>Alicholipa</span><span>{{ money($invoice->amount_paid) }}</span></div>
                    <div class="d-flex justify-content-between {{ $invoice->balance() > 0 ? 'text-danger' : 'text-success' }}"><span>Deni</span><span>{{ money($invoice->balance()) }}</span></div>
                </div>
            </div>
        </div>
    </div>
@endsection
