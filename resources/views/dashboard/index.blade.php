@extends('layout.master')
@section('title', 'Ticket')
@section('css')
     <!-- slick css -->
     <link rel="stylesheet" href="{{ asset('assets/vendor/slick/slick.css') }}">
     <link rel="stylesheet" href="{{ asset('assets/vendor/slick/slick-theme.css') }}">

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
            <div class="col-8 p-0">
                <h4 class="main-title">Accueil</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="#" class="f-s-14 f-w-500">
                            <span>
                                <i class="ph-duotone  ph-stack f-s-16"></i> Tableau de bord
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
            <!-- Filter -->
            <div class="col-4 p-0">
                <div class="d-flex justify-content-end ">
                    <a type="button" href="javascript!:" data-bs-toggle="collapse" data-bs-target="#collapseFilter" role="button"
                        aria-expanded="false" aria-controls="collapseFilter"
                        class="waves-effect btn text-info bg-white btn-md mb-2">
                        <i class="ti ti-chevron-down"></i> Filtrer
                    </a>
                </div>
            </div>
        </div>
        <!-- Breadcrumb end -->

        <!-- Filter Options-->
        <div class="row m-1">
            <div class="col-12 collapse mb-4 p-0 bg-white" id="collapseFilter">
                <div class="card bg-white text-black">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="warehouse_filter" class="form-label"> Point de distribution : </label>
                                    <select id="warehouse_filter" name="warehouse" class="form-select">
                                        <option value="" selected>Tous</option>
                                        <option value="Point A">Point A</option>
                                        <option value="Point B">Point B</option>
                                        <option value="Point C">Point C</option>
                                        <option value="Point D">Point D</option>
                                        <option value="Point E">Point E</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="wfirstName2" class="form-label"> Date : </label>
                                    <div>
                                        <button type="button" class="btn btn-default"
                                            style="width: 100%; background-color: white; color: black; border: 1px solid lightgrey" id="daterange-btn">
                                            <span>
                                                <i class="fa fa-calendar"></i> Sélectionner une date
                                            </span>
                                            <i class="fa fa-caret-down"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ticket start -->
        <div class="row ticket-app">
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-lg-3">
                        <div class="card ticket-card bg-light-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <p class="f-s-16 mb-0">Chiffre d'affaires<br>(en CFA)</p>
                                    <div class="h-40 w-40 d-flex-center b-r-8 bg-white">
                                        <i class="ph-bold ph-currency-circle-dollar f-s-25 text-primary"></i>
                                    </div>
                                </div>
                                <h3 class="text-primary-dark">15.000.000</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card ticket-card bg-light-warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <p class="f-s-16 mb-0">Commandes<br>en attente</p>
                                    <div class="h-40 w-40 d-flex-center b-r-8 bg-white">
                                        <i class="ph-bold ph-clock-countdown f-s-25 text-warning"></i>
                                    </div>
                                </div>
                                <h3 class="text-warning-dark">185</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card ticket-card bg-light-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <p class="f-s-16 mb-0">Commandes<br>livrées</p>
                                    <div class="h-40 w-40 d-flex-center b-r-8 bg-white">
                                        <i class="ph-bold ph-check-circle f-s-25 text-success"></i>
                                    </div>
                                </div>
                                <h3 class="text-success-dark">185</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card ticket-card bg-light-danger">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <p class="f-s-16 mb-0">Commandes<br>annulées</p>
                                    <div class="h-40 w-40 d-flex-center b-r-8 bg-white">
                                        <i class="ph-bold ph-x-circle f-s-25 text-danger"></i>
                                    </div>
                                </div>
                                <h3 class="text-danger-dark">185</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ticket table-->
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Les 10 dernières commandes</h4>
                    </div>
                    <div class="card-body px-0">
                        <div class="table-responsive app-scroll app-datatable-default">
                            <table class="w-100 display ticket-app-table" id="ticketdatatable">
                                <thead>
                                    <tr>
                                        <th>N° commande</th>
                                        <th>Point de distr.</th>
                                        <th>Client</th>
                                        <th>Produits</th>
                                        <th>Total (CFA)</th>
                                        <th>Livreur</th>
                                        <th>Date</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody id="ticket_key_body">
                                    <tr>
                                        <td><a href="{{ route('orders.details', 2) }}">CMD00126</a></td>
                                        <td>Point YDE B</td>
                                        <td>Ndongo Carine</td>
                                        <td>
                                            <span class="badge rounded-pill bg-light-secondary mb-1 text-small">3 x Bouteille de 9kg</span>
                                            <span class="badge rounded-pill bg-light-secondary text-small">4 x Bouteille de 12kg</span>
                                        </td>
                                        <td>45 000</td>
                                        <td>Eyoum Claire</td>
                                        <td>23/04/2025</td>
                                        <td><span class="badge text-outline-warning">En cours</span></td>
                                    </tr>
                                    <tr>
                                        <td><a href="{{ route('orders.details', 3) }}">CMD00127</a></td>
                                        <td>Point YDE C</td>
                                        <td>Mbarga Elise</td>
                                        <td>
                                            <span class="badge rounded-pill bg-light-secondary mb-1">2 x Bouteille de 9kg</span>
                                            <span class="badge rounded-pill bg-light-secondary">5 x Bouteille de 12kg</span>
                                        </td>
                                        <td>60 000</td>
                                        <td>Kamga Lionel</td>
                                        <td>24/04/2025</td>
                                        <td><span class="badge text-outline-success">Terminée</span></td>
                                    </tr>
                                    <tr>
                                        <td><a href="{{ route('orders.details', 4) }}">CMD00128</a></td>
                                        <td>Point YDE D</td>
                                        <td>Ndongmo Roger</td>
                                        <td>
                                            <span class="badge rounded-pill bg-light-secondary mb-1">4 x Bouteille de 9kg</span>
                                            <span class="badge rounded-pill bg-light-secondary">3 x Bouteille de 12kg</span>
                                        </td>
                                        <td>50 000</td>
                                        <td>Fouda Mireille</td>
                                        <td>24/04/2025</td>
                                        <td><span class="badge text-outline-success">Terminée</span></td>
                                    </tr>
                                    <tr>
                                        <td><a href="{{ route('orders.details', 5) }}">CMD00129</a></td>
                                        <td>Point YDE E</td>
                                        <td>Ekani Paul</td>
                                        <td>
                                            <span class="badge rounded-pill bg-light-secondary mb-1">5 x Bouteille de 9kg</span>
                                            <span class="badge rounded-pill bg-light-secondary">2 x Bouteille de 12kg</span>
                                        </td>
                                        <td>70 000</td>
                                        <td>Tchatchoua Paul</td>
                                        <td>25/04/2025</td>
                                        <td><span class="badge text-outline-success">Terminée</span></td>
                                    </tr>
                                    <tr>
                                        <td><a href="{{ route('orders.details', 6) }}">CMD00130</a></td>
                                        <td>Point YDE F</td>
                                        <td>Fotso Jules</td>
                                        <td>
                                            <span class="badge rounded-pill bg-light-secondary mb-1">3 x Bouteille de 9kg</span>
                                            <span class="badge rounded-pill bg-light-secondary">6 x Bouteille de 12kg</span>
                                        </td>
                                        <td>85 000</td>
                                        <td>Nguefack Jean</td>
                                        <td>25/04/2025</td>
                                        <td><span class="badge text-outline-success">Terminée</span></td>
                                    </tr>
                                    <tr>
                                        <td><a href="{{ route('orders.details', 7) }}">CMD00131</a></td>
                                        <td>Point YDE F</td>
                                        <td>Fotso Jules</td>
                                        <td>
                                            <span class="badge rounded-pill bg-light-secondary mb-1">5 x Bouteille de 9kg</span>
                                            <span class="badge rounded-pill bg-light-secondary">3 x Bouteille de 12kg</span>
                                        </td>
                                        <td>70 000</td>
                                        <td>Nguefack Jean</td>
                                        <td>26/04/2025</td>
                                        <td><span class="badge text-outline-success">Terminée</span></td>
                                    </tr>
                                    <tr>
                                        <td><a href="{{ route('orders.details', 8) }}">CMD00132</a></td>
                                        <td>Point YDE F</td>
                                        <td>Fotso Jules</td>
                                        <td>
                                            <span class="badge rounded-pill bg-light-secondary mb-1">3 x Bouteille de 9kg</span>
                                            <span class="badge rounded-pill bg-light-secondary">4 x Bouteille de 12kg</span>
                                        </td>
                                        <td>80 000</td>
                                        <td>Nguefack Jean</td>
                                        <td>27/04/2025</td>
                                        <td><span class="badge text-outline-success">Terminée</span></td>
                                    </tr>
                                    <tr>
                                        <td><a href="{{ route('orders.details', 9) }}">CMD00133</a></td>
                                        <td>Point YDE F</td>
                                        <td>Fotso Jules</td>
                                        <td>
                                            <span class="badge rounded-pill bg-light-secondary mb-1">6 x Bouteille de 9kg</span>
                                            <span class="badge rounded-pill bg-light-secondary">2 x Bouteille de 12kg</span>
                                        </td>
                                        <td>90 000</td>
                                        <td>Nguefack Jean</td>
                                        <td>28/04/2025</td>
                                        <td><span class="badge text-outline-success">Terminée</span></td>
                                    </tr>
                                    <tr>
                                        <td><a href="{{ route('orders.details', 10) }}">CMD00134</a></td>
                                        <td>Point YDE F</td>
                                        <td>Fotso Jules</td>
                                        <td>
                                            <span class="badge rounded-pill bg-light-secondary mb-1">8 x Bouteille de 9kg</span>
                                            <span class="badge rounded-pill bg-light-secondary">4 x Bouteille de 12kg</span>
                                        </td>
                                        <td>110 000</td>
                                        <td>Nguefack Jean</td>
                                        <td>29/04/2025</td>
                                        <td><span class="badge text-outline-success">Terminée</span></td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ticket table end -->
        </div>
        <!-- Ticket end -->
    </div>

    <!-- delete modal   -->
    <div class="modal fade" id="apiDeletModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body app-form">
                    <img src="{{ asset('assets/images/icons/delete-icon.png') }}" alt="" class="img-fluid">
                    <div class="text-center">
                        <h4 class="text-danger f-w-600">Are You Sure?</h4>
                        <p class="text-secondary f-s-16">You won't be able to revert this!</p>
                    </div>

                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="confirmDelete">Yes,Delet it</button>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- edit modal  -->
    <div class="modal fade" id="ticketeditModal" tabindex="-1" aria-labelledby="ticketeditModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="ticketeditModalLabel">Edit Ticket</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="ticket-form app-form">
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Title</label>
                                    <input type="text" class="form-control" placeholder="Performance Issue">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Client</label>
                                    <input type="text" class="form-control" placeholder="Michelle House">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="priority" class="form-label">Priority</label>
                                    <select class="form-select">
                                        <option selected="">Select Priority</option>
                                        <option value="Medium">Medium</option>
                                        <option value="Lower">Lower</option>
                                        <option value="High">High</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select">
                                        <option selected="">Select Status</option>
                                        <option value="open">open</option>
                                        <option value="inprogress">inprogress</option>
                                        <option value="closed">closed</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Date</label>
                                    <input type="date" class="form-control" name="trip-start">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Due Date</label>
                                    <input type="date" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Save changes</button>
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

    <!-- data table js-->
    <script src="{{ asset('assets/vendor/datatable/jquery.dataTables.min.js') }}"></script>

    <!-- api js -->
    <script src="{{ asset('assets/js/ticket.js') }}"></script>

@endsection
