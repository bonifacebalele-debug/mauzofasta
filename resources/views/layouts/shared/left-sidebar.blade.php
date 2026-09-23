@php
    // Route name, icon, and label for each module. A route that isn't
    // registered yet (later phases) renders as a disabled "coming soon"
    // item instead of a dead link — spec rule 29 (no fake functionality).
    $navItems = [
        ['route' => 'dashboard.index', 'icon' => 'ri-dashboard-3-line', 'label' => 'Mwanzo'],
        ['route' => 'orders.index', 'icon' => 'ri-shopping-cart-2-line', 'label' => 'Mauzo'],
        ['route' => 'products.index', 'icon' => 'ri-price-tag-3-line', 'label' => 'Bidhaa'],
        ['route' => 'customers.index', 'icon' => 'ri-group-line', 'label' => 'Wateja'],
        ['route' => 'payments.index', 'icon' => 'ri-bank-card-line', 'label' => 'Malipo'],
        ['route' => 'invoices.index', 'icon' => 'ri-file-list-3-line', 'label' => 'Ankara'],
        ['route' => 'expenses.index', 'icon' => 'ri-wallet-3-line', 'label' => 'Gharama'],
        ['route' => 'deliveries.index', 'icon' => 'ri-truck-line', 'label' => 'Uwasilishaji'],
        ['route' => 'store.settings', 'icon' => 'ri-store-2-line', 'label' => 'Duka la Mtandaoni'],
        ['route' => 'reports.index', 'icon' => 'ri-bar-chart-2-line', 'label' => 'Ripoti'],
        ['route' => 'subscription.index', 'icon' => 'ri-vip-crown-line', 'label' => 'Usajili'],
        ['route' => 'settings.business', 'icon' => 'ri-settings-4-line', 'label' => 'Mipangilio'],
    ];
@endphp
<!-- ========== Left Sidebar Start ========== -->
<div class="leftside-menu">

    <a href="{{ route('dashboard.index') }}" class="logo logo-light">
        <span class="logo-lg fw-bold fs-20 text-white">MAUZO FASTA</span>
        <span class="logo-sm fw-bold fs-16 text-white">MF</span>
    </a>

    <a href="{{ route('dashboard.index') }}" class="logo logo-dark">
        <span class="logo-lg fw-bold fs-20">MAUZO FASTA</span>
        <span class="logo-sm fw-bold fs-16">MF</span>
    </a>

    <div class="h-100" id="leftside-menu-container" data-simplebar>
        <ul class="side-nav">
            <li class="side-nav-title">Menyu</li>

            @foreach ($navItems as $item)
                <li class="side-nav-item">
                    @if (\Illuminate\Support\Facades\Route::has($item['route']))
                        <a href="{{ route($item['route']) }}"
                            class="side-nav-link {{ request()->routeIs($item['route']) ? 'active' : '' }}">
                            <i class="{{ $item['icon'] }}"></i>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @else
                        <a href="javascript:void(0);" class="side-nav-link disabled text-muted" aria-disabled="true">
                            <i class="{{ $item['icon'] }}"></i>
                            <span>{{ $item['label'] }}</span>
                            <span class="badge bg-light text-muted float-end">Hivi Karibuni</span>
                        </a>
                    @endif
                </li>
            @endforeach
        </ul>

        <div class="clearfix"></div>
    </div>
</div>
<!-- ========== Left Sidebar End ========== -->
