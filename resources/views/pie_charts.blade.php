@extends('layout.master')
@section('title', 'Pie Charts')
@section('css')
    <!-- apexcharts css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/apexcharts/apexcharts.css') }}">
@endsection
@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb start -->
        <div class="row m-1">
            <div class="col-12 ">
                <h4 class="main-title">Pie</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a class="f-s-14 f-w-500" href="#">
                      <span>
                        <i class="ph-duotone  ph-chart-pie-slice f-s-16"></i> Chart
                      </span>
                        </a>
                    </li>
                    <li class="">
                        <a class="f-s-14 f-w-500" href="#">
                      <span>
                        Apexcharts
                      </span>
                        </a>
                    </li>
                    <li class="active">
                        <a class="f-s-14 f-w-500" href="#">Pie</a>
                    </li>
                </ul>
            </div>
        </div>
        <!-- Breadcrumb end -->
        <div class="row">
            <!-- Simple Pie Chart start -->
            <div class="col-lg-6 col-xl-4">
                <div class="card equal-card ">
                    <div class="card-header">
                        <h5> Simple Pie Chart</h5>
                    </div>
                    <div class="card-body">
                        <div id="pie1"></div>
                    </div>
                </div>
            </div>
            <!-- Simple Pie Chart end -->
            <!-- updating Donut Chart start -->
            <div class="col-lg-6 col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Updating Donut Chart</h5>
                    </div>
                    <div class="card-body">
                        <div class="updating-btn-box actions text-center">
                            <button class="btn btn-sm btn-primary" id="add" onclick="appendData(this)">
                                + ADD
                            </button>

                            <button class="btn btn-sm btn-danger" id="remove">
                                - REMOVE
                            </button>

                            <button class="btn btn-sm btn-success" id="reset">
                                RESET
                            </button>
                        </div>
                        <div id="chart9"></div>
                    </div>
                </div>
            </div>
            <!-- updating Donut Chart end -->
            <!-- simple Donut Chart start -->
            <div class="col-lg-6 col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Updating Donut Chart</h5>
                    </div>
                    <div class="card-body">
                        <div id="chart3"></div>
                    </div>
                </div>
            </div>
            <!-- simple Donut Chart end -->
            <!-- simple Donut Chart start -->
            <div class="col-lg-6 col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Patterned Donut Chart</h5>
                    </div>
                    <div class="card-body">
                        <div id="chart4"></div>
                    </div>
                </div>
            </div>
            <!-- simple Donut Chart end -->
            <!-- simple Donut Chart start -->
            <div class="col-lg-6 col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Pie Chart with Image Fill</h5>
                    </div>
                    <div class="card-body">
                        <div id="chart5"></div>
                    </div>
                </div>
            </div>
            <!-- simple Donut Chart end -->


        </div>
    </div>
@endsection

@section('script')
<!--customizer-->
<div id="customizer"></div>

<!-- apexcharts-->
<script src="{{ asset('assets/vendor/apexcharts/apexcharts.min.js') }}"></script>

<!-- js-->
<script src="{{ asset('assets/js/pie_charts.js') }}"></script>
@endsection
