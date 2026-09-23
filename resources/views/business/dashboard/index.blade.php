@extends('layouts.vertical', ['title' => 'Mwanzo'])

@section('content')
    @include('layouts.shared.page-title', ['page_title' => 'Mwanzo'])

    @foreach ($alerts as $alert)
        <div class="alert alert-{{ $alert['level'] }} d-flex align-items-center" role="alert">
            <i class="ri-alert-line me-2 fs-18"></i>
            <div>{{ $alert['text'] }}</div>
        </div>
    @endforeach

    <div class="row g-3 mb-1">
        @foreach ($quickActions as $action)
            <div class="col-6 col-md-3">
                @if (\Illuminate\Support\Facades\Route::has($action['route']))
                    <a href="{{ route($action['route']) }}" class="btn btn-primary w-100 py-2">
                        <i class="{{ $action['icon'] }} me-1"></i> {{ $action['label'] }}
                    </a>
                @else
                    <button type="button" class="btn btn-outline-secondary w-100 py-2" disabled
                        title="Inakuja hivi karibuni">
                        <i class="{{ $action['icon'] }} me-1"></i> {{ $action['label'] }}
                    </button>
                @endif
            </div>
        @endforeach
    </div>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3 mt-3">
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Mauzo Leo</p>
                    <h3 class="mb-0">{{ money($metrics['sales_today']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Maagizo</p>
                    <h3 class="mb-0">{{ $metrics['orders_today'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Zilizolipwa</p>
                    <h3 class="mb-0">{{ $metrics['paid_orders_today'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Zisizolipwa</p>
                    <h3 class="mb-0">{{ $metrics['unpaid_orders_today'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Gharama</p>
                    <h3 class="mb-0">{{ money($metrics['expenses_today']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Makadirio ya Faida</p>
                    <h3 class="mb-0">{{ money($metrics['estimated_profit_today']) }}</h3>
                </div>
            </div>
        </div>
    </div>

    @if ($business && $metrics['orders_today'] === 0)
        <div class="card mt-3">
            <div class="card-body">
                <h5 class="mb-1">Karibu MAUZO FASTA 👋</h5>
                <p class="text-muted mb-0">
                    Bado hujafanya mauzo. Ongeza bidhaa yako ya kwanza, mteja wako wa kwanza, kisha bofya
                    <strong>Uza</strong> hapo juu kuanza. Vipengele hivi vinakuja katika Awamu ya 3 na ya 4.
                </p>
            </div>
        </div>
    @endif
@endsection
