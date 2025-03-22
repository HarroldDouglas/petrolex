@extends('layout.master')
@section('title', 'Column Chart')
@section('css')
    <!-- apexcharts css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/apexcharts/apexcharts.css') }}">
@endsection
@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb start -->
        <div class="row m-1">
            <div class="col-12 ">
                <h4 class="main-title">Column</h4>
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
                        <a class="f-s-14 f-w-500" href="#">Column</a>
                    </li>
                </ul>
            </div>
        </div>
        <!-- Breadcrumb end -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Basic Column Chart</h5>
                    </div>

                    <div class="card-body">
                        <div id="basic-colum"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5> Dumbbell Chart </h5>
                    </div>

                    <div class="card-body">
                        <div id="Dumbbell-chart"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5> Column Chart </h5>
                    </div>

                    <div class="card-body">
                        <div id="Column-chart"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5> Column with Markers</h5>
                    </div>

                    <div class="card-body">
                        <div id="markers-chart"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<!--customizer-->
<div id="customizer"></div>

<!-- apexcharts js-->
<script src="{{ asset('assets/vendor/apexcharts/apexcharts.min.js') }}"></script>
<script src="{{ asset('assets/vendor/apexcharts/column/dayjs.min.js') }}"></script>
<script src="{{ asset('assets/vendor/apexcharts/column/quarterOfYear.min.js') }}"></script>

<!-- Customizer js-->
<script src="{{ asset('assets/js/customizer.js') }}"></script>

<!-- js-->
<script src="{{ asset('assets/js/column.js') }}"></script>
@endsection
