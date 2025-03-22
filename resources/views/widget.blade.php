@extends('layout.master')
@section('title', 'Widget')
@section('css')
    <!-- apexcharts css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/apexcharts/apexcharts.css') }}">

    <!-- filepond css -->
    <link href="{{ asset('assets/vendor/filepond/filepond.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/filepond/image-preview.min.css') }}" rel="stylesheet">

    <!-- slick css -->
    <link href="{{ asset('assets/vendor/slick/slick.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/slick/slick-theme.css') }}" rel="stylesheet">
@endsection
@section('main-content')
    <div class="container-fluid mt-3">
        <div class="row">
            <div class="col-sm-6 col-lg-4 col-xxl-2">
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

            <div class="col-sm-6 col-lg-4 col-xxl-2">
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

            <div class="col-md-6 col-lg-4 col-xxl-3">
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

            <div class="col-md-6 col-xxl-5 ">
                <div class="card">
                    <div class="card-body p-0">
                        <div>
                            <div id="productOverview"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xxl-4 order-1-md">
                <div class="card project-connect-card">
                    <div class="card-body pb-0">
                        <div class="text-center">
                            <h5 class=" mb-2 f-s-24">Get started <span class="text-primary f-w-700">Effortlessly.</span>
                            </h5>
                            <p class="f-s-14 text-dark pb-0 txt-ellipsis-2">
                                Connect your team's tools and unlock a unified view of every project's
                                progress, deadlines, and team contributions.
                            </p>
                        </div>
                        <div class="connect-chat-box">
                            <div class="avatar-connect-box">
                                <img alt="logo" class="avatar-connect-logo"
                                     src="{{ asset('assets/images/dashboard/project/avatar.png') }}">
                                <img alt="logo" class="dribbble-connect-logo"
                                     src="{{ asset('assets/images/dashboard/project/dribbble.png') }}">
                            </div>
                            <img alt="img" src="{{ asset('assets/images/dashboard/project/chat.png') }}">
                            <img alt="logo"
                                 class="slack-logo animate__shakeY animate__animated animate__infinite animate__slower"
                                 src="{{ asset('assets/images/dashboard/project/slack.png') }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-xxl-4">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="card project-total-card">
                            <div class="card-body">
                                <div class="d-flex position-relative">
                                    <h5 class="text-dark txt-ellipsis-1">Total Hours</h5>
                                    <div class="clock-box">
                                        <div class="clock">
                                            <div class="hour" id="hour"></div>
                                            <div class="min" id="min"></div>
                                            <div class="sec" id="sec"></div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div class="d-flex">
                                        <h2 class="text-info-dark hour-display">00H</h2>
                                    </div>
                                    <div class="progress-labels mg-t-40">
                                        <span class="text-info">Productive</span>
                                        <span class="text-info">Middle</span>
                                        <span class="text-info">Idle</span>
                                    </div>
                                    <div class="custom-progress-container info-progress">
                                        <div class="progress-bar productive"></div>
                                        <div class="progress-bar middle"></div>
                                        <div class="progress-bar idle"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="card bg-info-300 project-details-card">
                            <div class="card-body">
                                <div class="d-flex gap-2">
                                    <span class="badge bg-white-300 text-info-dark p-1  b-r-10">📱 Mobile app</span>
                                    <span class="badge dashed-1-info text-info-dark ms-2 p-1  b-r-10">Marketing</span>
                                </div>
                                <div class="my-4">
                                    <h5 class="f-w-600 text-info-dark txt-ellipsis-1">Project Alpha</h5>
                                    <p class="text-info f-s-13 txt-ellipsis-1 mb-0">Revolutionizing ideas,
                                        empowering innovation, and driving success.</p>
                                </div>
                                <div class="d-flex align-items-center justify-content-between pt-3">
                                    <ul class="avatar-group">
                                        <li class="h-35 w-35 d-flex-center b-r-50 overflow-hidden bg-light-primary b-2-primary">
                                            <img alt="avtar" class="img-fluid"
                                                 src="{{ asset('assets/images/avtar/4.png') }}">
                                        </li>
                                        <li class="h-35 w-35 d-flex-center b-r-50 overflow-hidden bg-light-success b-2-success"
                                            data-bs-title="Lennon Briggs" data-bs-toggle="tooltip">
                                            <img alt="avtar" class="img-fluid"
                                                 src="{{ asset('assets/images/avtar/5.png') }}">
                                        </li>
                                        <li class="h-35 w-35 d-flex-center b-r-50 overflow-hidden bg-light-info b-2-info"
                                            data-bs-title="Maya Horton" data-bs-toggle="tooltip">
                                            <img alt="avtar" class="img-fluid"
                                                 src="{{ asset('assets/images/avtar/6.png') }}">
                                        </li>
                                    </ul>
                                    <span class="badge bg-white-300 text-info-dark ms-2 ">🔥 1H left</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="card bg-success-300 project-details-card">
                            <div class="card-body">
                                <div class="d-flex gap-2">
                                    <span class="badge bg-white-300 text-success-dark p-1 b-r-10">⚡ API </span>
                                    <span class="badge bg-transparent dashed-1-dark-light text-success-dark ms-2 p-1 b-r-10">Web Development</span>
                                </div>
                                <div class="my-4">
                                    <h5 class="f-w-600 text-success-dark txt-ellipsis-1">Project Beta</h5>
                                    <p class="text-success f-s-13 txt-ellipsis-1 mb-0"> Innovating solutions
                                        for seamless task management efficiency.</p>
                                </div>
                                <div class="d-flex align-items-center justify-content-between pt-3">
                                    <ul class="avatar-group">
                                        <li class="h-35 w-35 d-flex-center b-r-50 overflow-hidden bg-light-primary b-2-primary">
                                            <img alt="avtar" class="img-fluid"
                                                 src="{{ asset('assets/images/avtar/4.png') }}">
                                        </li>
                                        <li class="h-35 w-35 d-flex-center b-r-50 overflow-hidden bg-light-danger b-2-danger"
                                            data-bs-title="Maya Horton" data-bs-toggle="tooltip">
                                            <img alt="avtar" class="img-fluid"
                                                 src="{{ asset('assets/images/avtar/6.png') }}">
                                        </li>
                                    </ul>
                                    <span class="badge bg-white-300 text-success-dark ms-2 ">✨ 2D left</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="card core-teams-card">
                            <div class="card-body">
                                <div class="d-flex">
                                    <h5 class="text-dark f-w-600 txt-ellipsis-1">💼 Core Teams</h5>
                                </div>
                                <div>
                                    <h2 class="text-warning-dark my-4 d-inline-flex align-items-baseline">1k
                                        <span class="f-s-12 text-dark">Team Members</span></h2>
                                    <ul class="avatar-group justify-content-start ">
                                        <li class="h-35 w-35 d-flex-center b-r-50 overflow-hidden text-bg-primary b-2-light"
                                            data-bs-title="Sabrina Torres" data-bs-toggle="tooltip">
                                            <img alt="avtar" class="img-fluid"
                                                 src="{{ asset('assets/images/avtar/4.png') }}">
                                        </li>
                                        <li class="h-35 w-35 d-flex-center b-r-50 overflow-hidden text-bg-success b-2-light"
                                            data-bs-title="Eva Bailey" data-bs-toggle="tooltip">
                                            <img alt="avtar" class="img-fluid"
                                                 src="{{ asset('assets/images/avtar/5.png') }}">
                                        </li>
                                        <li class="h-35 w-35 d-flex-center b-r-50 overflow-hidden text-bg-danger b-2-light"
                                            data-bs-title="Michael Hughes" data-bs-toggle="tooltip">
                                            <img alt="avtar" class="img-fluid"
                                                 src="{{ asset('assets/images/avtar/6.png') }}">
                                        </li>
                                        <li class="text-bg-secondary h-35 w-35 d-flex-center b-r-50"
                                            data-bs-title="10 More" data-bs-toggle="tooltip">
                                            10+
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xxl-4">
                <div class="row">
                    <div class="col-12 mb-4">
                        <div>
                            <input class="file-uploader-box filelight file-light-info"
                                   data-allow-reorder="true"
                                   id="fileUploaderBox" multiple="multiple" type="file">
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card ">
                            <div class="card-body">
                                <div class="project-expense" id="projectExpense"></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
@endsection

@section('script')
<!--customizer-->
<div id="customizer"></div>

<!-- apexcharts -->
<script src="{{ asset('assets/vendor/apexcharts/apexcharts.min.js') }}"></script>
<script src="{{ asset('assets/vendor/apexcharts/column/dayjs.min.js') }}"></script>
<script src="{{ asset('assets/vendor/apexcharts/column/quarterOfYear.min.js') }}"></script>

<!-- filepond -->
<script src="{{ asset('assets/vendor/filepond/file-encode.min.js') }}"></script>
<script src="{{ asset('assets/vendor/filepond/validate-size.min.js') }}"></script>
<script src="{{ asset('assets/vendor/filepond/validate-type.js') }}"></script>
<script src="{{ asset('assets/vendor/filepond/exif-orientation.min.js') }}"></script>
<script src="{{ asset('assets/vendor/filepond/image-preview.min.js') }}"></script>
<script src="{{ asset('assets/vendor/filepond/filepond.min.js') }}"></script>

<!-- apexcharts -->
<script src="{{ asset('assets/vendor/apexcharts/apexcharts.min.js') }}"></script>

<!--js-->
<script src="{{ asset('assets/js/widget.js') }}"></script>
@endsection
