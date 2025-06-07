<!DOCTYPE html>
<html lang="fr">

<head>
    <!-- All meta and title start-->
    @include('layout.head')
    <!-- meta and title end-->

    <!-- css start-->
    @include('layout.css')
    @yield('css')
    @stack('styles')
    @livewireStyles
    <!-- css end-->
</head>

<body>
    <!-- Loader start-->
    <div class="app-wrapper">
        <div class="loader-wrapper">
            <div class="loader_16"></div>
        </div>
        <!-- Loader end-->

        <!-- Menu Navigation start -->
        @include('layout.sidebar')
        <!-- Menu Navigation end -->


        <div class="app-content">
            <!-- Header Section start -->
            @include('layout.header')
            <!-- Header Section end -->

            <!-- Main Section start -->
            <main>
                {{-- main body content --}}
                @yield('main-content')
            </main>
            <!-- Main Section end -->
        </div>

        <!-- tap on top -->
        <div class="go-top">
            <span class="progress-value">
                <i class="ti ti-chevron-up"></i>
            </span>
        </div>

        <!-- Footer Section start -->
        @include('layout.footer')
        <!-- Footer Section end -->
    </div>
</body>

<x-sweet-alert-notification />

<!-- scripts start-->
@include('layout.script')
@yield('script')

@stack('scripts')
<!-- scripts end-->

</html>
