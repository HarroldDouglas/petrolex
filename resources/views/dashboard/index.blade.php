@extends('layout.master')
@section('title', 'Ticket')
@section('css')
    <!-- slick css -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/slick/slick.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/slick/slick-theme.css') }}">

    <!-- Data Table css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/datatable/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/datatable/datatable2/buttons.dataTables.min.css') }}">
@endsection
@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb start -->
        <div class="row m-1">
            <div class="col-12 ">
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
        </div>
        <!-- Breadcrumb end -->

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
                                        <th>
                                            <div class="checkbox-wrapper">
                                                <label class="check-box m-0">
                                                    <input id="select-all1" type="checkbox">
                                                    <span class="checkmark outline-secondary"></span>
                                                </label>
                                            </div>
                                        </th>
                                        <th>ID Bouteille</th>
                                        <th>Client</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="ticket_key_body">
                                    <tr>
                                        <td>
                                            <div class="checkbox-wrapper">
                                                <label class="check-box m-0">
                                                    <input type="checkbox">
                                                    <span class="checkmark outline-secondary"></span>
                                                </label>
                                            </div>
                                        </td>
                                        <td>BTL-2044</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                Gavin Cortez
                                            </div>
                                        </td>
                                        <td><span class="badge text-outline-success">En cours</span></td>
                                        <td>1 jan 2024</td>
                                        <td>
                                            <div class="btn-group dropdown-icon-none">
                                                <button aria-expanded="false"
                                                    class="btn border-0 icon-btn b-r-4 dropdown-toggle active"
                                                    data-bs-auto-close="true" data-bs-toggle="dropdown" type="button">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="{{ route('ticket_details') }}"><i
                                                                class="ti ti-eye text-primary me-2"></i> View
                                                        </a></li>
                                                    <li><a class="dropdown-item" href="#"><i
                                                                class="ti ti-edit text-success me-2"></i>
                                                            Edit </a></li>
                                                    <li><a class="dropdown-item delete-btn" href="#"><i
                                                                class="ti ti-trash text-danger me-2"></i> Delete
                                                        </a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="checkbox-wrapper">
                                                <label class="check-box m-0">
                                                    <input type="checkbox">
                                                    <span class="checkmark outline-secondary"></span>
                                                </label>
                                            </div>
                                        </td>
                                        <td>AR 1763</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="h-30 w-30 d-flex-center b-r-50 overflow-hidden text-bg-dark me-2">
                                                    <img alt="" class="img-fluid"
                                                        src="{{ asset('../assets/images/avtar/14.png') }}">
                                                </div>
                                                Martena Mccray
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge text-outline-danger">lower</span>
                                        </td>
                                        <td>8 jan 2024</td>
                                        <td>
                                            <div class="btn-group dropdown-icon-none">
                                                <button aria-expanded="false"
                                                    class="btn border-0 icon-btn b-r-4 dropdown-toggle active"
                                                    data-bs-auto-close="true" data-bs-toggle="dropdown" type="button">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="{{ route('ticket_details') }}"><i
                                                                class="ti ti-eye text-primary me-2"></i> View
                                                        </a></li>
                                                    <li><a class="dropdown-item" href="#"><i
                                                                class="ti ti-edit text-success me-2"></i>
                                                            Edit </a></li>
                                                    <li><a class="dropdown-item delete-btn" href="#"><i
                                                                class="ti ti-trash text-danger me-2"></i> Delete
                                                        </a></li>
                                                </ul>
                                            </div>
                                        </td>

                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="checkbox-wrapper">
                                                <label class="check-box m-0">
                                                    <input type="checkbox">
                                                    <span class="checkmark outline-secondary"></span>
                                                </label>
                                            </div>
                                        </td>
                                        <td>AR 7452</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="h-30 w-30 d-flex-center b-r-50 overflow-hidden text-bg-info me-2">
                                                    <img alt="" class="img-fluid"
                                                        src="{{ asset('../assets/images/avtar/10.png') }}">
                                                </div>
                                                Gavin Joyce
                                            </div>
                                        </td>
                                        <td><span class="badge text-outline-success">High</span></td>
                                        <td>14 jan 2024</td>
                                        <td>
                                            <div class="btn-group dropdown-icon-none">
                                                <button aria-expanded="false"
                                                    class="btn border-0 icon-btn b-r-4 dropdown-toggle active"
                                                    data-bs-auto-close="true" data-bs-toggle="dropdown" type="button">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="{{ route('ticket_details') }}"><i
                                                                class="ti ti-eye text-primary me-2"></i> View
                                                        </a></li>
                                                    <li><a class="dropdown-item" href="#"><i
                                                                class="ti ti-edit text-success me-2"></i>
                                                            Edit </a></li>
                                                    <li><a class="dropdown-item delete-btn" href="#"><i
                                                                class="ti ti-trash text-danger me-2"></i> Delete
                                                        </a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="checkbox-wrapper">
                                                <label class="check-box m-0">
                                                    <input type="checkbox">
                                                    <span class="checkmark outline-secondary"></span>
                                                </label>
                                            </div>
                                        </td>
                                        <td>AR 1023</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="h-30 w-30 d-flex-center b-r-50 overflow-hidden text-bg-dark me-2">
                                                    <img alt="" class="img-fluid"
                                                        src="{{ asset('../assets/images/avtar/2.png') }}">
                                                </div>
                                                Gloria Little
                                            </div>

                                        </td>
                                        <td><span class="badge text-outline-warning">Medium</span></td>
                                        <td>6 hours ago</td>
                                        <td>
                                            <div class="btn-group dropdown-icon-none">
                                                <button aria-expanded="false"
                                                    class="btn border-0 icon-btn b-r-4 dropdown-toggle active"
                                                    data-bs-auto-close="true" data-bs-toggle="dropdown" type="button">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="{{ route('ticket_details') }}"><i
                                                                class="ti ti-eye text-primary me-2"></i> View
                                                        </a></li>
                                                    <li><a class="dropdown-item" href="#"><i
                                                                class="ti ti-edit text-success me-2"></i>
                                                            Edit </a></li>
                                                    <li><a class="dropdown-item delete-btn" href="#"><i
                                                                class="ti ti-trash text-danger me-2"></i> Delete
                                                        </a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="checkbox-wrapper">
                                                <label class="check-box m-0">
                                                    <input type="checkbox">
                                                    <span class="checkmark outline-secondary"></span>
                                                </label>
                                            </div>
                                        </td>
                                        <td>AR 2305</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="h-30 w-30 d-flex-center b-r-50 overflow-hidden text-bg-dark me-2">
                                                    <img alt="" class="img-fluid"
                                                        src="{{ asset('../assets/images/avtar/8.png') }}">
                                                </div>
                                                Jena Gaines
                                            </div>
                                        </td>
                                        <td><span class="badge text-outline-success">High</span></td>
                                        <td>6 hours ago</td>
                                        <td>
                                            <div class="btn-group dropdown-icon-none">
                                                <button aria-expanded="false"
                                                    class="btn border-0 icon-btn b-r-4 dropdown-toggle active"
                                                    data-bs-auto-close="true" data-bs-toggle="dropdown" type="button">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="{{ route('ticket_details') }}"><i
                                                                class="ti ti-eye text-primary me-2"></i> View
                                                        </a></li>
                                                    <li><a class="dropdown-item" href="#"><i
                                                                class="ti ti-edit text-success me-2"></i>
                                                            Edit </a></li>
                                                    <li><a class="dropdown-item delete-btn" href="#"><i
                                                                class="ti ti-trash text-danger me-2"></i> Delete
                                                        </a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="checkbox-wrapper">
                                                <label class="check-box m-0">
                                                    <input type="checkbox">
                                                    <span class="checkmark outline-secondary"></span>
                                                </label>
                                            </div>
                                        </td>
                                        <td>AR2058</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="h-30 w-30 d-flex-center b-r-50 overflow-hidden text-bg-dark me-2">
                                                    <img alt="" class="img-fluid"
                                                        src="{{ asset('../assets/images/avtar/11.png') }}">
                                                </div>
                                                Jenette Caldwell
                                            </div>
                                        </td>
                                        <td><span class="badge text-outline-danger">lower</span></td>
                                        <td>20 jan 2024</td>
                                        <td>
                                            <div class="btn-group dropdown-icon-none">
                                                <button aria-expanded="false"
                                                    class="btn border-0 icon-btn b-r-4 dropdown-toggle active"
                                                    data-bs-auto-close="true" data-bs-toggle="dropdown" type="button">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="{{ route('ticket_details') }}"><i
                                                                class="ti ti-eye text-primary me-2"></i> View
                                                        </a></li>
                                                    <li><a class="dropdown-item" href="#"><i
                                                                class="ti ti-edit text-success me-2"></i>
                                                            Edit </a></li>
                                                    <li><a class="dropdown-item delete-btn" href="#"><i
                                                                class="ti ti-trash text-danger me-2"></i> Delete
                                                        </a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="checkbox-wrapper">
                                                <label class="check-box m-0">
                                                    <input type="checkbox">
                                                    <span class="checkmark outline-secondary"></span>
                                                </label>
                                            </div>
                                        </td>
                                        <td>AR 1935</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="h-30 w-30 d-flex-center b-r-50 overflow-hidden text-bg-dark me-2">
                                                    <img alt="" class="img-fluid"
                                                        src="{{ asset('../assets/images/avtar/4.png') }}">
                                                </div>
                                                Jennifer Acosta
                                            </div>
                                        </td>
                                        <td><span class="badge text-outline-warning">Medium</span></td>
                                        <td>7 June 2024</td>
                                        <td>
                                            <div class="btn-group dropdown-icon-none">
                                                <button aria-expanded="false"
                                                    class="btn border-0 icon-btn b-r-4 dropdown-toggle active"
                                                    data-bs-auto-close="true" data-bs-toggle="dropdown" type="button">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="{{ route('ticket_details') }}"><i
                                                                class="ti ti-eye text-primary me-2"></i> View
                                                        </a></li>
                                                    <li><a class="dropdown-item" href="#"><i
                                                                class="ti ti-edit text-success me-2"></i>
                                                            Edit </a></li>
                                                    <li><a class="dropdown-item delete-btn" href="#"><i
                                                                class="ti ti-trash text-danger me-2"></i> Delete
                                                        </a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="checkbox-wrapper">
                                                <label class="check-box m-0">
                                                    <input type="checkbox">
                                                    <span class="checkmark outline-secondary"></span>
                                                </label>
                                            </div>
                                        </td>
                                        <td>AR 3056</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="h-30 w-30 d-flex-center b-r-50 overflow-hidden text-bg-dark me-2">
                                                    <img alt="" class="img-fluid"
                                                        src="{{ asset('../assets/images/avtar/2.png') }}">
                                                </div>
                                                Jennifer Chang
                                            </div>
                                        </td>
                                        <td><span class="badge text-outline-success">High</span></td>
                                        <td>10 hours ago</td>
                                        <td>
                                            <div class="btn-group dropdown-icon-none">
                                                <button aria-expanded="false"
                                                    class="btn border-0 icon-btn b-r-4 dropdown-toggle active"
                                                    data-bs-auto-close="true" data-bs-toggle="dropdown" type="button">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="{{ route('ticket_details') }}"><i
                                                                class="ti ti-eye text-primary me-2"></i> View
                                                        </a></li>
                                                    <li><a class="dropdown-item" href="#"><i
                                                                class="ti ti-edit text-success me-2"></i>
                                                            Edit </a></li>
                                                    <li><a class="dropdown-item delete-btn" href="#"><i
                                                                class="ti ti-trash text-danger me-2"></i> Delete
                                                        </a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="checkbox-wrapper">
                                                <label class="check-box m-0">
                                                    <input type="checkbox">
                                                    <span class="checkmark outline-secondary"></span>
                                                </label>
                                            </div>
                                        </td>
                                        <td>AR 0358</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="h-30 w-30 d-flex-center b-r-50 overflow-hidden text-bg-dark me-2">
                                                    <img alt="" class="img-fluid"
                                                        src="{{ asset('../assets/images/avtar/3.png') }}">
                                                </div>
                                                Michael Silva
                                            </div>
                                        </td>
                                        <td><span class="badge text-outline-warning">Medium</span></td>
                                        <td>14 June 2024</td>
                                        <td>
                                            <div class="btn-group dropdown-icon-none">
                                                <button aria-expanded="false"
                                                    class="btn border-0 icon-btn b-r-4 dropdown-toggle active"
                                                    data-bs-auto-close="true" data-bs-toggle="dropdown" type="button">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="{{ route('ticket_details') }}"><i
                                                                class="ti ti-eye text-primary me-2"></i> View
                                                        </a></li>
                                                    <li><a class="dropdown-item" href="#"><i
                                                                class="ti ti-edit text-success me-2"></i>
                                                            Edit </a></li>
                                                    <li><a class="dropdown-item delete-btn" href="#"><i
                                                                class="ti ti-trash text-danger me-2"></i> Delete
                                                        </a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="checkbox-wrapper">
                                                <label class="check-box m-0">
                                                    <input type="checkbox">
                                                    <span class="checkmark outline-secondary"></span>
                                                </label>
                                            </div>
                                        </td>
                                        <td>AR 4590</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="h-30 w-30 d-flex-center b-r-50 overflow-hidden text-bg-dark me-2">
                                                    <img alt="" class="img-fluid"
                                                        src="{{ asset('../assets/images/avtar/4.png') }}">
                                                </div>
                                                Michelle House
                                            </div>
                                        </td>
                                        <td><span class="badge text-outline-danger">lower</span></td>
                                        <td>4 July 2024</td>
                                        <td>
                                            <div class="btn-group dropdown-icon-none">
                                                <button aria-expanded="false"
                                                    class="btn border-0 icon-btn b-r-4 dropdown-toggle active"
                                                    data-bs-auto-close="true" data-bs-toggle="dropdown" type="button">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="{{ route('ticket_details') }}"><i
                                                                class="ti ti-eye text-primary me-2"></i> View
                                                        </a></li>
                                                    <li><a class="dropdown-item" href="#"><i
                                                                class="ti ti-edit text-success me-2"></i>
                                                            Edit </a></li>
                                                    <li><a class="dropdown-item delete-btn" href="#"><i
                                                                class="ti ti-trash text-danger me-2"></i> Delete
                                                        </a></li>
                                                </ul>
                                            </div>
                                        </td>
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
