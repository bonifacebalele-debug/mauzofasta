@php
    $business = current_business();
    $alerts = $business?->activeAlerts() ?? [];
@endphp
<!-- ========== Topbar Start ========== -->
<div class="navbar-custom">
    <div class="topbar container-fluid">
        <div class="d-flex align-items-center gap-1">

            <div class="logo-topbar">
                <a href="{{ route('dashboard.index') }}" class="logo-light">
                    <span class="logo-lg fw-bold fs-18">MAUZO FASTA</span>
                    <span class="logo-sm fw-bold fs-16">MF</span>
                </a>
                <a href="{{ route('dashboard.index') }}" class="logo-dark">
                    <span class="logo-lg fw-bold fs-18">MAUZO FASTA</span>
                    <span class="logo-sm fw-bold fs-16">MF</span>
                </a>
            </div>

            <button class="button-toggle-menu">
                <i class="ri-menu-line"></i>
            </button>

            @if ($business)
                <span class="text-muted ms-2 d-none d-md-inline">{{ $business->name }}</span>
            @endif
        </div>

        <ul class="topbar-menu d-flex align-items-center gap-3">
            <li class="dropdown notification-list">
                <a class="nav-link dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button"
                    aria-haspopup="false" aria-expanded="false">
                    <i class="ri-notification-3-line fs-22"></i>
                    @if (count($alerts))
                        <span class="noti-icon-badge badge text-bg-pink">{{ count($alerts) }}</span>
                    @endif
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated dropdown-lg py-0">
                    <div class="p-2 border-top-0 border-start-0 border-end-0 border-dashed border">
                        <h6 class="m-0 fs-16 fw-semibold">Arifa</h6>
                    </div>

                    <div style="max-height: 300px;" data-simplebar>
                        @forelse ($alerts as $alert)
                            <div class="dropdown-item notify-item">
                                <div class="notify-icon bg-{{ $alert['level'] }}-subtle">
                                    <i class="ri-alert-line text-{{ $alert['level'] }}"></i>
                                </div>
                                <p class="notify-details mb-0">{{ $alert['text'] }}</p>
                            </div>
                        @empty
                            <p class="text-muted text-center p-3 mb-0">Hakuna arifa kwa sasa.</p>
                        @endforelse
                    </div>
                </div>
            </li>

            <li class="d-none d-sm-inline-block">
                <div class="nav-link" id="light-dark-mode">
                    <i class="ri-moon-line fs-22"></i>
                </div>
            </li>

            <li class="dropdown">
                <a class="nav-link dropdown-toggle arrow-none nav-user" data-bs-toggle="dropdown" href="#" role="button"
                    aria-haspopup="false" aria-expanded="false">
                    <span class="account-user-avatar">
                        <span class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle" style="width:32px;height:32px;">
                            {{ mb_substr(auth()->user()->name ?? '?', 0, 1) }}
                        </span>
                    </span>
                    <span class="d-lg-block d-none">
                        <h5 class="my-0 fw-normal">{{ auth()->user()->name }}
                            <i class="ri-arrow-down-s-line d-none d-sm-inline-block align-middle"></i>
                        </h5>
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated profile-dropdown">
                    <div class="dropdown-header noti-title">
                        <h6 class="text-overflow m-0">Karibu, {{ auth()->user()->name }}</h6>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="ri-logout-box-line fs-18 align-middle me-1"></i>
                            <span>Toka</span>
                        </button>
                    </form>
                </div>
            </li>
        </ul>
    </div>
</div>
<!-- ========== Topbar End ========== -->
