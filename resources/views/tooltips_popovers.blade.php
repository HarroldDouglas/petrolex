@extends('layout.master')
@section('title', 'Tooltips Popovers')
@section('css')

@endsection
@section('main-content')
    <div class="container-fluid ui-section">
        <!-- Breadcrumb start -->
        <div class="row m-1">
            <div class="col-12 ">
                <h4 class="main-title">Tooltips Popovers</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a class="f-s-14 f-w-500" href="#">
                              <span>
                                <i class="ph-duotone  ph-briefcase-metal f-s-16"></i> Advance UI
                              </span>
                        </a>
                    </li>
                    <li class="active">
                        <a class="f-s-14 f-w-500" href="#">Tooltips Popovers</a>
                    </li>
                </ul>
            </div>
        </div>
        <!-- Breadcrumb end -->
        <div class="row">
            <!-- Default Tooltips start -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Default Tooltips </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-3">
                            <button class="btn btn-primary" data-bs-placement="top" data-bs-toggle="tooltip"
                                    title="Custom tooltip"
                                    type="button">Custom tooltip
                            </button>
                            <span class="d-inline-block" data-bs-toggle="tooltip" tabindex="0"
                                  title="Disabled tooltip">
                      <button class="btn bg-secondary-300" disabled type="button">Disabled Tooltips</button>
                    </span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Default Tooltips end -->
            <!-- Placement start -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Placement </h5>
                    </div>
                    <div class="card-body d-flex flex-wrap gap-3">
                        <div>
                            <button class="btn btn-primary" data-bs-placement="top" data-bs-toggle="tooltip"
                                    title="Tooltip on top"
                                    type="button">
                                Tooltip on top
                            </button>
                        </div>
                        <div>
                            <button class="btn btn-secondary" data-bs-placement="right"
                                    data-bs-toggle="tooltip"
                                    title="Tooltip on right"
                                    type="button">
                                Tooltip on right
                            </button>

                        </div>
                        <div>
                            <button class="btn btn-success" data-bs-placement="bottom"
                                    data-bs-toggle="tooltip"
                                    title="Tooltip on bottom"
                                    type="button">
                                Tooltip on bottom
                            </button>
                        </div>

                        <div>
                            <button class="btn btn-danger" data-bs-placement="left" data-bs-toggle="tooltip"
                                    title="Tooltip on left"
                                    type="button">
                                Tooltip on left
                            </button>
                        </div>

                    </div>
                </div>
            </div>
            <!-- Placement end -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>HTML</h5>
                    </div>
                    <div class="card-body">
                        <button class="btn btn-warning" data-bs-html="true" data-bs-toggle="tooltip"
                                title="<em>Tooltip</em> <u>with</u> <b>HTML</b>"
                                type="button">
                            Tooltip with HTML
                        </button>
                    </div>
                </div>
            </div>
            <!-- Colors Tooltips start -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Colors Tooltips</h5>
                    </div>
                    <div class="card-body d-flex flex-wrap gap-2">
                        <button class="btn btn-primary" data-bs-custom-class="custom-primary"
                                data-bs-html="true"
                                data-bs-toggle="tooltip" title="Primary" type="button">
                            Primary
                        </button>
                        <button class="btn btn-secondary" data-bs-custom-class="custom-secondary"
                                data-bs-html="true"
                                data-bs-toggle="tooltip" title="Secondary"
                                type="button">
                            Secondary
                        </button>
                        <button class="btn btn-success" data-bs-custom-class="custom-success"
                                data-bs-html="true"
                                data-bs-toggle="tooltip" title="Success" type="button">
                            Success
                        </button>
                        <button class="btn btn-danger" data-bs-custom-class="custom-danger"
                                data-bs-html="true"
                                data-bs-toggle="tooltip" title="Danger" type="button">
                            Danger
                        </button>
                        <button class="btn btn-warning" data-bs-custom-class="custom-warning"
                                data-bs-html="true"
                                data-bs-toggle="tooltip" title="Warning" type="button">
                            Warning
                        </button>
                        <button class="btn btn-info" data-bs-custom-class="custom-info" data-bs-html="true"
                                data-bs-toggle="tooltip" title="Info" type="button">
                            Info
                        </button>
                        <button class="btn btn-light" data-bs-custom-class="custom-light"
                                data-bs-html="true"
                                data-bs-toggle="tooltip" title="Light" type="button">
                            Light
                        </button>
                        <button class="btn btn-dark" data-bs-custom-class="custom-dark" data-bs-html="true"
                                data-bs-toggle="tooltip" title="Dark" type="button">
                            Dark
                        </button>
                    </div>
                </div>
            </div>
            <!-- Colors Tooltips end -->
            <!-- Custom popovers start -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Custom popovers </h5>
                    </div>
                    <div class="card-body">
                        <a class="btn text-light-warning"
                           data-bs-content="And here's some amazing content. It's very engaging. Right?"
                           data-bs-title="Dismissible popover"
                           data-bs-toggle="popover"
                           data-bs-trigger="focus" role="button"
                           tabindex="0">Dismissible
                            popover</a>
                    </div>
                </div>
            </div>
            <!-- Custom popovers end -->

        </div>
    </div>
@endsection

@section('script')
<!--customizer-->
<div id="customizer"></div>

<!-- Tooltip js  -->
<script src="{{ asset('assets/js/tooltips_popovers.js') }}"></script>
@endsection
