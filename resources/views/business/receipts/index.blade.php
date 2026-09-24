@extends('layouts.vertical', ['title' => 'Risiti'])

@section('content')
    @include('layouts.shared.page-title', ['page_title' => 'Risiti'])

    <div class="card">
        <div class="card-body">
            @if ($receipts->isEmpty())
                <div class="text-center py-5">
                    <p class="text-muted">Bado hakuna risiti.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Namba</th>
                                <th>Mteja</th>
                                <th>Tarehe</th>
                                <th>Kiasi</th>
                                <th>Njia</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($receipts as $receipt)
                                <tr>
                                    <td><a href="{{ route('receipts.show', $receipt) }}">{{ $receipt->receipt_number }}</a></td>
                                    <td>{{ $receipt->customer->name }}</td>
                                    <td>{{ $receipt->issued_at->format('d M Y H:i') }}</td>
                                    <td>{{ money($receipt->amount) }}</td>
                                    <td>{{ $receipt->method }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('receipts.pdf', $receipt) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                            <i class="ri-download-2-line"></i> PDF
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">{{ $receipts->links() }}</div>
            @endif
        </div>
    </div>
@endsection
