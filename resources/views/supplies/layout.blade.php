@extends('layout.master')

@section('css')
    <!-- select2 css -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/select/select2.min.css') }}">
    <style>
        .nav-link {
            text-decoration: none !important;
            color: inherit;
        }

        #supply-tabs {
            border: none !important;
        }
    </style>
    @stack('css')
@endsection

@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb -->
        <div class="row m-1">
            <div class="col-8">
                <h4 class="main-title">@yield('page-title')</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li><a href="{{ route('supplies.list') }}" class="f-s-14 f-w-500">
                            <i class="ph-duotone ph-stack f-s-16"></i> Approvisionnements</a></li>
                    <li class="active"><a href="#" class="f-s-14 f-w-500">@yield('breadcrumb-title')</a></li>
                </ul>
            </div>
            <div class="col-4 p-0">
                <div class="d-flex justify-content-end">
                    <a href="{{ route('supplies.list') }}" class="btn btn-primary">
                        <i class="ti ti-arrow-back"></i> Retourner à la liste
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <!-- Navigation "onglets" -->
                @include('supplies.partials.tabs-navigation')

                <!-- Contenu de la page -->
                <div class="mt-3">
                    @yield('tab-content')
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('assets/vendor/select/select2.min.js') }}"></script>
    @stack('scripts')
@endsection
