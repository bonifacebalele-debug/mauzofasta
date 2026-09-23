<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-sidenav-size="{{ $sidenav ?? 'default' }}" data-layout-mode="{{ $layoutMode ?? 'fluid' }}" data-layout-position="{{ $position ?? 'fixed' }}" data-menu-color="{{ $menuColor ?? 'dark' }}" data-topbar-color="{{ $topbarColor ?? 'light' }}">

<head>
    @include('layouts.shared.title-meta', ['title' => $title ?? 'Mwanzo'])
    @yield('css')
    @include('layouts.shared.head-css')
</head>

<body>
    <!-- Begin page -->
    <div class="wrapper">

        @include('layouts.shared.topbar')
        @include('layouts.shared.left-sidebar')

        <div class="content-page">
            <div class="content">

                <!-- Start Content-->
                <div class="container-fluid">
                    @yield('content')
                </div>
                <!-- container -->

            </div>
            <!-- content -->

            @include('layouts.shared.footer')
        </div>

    </div>
    <!-- END wrapper -->

    @yield('modal')

    @include('layouts.shared.footer-scripts')

    @vite(['resources/js/layout.js', 'resources/js/main.js'])

    @yield('script')
</body>

</html>
