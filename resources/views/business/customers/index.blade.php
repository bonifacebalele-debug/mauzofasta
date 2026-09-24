@extends('layouts.vertical', ['title' => 'Wateja'])

@section('content')
    @include('layouts.shared.page-title', ['page_title' => 'Wateja'])

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <form method="GET" class="d-flex gap-2">
                    <input type="search" name="search" class="form-control" placeholder="Tafuta jina au namba ya simu..."
                        value="{{ request('search') }}" style="max-width: 260px;">
                    <button type="submit" class="btn btn-outline-secondary">Tafuta</button>
                </form>

                @can('create', \App\Models\Customer::class)
                    <a href="{{ route('customers.create') }}" class="btn btn-primary">
                        <i class="ri-user-add-line me-1"></i> Ongeza Mteja
                    </a>
                @endcan
            </div>

            @if ($customers->isEmpty())
                <div class="text-center py-5">
                    <p class="text-muted">Bado huna wateja.</p>
                    @can('create', \App\Models\Customer::class)
                        <a href="{{ route('customers.create') }}" class="btn btn-primary">+ Ongeza Mteja wa Kwanza</a>
                    @endcan
                </div>
            @else
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Jina</th>
                                <th>Simu</th>
                                <th>Eneo</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($customers as $customer)
                                <tr>
                                    <td><a href="{{ route('customers.show', $customer) }}">{{ $customer->name }}</a></td>
                                    <td>{{ $customer->phone }}</td>
                                    <td>{{ trim(($customer->district ?? '').' '.($customer->region ?? ''), ' ') ?: '—' }}</td>
                                    <td class="text-end">
                                        @can('update', $customer)
                                            <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm btn-outline-secondary">Hariri</a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">{{ $customers->links() }}</div>
            @endif
        </div>
    </div>
@endsection
