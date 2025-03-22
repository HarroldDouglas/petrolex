@extends('layout.master')
@section('title', 'Line Chart')
@section('css')
    <!-- apexcharts css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/apexcharts/apexcharts.css') }}">
@endsection
@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb start -->
        <div class="row m-1">
            <div class="col-12 ">
                <h4 class="main-title">Line</h4>
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
                        <a class="f-s-14 f-w-500" href="#">Line</a>
                    </li>
                </ul>
            </div>
        </div>
        <!-- Breadcrumb end -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Basic Line Chart</h5>
                    </div>
                    <div class="card-body">
                        <div id="line-chart"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Gradient Line Chart</h5>
                    </div>
                    <div class="card-body">
                        <div id="gradient-chart"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Dashed Line Chart</h5>
                    </div>
                    <div class="card-body">
                        <div id="mixed-chart"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Stepline Chart</h5>
                    </div>
                    <div class="card-body">
                        <div id="Stepline-chart"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('script')
<!--customizer-->
<div id="customizer"></div>

<!-- apexcharts-->
<script src="{{ asset('assets/vendor/apexcharts/apexcharts.min.js') }}"></script>

<!-- js-->
<script src="{{ asset('assets/js/line.js') }}"></script>
@endsection

