<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('layouts.shared.title-meta', ['title' => $title ?? 'Karibu'])
    @include('layouts.shared.head-css')
</head>

<body class="authentication-bg position-relative">
    <div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5 position-relative">
        <div class="container">
            <div class="row justify-content-center">
                <div class="{{ $wide ?? false ? 'col-xxl-9 col-lg-11' : 'col-xxl-6 col-lg-8' }}">
                    <div class="card overflow-hidden">
                        <div class="p-4">
                            <div class="text-center mb-4">
                                <a href="{{ url('/') }}" class="d-inline-block">
                                    <span class="fs-22 fw-bold text-dark">MAUZO FASTA</span>
                                </a>
                                <p class="text-muted mb-0">Biashara yako. Mauzo yako. Fasta.</p>
                            </div>

                            @yield('content')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer footer-alt fw-medium">
        <span class="text-muted fs-13">Powered by AIO Graphics &amp; Technology</span>
    </footer>

    @include('layouts.shared.footer-scripts')
    @yield('script')
</body>

</html>
