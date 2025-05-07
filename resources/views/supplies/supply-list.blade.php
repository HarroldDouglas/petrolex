@extends('layout.master')
@section('title', 'Liste des approvisionnements')
@section('css')
    <!-- Data Table css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/datatable/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/datatable/datatable2/buttons.dataTables.min.css') }}">

    <!-- Daterangepicker CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-daterangepicker@3.1.0/daterangepicker.css" rel="stylesheet" />
    <!-- Bootstrap Datepicker CSS -->
    <link href="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/css/bootstrap-datepicker.min.css" rel="stylesheet" />

@endsection
@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb start -->
        <div class="row m-1">
            <div class="col-8 p-O ">
                <h4 class="main-title"> liste des approvisionnements</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="#" class="f-s-14 f-w-500">
                      <span>
                        <i class="ph-duotone  ph-stack f-s-16"></i> Apps
                      </span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="f-s-14 f-w-500">Approvisionnements</a>
                    </li>
                    <li class="active">
                        <a href="#" class="f-s-14 f-w-500">Liste des approvisionnements</a>
                    </li>
                </ul>
            </div>
            <!-- Filter -->
            <div class="col-4 p-0">
                <div class="d-flex justify-content-end text-primary-dark">
                    <a type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilter" role="button"
                        aria-expanded="false" aria-controls="collapseFilter"
                        class="waves-effect btn text-primary-dark bg-white btn-md mb-2">
                        <i class="ti ti-chevron-down"></i> Filtrer
                    </a>
                </div>
            </div>
        </div>
        <!-- Breadcrumb end -->

        <!-- Filter Options-->
        <div class="row m-1">
            <div class="col-12 collapse mb-4 p-0" id="collapseFilter">
                @livewire('dashboard.filter-component')
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card card-border">
                    <div class="card-body p-0">
                        <!-- table -->
                        <div class="table-responsive app-scroll app-datatable-default">
                            <table class="w-100 display ticket-app-table" id="ticketdatatable">
                                <thead>
                                <tr>
                                    <th class="w-20">Titre</th>
                                    <th class="w-30">Description</th>
                                    <th class="w-10">Date</th>
                                    <th class="w-30">Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Approvisionnement def</td>
                                        <td>Approvisionnement def détaillée</td>
                                        <td>11 Avr,2025 10:15</td>
                                        <td>
                                            <a href="{{ route('supplies.details', 4) }}" class="btn btn-light-primary icon-btn w-30 h-30 b-r-22 me-0"><i class="ti ti-eye"></i></a>
                                            <a href="{{ route('supplies.edit', 4) }}" class="btn btn-light-success icon-btn w-30 h-30 b-r-22 me-0"><i class="ti ti-edit"></i></a>
                                            <button class="btn btn-light-danger icon-btn w-30 h-30 b-r-22 delete-btn"><i class="ti ti-trash"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Approvisionnement hij</td>
                                        <td>Approvisionnement hij détaillée</td>
                                        <td>14 Avr,2025 10:15</td>
                                        <td>
                                            <a href="{{ route('supplies.details', 5) }}" class="btn btn-light-primary icon-btn w-30 h-30 b-r-22 me-0"><i class="ti ti-eye"></i></a>
                                            <a href="{{ route('supplies.edit', 5) }}" class="btn btn-light-success icon-btn w-30 h-30 b-r-22 me-0"><i class="ti ti-edit"></i></a>
                                            <button class="btn btn-light-danger icon-btn w-30 h-30 b-r-22 delete-btn"><i class="ti ti-trash"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Approvisionnement klm</td>
                                        <td>Approvisionnement klm détaillée</td>
                                        <td>15 Avr,2025 10:15</td>
                                        <td>
                                            <a href="{{ route('supplies.details', 6) }}" class="btn btn-light-primary icon-btn w-30 h-30 b-r-22 me-0"><i class="ti ti-eye"></i></a>
                                            <a href="{{ route('supplies.edit', 6) }}" class="btn btn-light-success icon-btn w-30 h-30 b-r-22 me-0"><i class="ti ti-edit"></i></a>
                                            <button class="btn btn-light-danger icon-btn w-30 h-30 b-r-22 delete-btn"><i class="ti ti-trash"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Approvisionnement nop</td>
                                        <td>Approvisionnement nop détaillée</td>
                                        <td>17 Avr,2025 10:15</td>
                                        <td>
                                            <a href="{{ route('supplies.details', 7) }}" class="btn btn-light-primary icon-btn w-30 h-30 b-r-22 me-0"><i class="ti ti-eye"></i></a>
                                            <a href="{{ route('supplies.edit', 7) }}" class="btn btn-light-success icon-btn w-30 h-30 b-r-22 me-0"><i class="ti ti-edit"></i></a>
                                            <button class="btn btn-light-danger icon-btn w-30 h-30 b-r-22 delete-btn"><i class="ti ti-trash"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Approvisionnement qrs</td>
                                        <td>Approvisionnement qrs détaillée</td>
                                        <td>19 Avr,2025 10:15</td>
                                        <td>
                                            <a href="{{ route('supplies.details', 8) }}" class="btn btn-light-primary icon-btn w-30 h-30 b-r-22 me-0"><i class="ti ti-eye"></i></a>
                                            <a href="{{ route('supplies.edit', 8) }}" class="btn btn-light-success icon-btn w-30 h-30 b-r-22 me-0"><i class="ti ti-edit"></i></a>
                                            <button class="btn btn-light-danger icon-btn w-30 h-30 b-r-22 delete-btn"><i class="ti ti-trash"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Approvisionnement tuv</td>
                                        <td>Approvisionnement tuv détaillée</td>
                                        <td>21 Avr,2025 10:15</td>
                                        <td>
                                            <a href="{{ route('supplies.details', 9) }}" class="btn btn-light-primary icon-btn w-30 h-30 b-r-22 me-0"><i class="ti ti-eye"></i></a>
                                            <a href="{{ route('supplies.edit', 9) }}" class="btn btn-light-success icon-btn w-30 h-30 b-r-22 me-0"><i class="ti ti-edit"></i></a>
                                            <button class="btn btn-light-danger icon-btn w-30 h-30 b-r-22 delete-btn"><i class="ti ti-trash"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Approvisionnement wxy</td>
                                        <td>Approvisionnement wxy détaillée</td>
                                        <td>23 Avr,2025 10:15</td>
                                        <td>
                                            <a href="{{ route('supplies.details', 10) }}" class="btn btn-light-primary icon-btn w-30 h-30 b-r-22 me-0"><i class="ti ti-eye"></i></a>
                                            <a href="{{ route('supplies.edit', 10) }}" class="btn btn-light-success icon-btn w-30 h-30 b-r-22 me-0"><i class="ti ti-edit"></i></a>
                                            <button class="btn btn-light-danger icon-btn w-30 h-30 b-r-22 delete-btn"><i class="ti ti-trash"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Approvisionnement z</td>
                                        <td>Approvisionnement z détaillée</td>
                                        <td>25 Avr,2025 10:15</td>
                                        <td>
                                            <a href="{{ route('supplies.details', 11) }}" class="btn btn-light-primary icon-btn w-30 h-30 b-r-22 me-0"><i class="ti ti-eye"></i></a>
                                            <a href="{{ route('supplies.edit', 11) }}" class="btn btn-light-success icon-btn w-30 h-30 b-r-22 me-0"><i class="ti ti-edit"></i></a>
                                            <button class="btn btn-light-danger icon-btn w-30 h-30 b-r-22 delete-btn"><i class="ti ti-trash"></i></button>
                                        </td>
                                    </tr>
                                    
                            </table>
                        </div>
                        <!-- table -->
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <!--customizer-->
    <div id="customizer"></div>

    <!-- slick-file -->
    <script src="{{ asset('assets/vendor/slick/slick.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-daterangepicker@3.1.0/daterangepicker.js"></script>
    <script src="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/js/bootstrap-datepicker.min.js"></script>

    <!-- data table js-->
    <script src="{{ asset('assets/vendor/datatable/jquery.dataTables.min.js') }}"></script>

    <!-- apexcharts js-->
    <script src="{{ asset('assets/vendor/apexcharts/apexcharts.min.js') }}"></script>

     <!-- Tooltips_popovers. Js -->
    <script src="{{ asset('assets/js/tooltips_popovers.js') }}"></script>

    <!-- api js -->
    <script src="{{ asset('assets/js/ticket.js') }}"></script>

    <!-- Dashboard js -->
    <script src="{{asset('assets/js/dashboard.js')}}"></script>

     <!-- select2 -->
     <script src="{{asset('assets/vendor/select/select2.min.js')}}"></script>

     <!--js-->
     <script src="{{asset('assets/js/select.js')}}"></script>

@endsection
