@extends('layouts.vertical', ['title' => 'Ankara'])

@section('content')
    @include('layouts.shared.page-title', ['page_title' => 'Ankara'])

    <div class="card">
        <div class="card-body">
            @if ($invoices->isEmpty())
                <div class="text-center py-5">
                    <p class="text-muted">Bado hakuna ankara.</p>
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
                                <th>Hali</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoices as $invoice)
                                <tr>
                                    <td><a href="{{ route('invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a></td>
                                    <td>{{ $invoice->customer->name }}</td>
                                    <td>{{ $invoice->issue_date->format('d M Y') }}</td>
                                    <td>{{ money($invoice->total) }}</td>
                                    <td><span class="badge bg-secondary-subtle text-secondary">{{ $invoice->status }}</span></td>
                                    <td class="text-end">
                                        <a href="{{ route('invoices.pdf', $invoice) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                            <i class="ri-download-2-line"></i> PDF
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">{{ $invoices->links() }}</div>
            @endif
        </div>
    </div>
@endsection
