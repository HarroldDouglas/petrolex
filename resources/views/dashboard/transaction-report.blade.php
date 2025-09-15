@extends('layout.master')
@section('title', 'Transactions')
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
            <div class="col-8  p-0">
                <h4 class="main-title">Suivi et Rapports</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="#" class="f-s-14 f-w-500">
                            <span>
                                <i class="ph-duotone  ph-stack f-s-16"></i> Transactions
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
            <!-- Filter -->
            <div class="col-4 p-0">
                <div class="d-flex justify-content-end ">
                    <a type="button" href="javascript!:" data-bs-toggle="collapse" data-bs-target="#collapseFilter"
                        role="button" aria-expanded="false" aria-controls="collapseFilter"
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
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="warehouse_filter" class="form-label"> Centre de distribution : </label>
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
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="warehouse_filter" class="form-label"> Type de transactions : </label>
                                    <select id="warehouse_filter" name="warehouse" class="form-select">
                                        <option value="" selected>Tous</option>
                                        <option value="in_progress">Vente</option>
                                        <option value="delivered">Achat</option>
                                        <option value="delivered">Dépenses</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="wfirstName2" class="form-label"> Date : </label>
                                    <div>
                                        <button type="button" class="btn btn-default"
                                            style="width: 100%; background-color: white; color: black; border: 1px solid lightgrey"
                                            id="daterange-btn">
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

        <!-- Stock 1 Sales start -->
        <div class="row ticket-app">
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-lg-3">
                        <div class="card ticket-card bg-light-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <p class="f-s-16 mb-0">Total des transactions<br>(en {{ \App\Enums\Currency::from(config('countries.default_currency', 'XAF'))->symbol() }})</p>
                                    <div class="h-40 w-40 d-flex-center b-r-8 bg-white">
                                        <i class="ph-bold ph-arrows-left-right f-s-25 text-primary"></i>
                                    </div>
                                </div>
                                <h3 class="text-primary-dark">9 500 000</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card ticket-card bg-light-warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <p class="f-s-16 mb-0">Total des achats<br>(en {{ \App\Enums\Currency::from(config('countries.default_currency', 'XAF'))->symbol() }})</p>
                                    <div class="h-40 w-40 d-flex-center b-r-8 bg-white">
                                        <i class="ph-bold ph-arrow-circle-up f-s-25 text-warning"></i>
                                    </div>
                                </div>
                                <h3 class="text-warning-dark">3 000 000</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="card ticket-card bg-light-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <p class="f-s-16 mb-0">Total des ventes<br>(en {{ \App\Enums\Currency::from(config('countries.default_currency', 'XAF'))->symbol() }})</p>
                                    <div class="h-40 w-40 d-flex-center b-r-8 bg-white">
                                        <i class="ph-bold ph-currency-circle-dollar f-s-25 text-success"></i>
                                    </div>
                                </div>
                                <h3 class="text-success-dark">5 000 000</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="card ticket-card bg-light-danger">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <p class="f-s-16 mb-0">Autres dépenses<br>(en {{ \App\Enums\Currency::from(config('countries.default_currency', 'XAF'))->symbol() }})</p>
                                    <div class="h-40 w-40 d-flex-center b-r-8 bg-white">
                                        <i class="ph-bold ph-bank f-s-25 text-danger"></i>
                                    </div>
                                </div>
                                <h3 class="text-danger-dark">1 500 000</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- order report table-->
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Transactions</h4>
                    </div>
                    <div class="card-body px-0">
                        <div class="table-responsive app-scroll app-datatable-default">
                            <table class="w-100 display ticket-app-table" id="ticketdatatable">
                                <thead>
                                    <tr>
                                        <th class="w-10">N° Commande</th>
                                        <th class="w-10">N° Transaction</th>
                                        <th class="w-10">Initiateur</th>
                                        <th class="w-10">Destinataire</th>
                                        <th class="w-10">Montant ({{ \App\Enums\Currency::from(config('countries.default_currency', 'XAF'))->symbol() }})</th>
                                        <th class="w-20">Type de transaction</th>
                                        <th class="w-10">Méthode</th>
                                        <th class="w-10">Date</th>
                                        <th class="w-10">Statut</th>
                                    </tr>
                                </thead>
                                <tbody id="ticket_key_body">
                                    <tr>
                                        <td><a href="#">CMD00125</a></td>
                                        <td><a href="#">TRN00432</a></td>
                                        <td>Fotso Jules</td>
                                        <td>Centre YDE A</td>
                                        <td>30 000</td>
                                        <td>Vente de produit</td>
                                        <td>OM</td>
                                        <td>23/04/2025</td>
                                        <td><span class="badge text-outline-success">Terminée</span></td>
                                    </tr>
                                    <tr>
                                        <td><a href="#">CMD00126</a></td>
                                        <td><a href="#">TRN00433</a></td>
                                        <td>Ndongo Carine</td>
                                        <td>Fournisseur</td>
                                        <td>45 000</td>
                                        <td>Achat de Gaz</td>
                                        <td>MOMO</td>
                                        <td>24/04/2025</td>
                                        <td><span class="badge text-outline-warning">En attente</span></td>
                                    </tr>
                                    <tr>
                                        <td><a href="#">CMD00127</a></td>
                                        <td><a href="#">TRN00434</a></td>
                                        <td>Mbarga Elise</td>
                                        <td>Centre YDE C</td>
                                        <td>50 000</td>
                                        <td>Vente de produit</td>
                                        <td>Carte Bancaire</td>
                                        <td>25/04/2025</td>
                                        <td><span class="badge text-outline-danger">Échouée</span></td>
                                    </tr>
                                    <tr>
                                        <td><a href="#">CMD00128</a></td>
                                        <td><a href="#">TRN00435</a></td>
                                        <td>Kamga Lionel</td>
                                        <td>Centre YDE D</td>
                                        <td>60 000</td>
                                        <td>Vente de produit</td>
                                        <td>OM</td>
                                        <td>26/04/2025</td>
                                        <td><span class="badge text-outline-success">Terminée</span></td>
                                    </tr>
                                    <tr>
                                        <td><a href="#">CMD00129</a></td>
                                        <td><a href="#">TRN00436</a></td>
                                        <td>Fouda Mireille</td>
                                        <td>Centre YDE E</td>
                                        <td>70 000</td>
                                        <td>Vente de produit</td>
                                        <td>MOMO</td>
                                        <td>27/04/2025</td>
                                        <td><span class="badge text-outline-success">Terminée</span></td>
                                    </tr>
                                    <tr>
                                        <td><a href="#">CMD00130</a></td>
                                        <td><a href="#">TRN00437</a></td>
                                        <td>Ekani Paul</td>
                                        <td>Centre YDE F</td>
                                        <td>80 000</td>
                                        <td>Vente de produit</td>
                                        <td>Carte Bancaire</td>
                                        <td>28/04/2025</td>
                                        <td><span class="badge text-outline-warning">En attente</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 col-xxl-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex align-items-center">
                                <h4 class="card-title me-auto">Commandes</h4>
                                <div class="dropdown">
                                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button"
                                        id="chartType" data-bs-toggle="dropdown" aria-expanded="false">
                                        Graphe
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="chartType">
                                        <li><a class="dropdown-item" href="javascript:;" data-type="column">Bande</a>
                                        </li>
                                        <li><a class="dropdown-item" href="javascript:;" data-type="Pie">Camembert</a>
                                        </li>
                                        <li><a class="dropdown-item" href="javascript:;" data-type="line">Ligne</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div>
                                <div id="transaction-report-chart"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ticket table end -->
            </div>
            <!-- Ticket end -->
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
        <script src="{{ asset('assets/js/dashboard.js') }}"></script>

        <!-- select2 -->
        <script src="{{ asset('assets/vendor/select/select2.min.js') }}"></script>

        <!--js-->
        <script src="{{ asset('assets/js/select.js') }}"></script>

        <!--js-->
        <script src="{{ asset('assets/js/pages/transaction-report.js') }}"></script>

    @endsection
