@extends('layout.master')
@section('title', 'Ecommerce Dashboard')
@section('css')

    <!-- apexcharts css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/apexcharts/apexcharts.css') }}">

    <!-- slick css -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/slick/slick.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/slick/slick-theme.css') }}">

    <!-- filepond css -->
    <link href="{{ asset('assets/vendor/filepond/filepond.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/filepond/image-preview.min.css') }}" rel="stylesheet">

@endsection
@section('main-content')
    <div class="container-fluid mt-3">
        <div class="row">
            <div class="col-sm-6 col-lg-4 col-xxl-2 order--1-lg">
                <div class="row">
                    <div class="col-12">
                        <div class="card orders-provided-card">
                            <div class="card-body">
                                <i class="ph-bold  ph-circle circle-bg-img"></i>
                                <div>
                                    <p class="f-s-18 f-w-600 text-dark txt-ellipsis-1">📈 Orders Provided</p>
                                    <h2 class="text-secondary-dark mb-0">2.36k</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card bg-primary-300 product-sold-card">
                            <div class="card-body">
                                <div>
                                    <h5 class="text-primary-dark f-w-600">Order Import</h5>
                                    <p class="text-dark f-w-600 mb-0 mt-2 txt-ellipsis-1"><i
                                            class="iconoir-calendar f-s-16 align-text-top me-2"></i>Oct 1 -
                                        Oct 15, 2024</p>
                                </div>
                                <div class="my-4">
                                    <h4 class="text-primary-dark">2,450</h4>
                                </div>
                                <div class="custom-progress-container">
                                    <div class="progress-bar productive"></div>
                                    <div class="progress-bar middle"></div>
                                    <div class="progress-bar idle"></div>
                                </div>
                                <div class="progress-labels">
                                    <span>Productive</span>
                                    <span>Middle</span>
                                    <span>Idle</span>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-4 col-xxl-2 order--1-lg">
                <div class="row">
                    <div class="col-12">
                        <div class="card bg-danger-300 product-sold-card">
                            <div class="card-body">
                                <div>
                                    <h5 class="text-danger-dark f-w-600 ">Product Sold</h5>
                                    <div id="productSold"></div>
                                </div>
                                <div>
                                    <h4>$6.56k</h4>
                                    <p class="mb-0 text-dark f-w-500 txt-ellipsis-1">Last Week<span
                                            class="badge bg-white-300 text-danger-dark ms-2">-45%</span>
                                    </p>
                                </div>
                                <a class="bg-danger h-35 w-35 d-flex-center b-r-50 product-sold-icon"
                                   href="{{ route('order_details') }}">
                                    <i class="iconoir-arrow-right f-w-600 f-s-18 animate__pulse animate__fadeOutRight  animate__infinite animate__slower"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card product-store-card">
                            <div class="card-body">
                                <i class="ph-bold  ph-circle circle-bg-img"></i>
                                <div>
                                    <p class="text-success f-s-18 f-w-600 txt-ellipsis-1">📝 Store
                                        Product</p>
                                    <h2 class="text-success-dark mb-0">-6,876</h2>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-7 col-lg-5">
                <div class="card">
                    <div class="card-body p-0">
                        <div>
                            <div id="productOverview"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-5 col-lg-4 col-xxl-3 order--1-lg">
                <div class="card order-detail-card">
                    <div class="pt-3">
                        <h5 class="pa-s-20">Orders details</h5>
                    </div>
                    <div class="card-body">
                        <ul class="order-content-list">
                            <li class="bg-success-300">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h6 class="text-success-dark f-w-600 mb-0"> 📦#P98056745</h6>
                                    <span class="badge text-light-success me-2">Delivered</span>
                                </div>
                                <div>
                                    <p class="text-success mb-0 txt-ellipsis-2">Your order was delivered on
                                        October 10, 2024. </p>
                                </div>
                            </li>
                            <li class="bg-info-300">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h6 class="text-info-dark f-w-600 mb-0"> 📦#5Qi4586781</h6>
                                    <span class="badge text-light-info me-2">Shipped</span>
                                </div>
                                <div>
                                    <p class="text-info mb-0 txt-ellipsis-2">Your order has been shipped and
                                        will be
                                        delivered by October 15, 2024.
                                    </p>
                                </div>
                            </li>
                            <li class="bg-danger-300">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h6 class="text-danger-dark f-w-600 mb-0"> 📦#84O5L6715</h6>
                                    <span class="badge text-light-danger me-2"> Canceled</span>
                                </div>
                                <div>
                                    <p class="text-danger-dark f-w-600 mb-0 txt-ellipsis-1">Your order was
                                        canceled.</p>
                                    <p class="text-danger mb-0 txt-ellipsis-1"><span class="f-w-600">Date Ordered</span>:
                                        October 14, 2024</p>
                                </div>
                            </li>
                            <li class="bg-success-300">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h6 class="text-success-dark f-w-600 mb-0"> 📦#H54367890</h6>
                                    <span class="badge text-light-success me-2">Delivered</span>
                                </div>
                                <div>
                                    <p class="text-success mb-0 txt-ellipsis-2">Your order was delivered on
                                        November 30, 2024.</p>
                                </div>
                            </li>
                            <li class="bg-info-300">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h6 class="text-info-dark f-w-600 mb-0"> 📦#78JY45672</h6>
                                    <span class="badge text-light-info me-2">Shipped</span>
                                </div>
                                <div>
                                    <p class="text-info mb-0 txt-ellipsis-2">Your order has been shipped and
                                        will be delivered by December 3, 2024.</p>
                                </div>
                            </li>
                            <li class="bg-danger-300">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h6 class="text-danger-dark f-w-600 mb-0"> 📦#45QRT9823</h6>
                                    <span class="badge text-light-danger me-2">Canceled</span>
                                </div>
                                <div>
                                    <p class="text-danger-dark f-w-600 mb-0 txt-ellipsis-1">Your order was
                                        canceled.</p>
                                    <p class="text-danger mb-0 txt-ellipsis-1"><span class="f-w-600">Date Ordered</span>:
                                        November 28, 2024</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-7 col-xxl-4">
                <div class="p-3">
                    <h5>Top List Products</h5>
                </div>
                <div class="card">
                    <div class="card-body px-0">
                        <div class="table-responsive app-scroll">
                            <table class="table align-middle top-products-table mb-0">
                                <thead>
                                <tr>
                                    <th scope="col">Product</th>
                                    <th scope="col">Units Sold</th>
                                    <th scope="col">Sales</th>
                                    <th scope="col">Rating</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <h6 class="mb-0">Wireless Headphones</h6>
                                        </div>
                                    </td>
                                    <td><span class="badge text-light-success f-s-12 f-w-700">250</span>
                                    </td>
                                    <td class="f-w-600 text-dark">$5,000</td>
                                    <td class="text-warning-dark f-w-600"><i
                                            class="iconoir-star-solid text-warning me-1"></i>4.8
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h6 class="mb-0">Smartwatch</h6>
                                    </td>
                                    <td><span class="badge text-light-success f-s-12 f-w-700">210</span>
                                    </td>
                                    <td class="f-w-600 text-dark">$5,000</td>
                                    <td class="text-warning-dark f-w-600"><i
                                            class="iconoir-star-solid text-warning me-1"></i>4
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h6 class="mb-0">Bluetooth Speaker</h6>
                                    </td>
                                    <td><span class="badge text-light-primary f-s-12 f-w-700">190</span>
                                    </td>
                                    <td class="f-w-600 text-dark">$4,200</td>
                                    <td class="text-warning-dark f-w-600"><i
                                            class="iconoir-star-solid text-warning me-1"></i>4.5
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h6 class="mb-0">4K Ultra HD TV</h6>
                                    </td>
                                    <td><span class="badge text-light-danger f-s-12 f-w-700">175</span></td>
                                    <td class="f-w-600 text-dark">$3,800</td>
                                    <td class="text-warning-dark f-w-600"><i
                                            class="iconoir-star-solid text-warning me-1"></i>4.9
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-7 col-xxl-5">
                <div class="p-3">
                    <h5>Sales by Country</h5>
                </div>
                <div class="row">
                    <div class="col-6 col-sm-4">
                        <div class="card country-card-warning">
                            <div class="card-body">
                                <i class="flag-icon flag-icon-deu f-s-28 b-r-20"></i>
                                <div class="mt-3">
                                    <h6 class="mb-0">Germany</h6>
                                    <p class="mb-0">3.8k</p>
                                </div>
                                <i class="iconoir-language icon-bg text-white"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4">
                        <div class="card bg-primary-300 ">
                            <div class="card-body ">
                                <i class="flag-icon flag-icon-aia f-s-28 b-r-20"></i>
                                <div class="mt-3">
                                    <h6 class="mb-0">Australia</h6>
                                    <p class="text-primary f-w-600 mb-0">3.8k</p>
                                </div>
                            </div>
                            <i class="iconoir-language icon-bg text-primary top-space-0"></i>
                            <span class="position-absolute top-0 end-0 pa-6 bg-success b-2-white border-light rounded-circle animate__animated animate__heartBeat animate__infinite animate__fast"></span>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4">
                        <div class="card country-card-danger">
                            <div class="card-body">
                                <i class="flag-icon flag-icon-can f-s-28 b-r-20"></i>
                                <div class="mt-3">
                                    <h6 class="mb-0">Germany</h6>
                                    <p class="f-w-600 mb-0">3.8k</p>
                                </div>
                                <i class="iconoir-language icon-bg text-white"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-sm-4">
                        <div class="card country-card-info">
                            <div class="card-body">
                                <i class="flag-icon flag-icon-fra f-s-28 b-r-20"></i>
                                <div class="mt-3">
                                    <h6 class="mb-0">France</h6>
                                    <p class="f-w-600 mb-0">3.8k</p>
                                </div>
                                <i class="iconoir-language icon-bg text-white"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4">
                        <div class="card country-card-danger">
                            <div class="card-body">
                                <i class="flag-icon flag-icon-usa f-s-28 b-r-20"></i>
                                <div class="mt-3">
                                    <h6 class="mb-0">USA</h6>
                                    <p class="f-w-600 mb-0">3.8k</p>
                                </div>
                                <i class="iconoir-language icon-bg text-white"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4">
                        <div class="card country-card-warning">
                            <div class="card-body">
                                <i class="flag-icon flag-icon-esp f-s-28 b-r-20"></i>
                                <div class="mt-3">
                                    <h6 class="mb-0">Spain</h6>
                                    <p class="f-w-500 mb-0">3.8k</p>
                                </div>
                                <i class="iconoir-language icon-bg text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-5 col-xxl-3">
                <div class="p-3">
                    <h5>Customer</h5>
                </div>
                <div class="card">
                    <div class="card-body">
                        <ul class="customer-list">
                            <li class="customer-list-item">
                                            <span class="text-light-primary f-w-600 h-35 w-35 d-flex-center b-r-50 customer-list-avtar">
                                                D
                                            </span>
                                <div class="customer-list-content">
                                    <h6 class="mb-0">Emily Johnson</h6>
                                </div>
                                <div>
                                                <span class="toggleCustomerButton text-light-primary h-35 w-35 d-flex-center b-r-50"
                                                      data-bs-title="Added" data-bs-toggle="tooltip">
                                                    <i class="iconoir-plus f-s-20"></i>
                                                </span>
                                </div>
                            </li>
                            <li class="customer-list-item">
                                            <span class="text-light-danger f-w-600 h-35 w-35 d-flex-center b-r-50 customer-list-avtar">
                                                AD
                                            </span>
                                <div class="customer-list-content">
                                    <h6 class="mb-0">Emily Johnson</h6>
                                </div>
                                <div>
                                                <span class="toggleCustomerButton text-light-danger h-35 w-35 d-flex-center b-r-50"
                                                      data-bs-title="Removed" data-bs-toggle="tooltip">
                                                    <i class="iconoir-minus f-s-20"></i>
                                                </span>
                                </div>
                            </li>
                            <li class="customer-list-item">
                                             <span class="text-light-warning f-w-600 h-35 w-35 d-flex-center b-r-50 customer-list-avtar">
                                                AD
                                            </span>
                                <div class="customer-list-content">
                                    <h6 class="mb-0">Emily Johnson</h6>
                                </div>
                                <div>
                                                <span class="toggleCustomerButton text-light-primary h-35 w-35 d-flex-center b-r-50"
                                                      data-bs-title="Added" data-bs-toggle="tooltip">
                                                    <i class="iconoir-plus f-s-20"></i>
                                                </span>
                                </div>
                            </li>
                            <li class="customer-list-item">
                                            <span class="text-light-info f-w-600 h-35 w-35 d-flex-center b-r-50 customer-list-avtar">
                                                AD
                                            </span>
                                <div class="customer-list-content">
                                    <h6 class="mb-0">Emily Johnson</h6>
                                </div>
                                <div>
                                                <span class="toggleCustomerButton text-light-primary h-35 w-35 d-flex-center b-r-50"
                                                      data-bs-title="Added" data-bs-toggle="tooltip">
                                                    <i class="iconoir-plus f-s-20"></i>
                                                </span>
                                </div>
                            </li>
                            <li class="customer-list-item">
                                            <span class="text-light-danger f-w-600 h-35 w-35 d-flex-center b-r-50 customer-list-avtar">
                                                AD
                                            </span>
                                <div class="customer-list-content">
                                    <h6 class="mb-0">Emily Johnson</h6>
                                </div>
                                <div>
                                              <span class="toggleCustomerButton text-light-danger h-35 w-35 d-flex-center b-r-50"
                                                    data-bs-title="Removed" data-bs-toggle="tooltip">
                                                    <i class="iconoir-minus f-s-20"></i>
                                                </span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xxl-3">
                <div class="p-3">
                    <h5>Sale Report</h5>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div>
                            <div id="totalSales"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xxl-3">
                <div class="p-3">
                    <h5>Product Category</h5>
                </div>
                <div class="card product-category-card">

                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="dropdown">
                                <a aria-expanded="false" class="text-dark" data-bs-toggle="dropdown"
                                   href="#"
                                   role="button">
                                    <i class="iconoir-align-left f-s-20 f-w-600 text-dark-dark"></i>
                                    <i class="ti ti-chevron-down ms-1 f-s-18 align-top"></i>
                                </a>
                                <ul class="dropdown-menu  dropdown-menu-end">
                                    <li><a class="dropdown-item" href="#">Last Month</a></li>
                                    <li><a class="dropdown-item" href="#">Last Week</a></li>
                                    <li><a class="dropdown-item" href="#">Last Year</a></li>
                                </ul>
                            </div>

                            <form class="app-form">
                                <select aria-label="Default select example"
                                        class="form-select custom-form-select">
                                    <option selected="">Filter</option>
                                    <option value="1">Fashion</option>
                                    <option value="2">Books</option>
                                    <option value="3">Sports</option>
                                    <option value="4">Fitness</option>
                                </select>
                            </form>
                        </div>
                        <ul class="product-category-list mt-3">
                            <li class="bg-info-300">
                                <div>
                                    <h6 class="text-info-dark mb-0">Clothing & Accessories</h6>
                                </div>
                                <div class="text-dark f-w-600 ms-2 flex-shrink-0">
                                    $5,000
                                    <span class="badge bg-white-300 text-info-dark f-w-700">5641</span>
                                </div>
                            </li>
                            <li class="bg-primary-300">
                                <div>
                                    <h6 class="text-primary-dark mb-0">Home & Kitchen</h6>
                                </div>
                                <div class="text-dark f-w-600 ms-2 flex-shrink-0">
                                    $5,000
                                    <span class="badge bg-white-300 text-primary-dark f-w-700">10k</span>
                                </div>
                            </li>
                            <li class="bg-danger-300">
                                <div>
                                    <h6 class="text-danger-dark mb-0">Electronics</h6>
                                </div>
                                <div class="text-dark f-w-600 ms-2 flex-shrink-0">
                                    $5,000
                                    <span class="badge bg-white-300 text-danger-dark f-w-700">6897</span>
                                </div>
                            </li>
                            <li class="bg-warning-300">
                                <div>
                                    <h6 class="text-warning-dark mb-0">Jewellery</h6>
                                </div>
                                <div class="text-dark f-w-600 ms-2 flex-shrink-0">
                                    $5,000
                                    <span class="badge bg-white-300 text-warning-dark f-w-700">4548</span>
                                </div>
                            </li>

                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xxl-3">
                <div class="p-3">
                    <h5>Overview</h5>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div>
                            <div id="salesChart"></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="dropdown">
                                <a aria-expanded="false" class="text-dark" data-bs-toggle="dropdown"
                                   href="#"
                                   role="button">
                                    <i class="iconoir-align-left f-s-20 f-w-600 text-dark-dark"></i>
                                    <i class="ti ti-chevron-down ms-1 f-s-18 align-top"></i>
                                </a>
                                <ul class="dropdown-menu  dropdown-menu-end">
                                    <li><a class="dropdown-item" href="#">Last Month</a></li>
                                    <li><a class="dropdown-item" href="#">Last Week</a></li>
                                    <li><a class="dropdown-item" href="#">Last Year</a></li>
                                </ul>
                            </div>

                            <form class="app-form">
                                <select aria-label="Default select example"
                                        class="form-select custom-form-select">
                                    <option selected="">Jan</option>
                                    <option value="1">Feb</option>
                                    <option value="2">Mar</option>
                                    <option value="3">..</option>
                                    <option value="4">Dec</option>
                                </select>
                            </form>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-md-6 col-xxl-3">
                <div class="p-3">
                    <h5>Transaction</h5>
                </div>
                <div class="card transaction-card">

                    <div class="card-body">

                        <div class="text-center">
                            <img alt="logo-img" src="{{ asset('../assets/images/form/done.png') }}">
                            <h6 class="text-success-dark mb-0">Thank You!</h6>
                            <p class="mb-0 f-w-600 text-success d-inline transaction-txt">Your transaction
                                was
                                successful </p>
                            <img alt="gif"
                                 class="w-30 d-inline align-text-bottom"
                                 src="{{ asset('../assets/images/dashboard/ecommerce-dashboard/celebration.gif') }}">
                        </div>

                        <div class="custom-divider"></div>

                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-dark f-w-500 mb-0"><i
                                        class="iconoir-credit-cards f-s-16 align-text-top me-2"></i>Transaction
                                    ID</p>
                                <h6 class="text-success-dark">568368657681</h6>
                            </div>
                            <div>
                                <p class="text-dark f-w-500 mb-0"><i
                                        class="iconoir-dollar-circle f-s-16 align-text-top me-2"></i>Amount
                                </p>
                                <h6 class="text-success-dark">$68.00</h6>
                            </div>
                        </div>

                        <div class="mt-3">
                            <p class="text-dark f-w-500 mb-0"><i
                                    class="iconoir-calendar f-s-16 align-text-top me-2"></i>Date & Time</p>
                            <h6 class="mb-0 text-success-dark">15 Jun 2024 • 6:90PM</h6>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('script')

    <!-- slick-file -->
    <script src="{{ asset('assets/vendor/slick/slick.min.js') }}"></script>

    <!-- apexcharts js-->
    <script src="{{ asset('assets/vendor/apexcharts/apexcharts.min.js') }}"></script>

     <!-- Tooltips_popovers. Js -->
    <script src="{{ asset('assets/js/tooltips_popovers.js') }}"></script>

    <!-- Ecommerce Dashboard js-->
    <script src="{{ asset('assets/js/ecommerce_dashboard.js') }}"></script>

@endsection
