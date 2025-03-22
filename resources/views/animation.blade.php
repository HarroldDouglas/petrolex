@extends('layout.master')
@section('title', 'Animation')
@section('css')

@endsection
@section('main-content')
    <div class="container-fluid ui-section">
        <!-- Breadcrumb start -->
        <div class="row m-1">
            <div class="col-12 ">
                <h4 class="main-title">Animation</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a class="f-s-14 f-w-500" href="#">
                            <span>
                              <i class="ph-duotone  ph-briefcase-metal f-s-16"></i> Advance UI
                            </span>
                        </a>
                    </li>
                    <li class="active">
                        <a class="f-s-14 f-w-500" href="#">Animation</a>
                    </li>
                </ul>
            </div>
        </div>
        <!-- Breadcrumb end -->

        <div class="row">
            <!-- example start -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Where can use? some example ..!</h5>
                    </div>
                    <div class="card-body">
                        <div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex flex-wrap gap-3">
                                        <div class="h-45 w-45 d-flex-center b-r-50 overflow-hidden text-bg-primary">
                                            <img alt=""
                                                 class="img-fluid animate__pulse animate__animated animate__infinite animate__faster"
                                                 src="{{ asset('../assets/images/avtar/2.png') }}">
                                        </div>
                                        <span class="bg-secondary h-45 w-45 d-flex-center b-r-50 position-relative">
                            <img alt="" class="img-fluid b-r-50" src="{{ asset('../assets/images/avtar/1.png') }}">
                            <span
                                class="position-absolute top-0 end-0 p-1 bg-success border border-light rounded-circle animate__animated animate__zoomIn animate__infinite animate__fast"></span>
                          </span>
                                        <span class="bg-secondary h-45 w-45 d-flex-center b-r-50 position-relative">
                            <img alt="" class="img-fluid b-r-50" src="{{ asset('../assets/images/avtar/6.png') }}">
                            <span
                                class="position-absolute top-10 start-40 translate-middle d-flex-center bg-danger border border-light rounded-circle text-center h-20 w-20 f-s-10">
                              <i
                                  class="ti ti-message-circle animate__animated animate__heartBeat animate__infinite animate__fast"></i>
                            </span>
                          </span>
                                        <span class="text-outline-primary h-45 w-45 d-flex-center b-r-50">
                            <i
                                class="ti ti-bell-ringing animate__animated animate__rubberBand animate__infinite animate__fast"></i>
                          </span>
                                        <button class="btn btn-success btn-lg" type="button"> Submit <i
                                                class="ti ti-chevrons-right animate__animated animate__fadeOutRight  animate__infinite animate__fast"></i>
                                        </button>
                                        <button class="btn btn-danger btn-lg" type="button">
                                            <i
                                                class="ti ti-trash animate__animated animate__bounceIn  animate__infinite animate__fast"></i>
                                            Delete
                                        </button>
                                        <button class="btn btn-primary btn-lg" type="button">
                                            <i
                                                class="ti ti-download animate__animated animate__bounceInDown  animate__infinite animate__slow"></i>
                                            Download
                                        </button>
                                        <button class="btn btn-warning btn-lg" type="button"> Upload <i
                                                class="ti ti-upload animate__animated animate__fadeOutRight  animate__infinite animate__fast"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- example end -->
            <div class="col-12">
                <div class="animation-blocks" data-masonry='{"percentPosition": true }'>
                    <!-- Attention seekers start -->
                    <div class="card cheatsheet-card animation-card">
                        <div class="card-header p-0">
                            <a aria-expanded="false"
                               class="btn btn-primary w-100 text-center f-s-18 f-w-500 rounded-bottom-0 py-2"
                               data-bs-toggle="collapse" href="#attention-seekers">Attention
                                seekers</a>
                        </div>
                        <div class="collapse  card-body show px-4" id="attention-seekers">
                            <ul>
                                <li>
                                    <div class="d-flex flex-wrap gap-3">
                                        <a class="btn btn-light-primary" data-ani="bounce" href="#"> bounce</a>
                                        <a class="btn btn-light-primary" data-ani="flash" href="#"> flash</a>
                                        <a class="btn btn-light-primary" data-ani="pulse" href="#"> pulse</a>
                                        <a class="btn btn-light-primary" data-ani="rubberBand" href="#">
                                            rubberBand</a>
                                        <a class="btn btn-light-primary" data-ani="shakeX" href="#"> shakeX</a>
                                        <a class="btn btn-light-primary" data-ani="shakeY" href="#"> shakeY</a>
                                        <a class="btn btn-light-primary" data-ani="headShake" href="#">
                                            headShake</a>
                                        <a class="btn btn-light-primary" data-ani="swing" href="#"> swing</a>
                                        <a class="btn btn-light-primary" data-ani="tada" href="#"> tada</a>
                                        <a class="btn btn-light-primary" data-ani="wobble" href="#"> wobble</a>
                                        <a class="btn btn-light-primary" data-ani="jello" href="#"> jello</a>
                                        <a class="btn btn-light-primary" data-ani="heartBeat" href="#">
                                            heartBeat</a>
                                    </div>
                                </li>

                            </ul>
                        </div>
                    </div>
                    <!-- Attention seekers end -->
                    <!-- Back entrances start -->
                    <div class="card cheatsheet-card animation-card">
                        <div class="card-header p-0">
                            <a aria-expanded="false"
                               class="btn btn-primary w-100 text-center f-s-18 f-w-500 rounded-bottom-0 py-2"
                               data-bs-toggle="collapse" href="#back-entrances">
                                Back entrances
                            </a>
                        </div>
                        <div class="collapse card-body show" id="back-entrances">
                            <ul>
                                <li class="d-flex flex-wrap gap-3">
                                    <a class="btn btn-light-primary" data-ani="backInDown" href="#">
                                        backInDown</a>
                                    <a class="btn btn-light-primary" data-ani="backInLeft" href="#">
                                        backInLeft</a>
                                    <a class="btn btn-light-primary" data-ani="backInRight" href="#">
                                        backInRight</a>
                                    <a class="btn btn-light-primary" data-ani="backInUp" href="#"> backInUp</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!-- Back entrances end -->
                    <!-- Back exits strat -->
                    <div class="card cheatsheet-card animation-card">
                        <div class="card-header p-0">
                            <a aria-expanded="false"
                               class="btn btn-primary w-100 text-center f-s-18 f-w-500 rounded-bottom-0 py-2"
                               data-bs-toggle="collapse" href="#back-exits">
                                Back exits
                            </a>
                        </div>
                        <div class="collapse card-body show" id="back-exits">
                            <ul>
                                <li>
                                    <div class="d-flex flex-wrap gap-3">
                                        <a class="btn btn-light-primary" data-ani="backOutDown" href="#">
                                            backOutDown</a>
                                        <a class="btn btn-light-primary" data-ani="backOutLeft" href="#">
                                            backOutLeft</a>
                                        <a class="btn btn-light-primary" data-ani="backOutRight" href="#">
                                            backOutRight</a>
                                        <a class="btn btn-light-primary" data-ani="backOutUp" href="#">
                                            backOutUp</a>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!-- Back exits end -->
                    <!-- Bouncing entrances start -->
                    <div class="card cheatsheet-card animation-card">
                        <div class="card-header p-0">
                            <a aria-expanded="false"
                               class="btn btn-primary w-100 text-center f-s-18 f-w-500 rounded-bottom-0 py-2"
                               data-bs-toggle="collapse" href="#bouncing-entrances">
                                Bouncing entrances
                            </a>
                        </div>
                        <div class="collapse card-body show" id="bouncing-entrances">
                            <ul>
                                <li>
                                    <div class="d-flex flex-wrap gap-3">
                                        <a class="btn btn-light-primary" data-ani="bounceIn" href="#">
                                            bounceIn</a>
                                        <a class="btn btn-light-primary" data-ani="bounceInDown" href="#">
                                            bounceInDown</a>
                                        <a class="btn btn-light-primary" data-ani="bounceInLeft" href="#">
                                            bounceInLeft</a>
                                        <a class="btn btn-light-primary" data-ani="bounceInRight" href="#">
                                            bounceInRight</a>
                                        <a class="btn btn-light-primary" data-ani="bounceInUp" href="#">
                                            bounceInUp</a>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!-- Bouncing entrances end -->
                    <!-- Bouncing exits start -->
                    <div class="card cheatsheet-card animation-card">
                        <div class="card-header p-0">
                            <a aria-expanded="false"
                               class="btn btn-primary w-100 text-center f-s-18 f-w-500 rounded-bottom-0 py-2"
                               data-bs-toggle="collapse" href="#bouncing-exits">
                                Bouncing exits
                            </a>
                        </div>
                        <div class="collapse card-body show" id="bouncing-exits">
                            <ul>
                                <li>
                                    <div class="d-flex flex-wrap gap-3">
                                        <a class="btn btn-light-primary" data-ani="bounceOut" href="#">
                                            bounceOut</a>
                                        <a class="btn btn-light-primary" data-ani="bounceOutDown" href="#">
                                            bounceOutDown</a>
                                        <a class="btn btn-light-primary" data-ani="bounceOutLeft" href="#">
                                            bounceOutLeft</a>
                                        <a class="btn btn-light-primary" data-ani="bounceOutRight" href="#">
                                            bounceOutRight</a>
                                        <a class="btn btn-light-primary" data-ani="bounceOutUp" href="#">
                                            bounceOutUp</a>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!-- Bouncing exits end -->
                    <!-- Fading entrances start -->
                    <div class="card cheatsheet-card animation-card">
                        <div class="card-header p-0">
                            <a aria-expanded="false"
                               class="btn btn-primary w-100 text-center f-s-18 f-w-500 rounded-bottom-0 py-2"
                               data-bs-toggle="collapse" href="#fading-entrances">
                                Fading entrances
                            </a>
                        </div>
                        <div class="collapse card-body show" id="fading-entrances">
                            <ul>
                                <li>
                                    <div class="d-flex flex-wrap gap-3">
                                        <a class="btn btn-light-primary" data-ani="fadeIn" href="#"> fadeIn</a>
                                        <a class="btn btn-light-primary" data-ani="fadeInDown" href="#">
                                            fadeInDown</a>
                                        <a class="btn btn-light-primary" data-ani="fadeInDownBig" href="#">
                                            fadeInDownBig</a>
                                        <a class="btn btn-light-primary" data-ani="fadeInLeft" href="#">
                                            fadeInLeft</a>
                                        <a class="btn btn-light-primary" data-ani="fadeInLeftBig" href="#">
                                            fadeInLeftBig</a>
                                        <a class="btn btn-light-primary" data-ani="fadeInRight" href="#">
                                            fadeInRight</a>
                                        <a class="btn btn-light-primary" data-ani="fadeInRightBig" href="#">
                                            fadeInRightBig</a>
                                        <a class="btn btn-light-primary" data-ani="fadeInUp" href="#">
                                            fadeInUp</a>
                                        <a class="btn btn-light-primary" data-ani="fadeInUpBig" href="#">
                                            fadeInUpBig</a>
                                        <a class="btn btn-light-primary" data-ani="fadeInTopLeft" href="#">
                                            fadeInTopLeft</a>
                                        <a class="btn btn-light-primary" data-ani="fadeInTopRight" href="#">
                                            fadeInTopRight</a>
                                        <a class="btn btn-light-primary" data-ani="fadeInBottomLeft" href="#">
                                            fadeInBottomLeft</a>
                                        <a class="btn btn-light-primary" data-ani="fadeInBottomRight" href="#">
                                            fadeInBottomRight</a>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!-- Fading entrances end -->
                    <!-- Fading exits start -->
                    <div class="card cheatsheet-card animation-card">
                        <div class="card-header p-0">
                            <a aria-expanded="false"
                               class="btn btn-primary w-100 text-center f-s-18 f-w-500 rounded-bottom-0 py-2"
                               data-bs-toggle="collapse" href="#fading-exits">
                                Fading exits
                            </a>
                        </div>
                        <div class="collapse card-body show" id="fading-exits">
                            <ul>
                                <li>
                                    <div class="d-flex flex-wrap gap-3">
                                        <a class="btn btn-light-primary" data-ani="fadeOut" href="#">
                                            fadeOut</a>
                                        <a class="btn btn-light-primary" data-ani="fadeOutDown" href="#">
                                            fadeOutDown</a>
                                        <a class="btn btn-light-primary" data-ani="fadeOutDownBig" href="#">
                                            fadeOutDownBig</a>
                                        <a class="btn btn-light-primary" data-ani="fadeOutLeft" href="#">
                                            fadeOutLeft</a>
                                        <a class="btn btn-light-primary" data-ani="fadeOutLeftBig" href="#">
                                            fadeOutLeftBig</a>
                                        <a class="btn btn-light-primary" data-ani="fadeOutRight" href="#">
                                            fadeOutRight</a>
                                        <a class="btn btn-light-primary" data-ani="fadeOutRightBig" href="#">
                                            fadeOutRightBig</a>
                                        <a class="btn btn-light-primary" data-ani="fadeOutUp" href="#">
                                            fadeOutUp</a>
                                        <a class="btn btn-light-primary" data-ani="fadeOutUpBig" href="#">
                                            fadeOutUpBig</a>
                                        <a class="btn btn-light-primary" data-ani="fadeOutTopLeft" href="#">
                                            fadeOutTopLeft</a>
                                        <a class="btn btn-light-primary" data-ani="fadeOutTopRight" href="#">
                                            fadeOutTopRight</a>
                                        <a class="btn btn-light-primary" data-ani="fadeOutBottomRight" href="#">
                                            fadeOutBottomRight</a>
                                        <a class="btn btn-light-primary" data-ani="fadeOutBottomLeft" href="#">
                                            fadeOutBottomLeft</a>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!-- Fading exits end -->
                    <!-- Flippers start -->
                    <div class="card cheatsheet-card animation-card">
                        <div class="card-header p-0">
                            <a aria-expanded="false"
                               class="btn btn-primary w-100 text-center f-s-18 f-w-500 rounded-bottom-0 py-2"
                               data-bs-toggle="collapse" href="#flippers">
                                Flippers
                            </a>
                        </div>
                        <div class="collapse card-body show" id="flippers">
                            <ul>
                                <li>
                                    <div class="d-flex flex-wrap gap-3">
                                        <a class="btn btn-light-primary" data-ani="flip" href="#"> flip</a>
                                        <a class="btn btn-light-primary" data-ani="flipInX" href="#">
                                            flipInX</a>
                                        <a class="btn btn-light-primary" data-ani="flipInY" href="#">
                                            flipInY</a>
                                        <a class="btn btn-light-primary" data-ani="flipOutX" href="#">
                                            flipOutX</a>
                                        <a class="btn btn-light-primary" data-ani="flipOutY" href="#">
                                            flipOutY</a>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!-- Flippers end -->
                    <!-- Lightspeed start -->
                    <div class="card cheatsheet-card animation-card">
                        <div class="card-header p-0">
                            <a aria-expanded="false"
                               class="btn btn-primary w-100 text-center f-s-18 f-w-500 rounded-bottom-0 py-2"
                               data-bs-toggle="collapse" href="#lightspeed">
                                Lightspeed
                            </a>
                        </div>
                        <div class="collapse card-body show" id="lightspeed">
                            <ul>
                                <li>
                                    <div class="d-flex flex-wrap gap-3">
                                        <a class="btn btn-light-primary" data-ani="lightSpeedInRight" href="#">
                                            lightSpeedInRight</a>
                                        <a class="btn btn-light-primary" data-ani="lightSpeedInLeft" href="#">
                                            lightSpeedInLeft</a>
                                        <a class="btn btn-light-primary" data-ani="lightSpeedOutRight" href="#">
                                            lightSpeedOutRight</a>
                                        <a class="btn btn-light-primary" data-ani="lightSpeedOutLeft" href="#">
                                            lightSpeedOutLeft</a>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!-- Lightspeed end -->
                    <!-- Rotating entrances start -->
                    <div class="card cheatsheet-card animation-card">
                        <div class="card-header p-0">
                            <a aria-expanded="false"
                               class="btn btn-primary w-100 text-center f-s-18 f-w-500 rounded-bottom-0 py-2"
                               data-bs-toggle="collapse" href="#rotating-entrances">
                                Rotating entrances
                            </a>
                        </div>
                        <div class="collapse card-body show" id="rotating-entrances">
                            <ul>
                                <li>
                                    <div class="d-flex flex-wrap gap-3">
                                        <a class="btn btn-light-primary" data-ani="rotateIn" href="#">
                                            rotateIn</a>
                                        <a class="btn btn-light-primary" data-ani="rotateInDownLeft" href="#">
                                            rotateInDownLeft</a>
                                        <a class="btn btn-light-primary" data-ani="rotateInDownRight" href="#">
                                            rotateInDownRight</a>
                                        <a class="btn btn-light-primary" data-ani="rotateInUpLeft" href="#">
                                            rotateInUpLeft</a>
                                        <a class="btn btn-light-primary" data-ani="rotateInUpRight" href="#">
                                            rotateInUpRight</a>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!-- Rotating entrances end -->
                    <!-- Rotating exits start -->
                    <div class="card cheatsheet-card animation-card">
                        <div class="card-header p-0">
                            <a aria-expanded="false"
                               class="btn btn-primary w-100 text-center f-s-18 f-w-500 rounded-bottom-0 py-2"
                               data-bs-toggle="collapse" href="#rotating-exits">
                                Rotating exits
                            </a>
                        </div>
                        <div class="collapse card-body show" id="rotating-exits">
                            <ul>
                                <li>
                                    <div class="d-flex flex-wrap gap-3">
                                        <a class="btn btn-light-primary" data-ani="rotateOut" href="#">
                                            rotateOut</a>
                                        <a class="btn btn-light-primary" data-ani="rotateOutDownLeft" href="#">
                                            rotateOutDownLeft</a>
                                        <a class="btn btn-light-primary" data-ani="rotateOutDownRight" href="#">
                                            rotateOutDownRight</a>
                                        <a class="btn btn-light-primary" data-ani="rotateOutUpLeft" href="#">
                                            rotateOutUpLeft</a>
                                        <a class="btn btn-light-primary" data-ani="rotateOutUpRight" href="#">
                                            rotateOutUpRight</a>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!-- Rotating exits end -->
                    <!-- Specials start -->
                    <div class="card cheatsheet-card animation-card">
                        <div class="card-header p-0">
                            <a aria-expanded="false"
                               class="btn btn-primary w-100 text-center f-s-18 f-w-500 rounded-bottom-0 py-2"
                               data-bs-toggle="collapse" href="#specials">
                                Specials
                            </a>
                        </div>
                        <div class="collapse card-body show" id="specials">
                            <ul>
                                <li>
                                    <div class="d-flex flex-wrap gap-3">
                                        <a class="btn btn-light-primary" data-ani="hinge" href="#"> hinge</a>
                                        <a class="btn btn-light-primary" data-ani="jackInTheBox" href="#">
                                            jackInTheBox</a>
                                        <a class="btn btn-light-primary" data-ani="rollIn" href="#"> rollIn</a>
                                        <a class="btn btn-light-primary" data-ani="rollOut" href="#">
                                            rollOut</a>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!-- Specials end -->
                    <!-- Zooming entrances start -->
                    <div class="card cheatsheet-card animation-card">
                        <div class="card-header p-0">
                            <a aria-expanded="false"
                               class="btn btn-primary w-100 text-center f-s-18 f-w-500 rounded-bottom-0 py-2"
                               data-bs-toggle="collapse" href="#zooming-entrances">
                                Zooming entrances
                            </a>
                        </div>
                        <div class="collapse card-body show" id="zooming-entrances">
                            <ul>
                                <li>
                                    <div class="d-flex flex-wrap gap-3">
                                        <a class="btn btn-light-primary" data-ani="zoomIn" href="#"> zoomIn</a>
                                        <a class="btn btn-light-primary" data-ani="zoomInDown" href="#">
                                            zoomInDown</a>
                                        <a class="btn btn-light-primary" data-ani="zoomInLeft" href="#">
                                            zoomInLeft</a>
                                        <a class="btn btn-light-primary" data-ani="zoomInRight" href="#">
                                            zoomInRight</a>
                                        <a class="btn btn-light-primary" data-ani="zoomInUp" href="#">
                                            zoomInUp</a>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="card cheatsheet-card animation-card">
                        <div class="card-header p-0">
                            <a aria-expanded="false"
                               class="btn btn-primary w-100 text-center f-s-18 f-w-500 rounded-bottom-0 py-2"
                               data-bs-toggle="collapse" href="#zooming-exits">
                                Zooming entrances
                            </a>
                        </div>
                        <div class="collapse card-body show" id="zooming-exits">
                            <ul>
                                <li>
                                    <div class="d-flex flex-wrap gap-3">
                                        <a class="btn btn-light-primary" data-ani="zoomOut" href="#">
                                            zoomOut</a>
                                        <a class="btn btn-light-primary" data-ani="zoomOutDown" href="#">
                                            zoomOutDown</a>
                                        <a class="btn btn-light-primary" data-ani="zoomOutLeft" href="#">
                                            zoomOutLeft</a>
                                        <a class="btn btn-light-primary" data-ani="zoomOutRight" href="#">
                                            zoomOutRight</a>
                                        <a class="btn btn-light-primary" data-ani="zoomOutUp" href="#">
                                            zoomOutUp</a>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!-- Zooming entrances end -->
                    <!-- Sliding entrances start -->
                    <div class="card cheatsheet-card animation-card">
                        <div class="card-header p-0">
                            <a aria-expanded="false"
                               class="btn btn-primary w-100 text-center f-s-18 f-w-500 rounded-bottom-0 py-2"
                               data-bs-toggle="collapse" href="#sliding-entrances">
                                Sliding entrances
                            </a>
                        </div>
                        <div class="collapse card-body show" id="sliding-entrances">
                            <ul>
                                <li>
                                    <div class="d-flex flex-wrap gap-3">
                                        <a class="btn btn-light-primary" data-ani="slideInDown" href="#">
                                            slideInDown</a>
                                        <a class="btn btn-light-primary" data-ani="slideInLeft" href="#">
                                            slideInLeft</a>
                                        <a class="btn btn-light-primary" data-ani="slideInRight" href="#">
                                            slideInRight</a>
                                        <a class="btn btn-light-primary" data-ani="slideInUp" href="#">
                                            slideInUp</a>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!-- Sliding entrances end -->
                    <!-- Sliding exits start -->
                    <div class="card cheatsheet-card animation-card">
                        <div class="card-header p-0">
                            <a aria-expanded="false"
                               class="btn btn-primary w-100 text-center f-s-18 f-w-500 rounded-bottom-0 py-2"
                               data-bs-toggle="collapse" href="#sliding-exits">
                                Sliding exits
                            </a>
                        </div>
                        <div class="collapse card-body show" id="sliding-exits">
                            <ul>
                                <li>
                                    <div class="d-flex flex-wrap gap-3">
                                        <a class="btn btn-light-primary" data-ani="slideOutDown" href="#">
                                            slideOutDown</a>
                                        <a class="btn btn-light-primary" data-ani="slideOutLeft" href="#">
                                            slideOutLeft</a>
                                        <a class="btn btn-light-primary" data-ani="slideOutRight" href="#">
                                            slideOutRight</a>
                                        <a class="btn btn-light-primary" data-ani="slideOutUp" href="#">
                                            slideOutUp</a>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!-- Sliding exits end -->
                </div>
            </div>
            <!-- How to use start -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>How to use</h5>
                    </div>
                    <div class="card-body">
                        <div>
                            <div class="row">
                                <div class="col-12">
                                    <ol class="list-group list-group-numbered">
                                        <li class="list-group-item d-flex justify-content-between align-items-start">
                                            <div class="ms-2 me-auto">
                                                <h6> By adding clases </h6>
                                                <p>Add the class <code>animate__animated</code> to an element,
                                                    along with any of the
                                                    animation names (don't forget the <code>animate__</code>
                                                    prefix!): </p>
                                                <p>
                                                    <code> &lt;h5&gt; class="animate__animated
                                                        animate__bounce">An animated element &lt;/h5&gt; </code>
                                                </p>
                                                <p class="fw-600 ms-3 mt-3">Utility Classes </p>
                                                <div class="ms-4">
                                                    <p>Animate.css provides the following delays:</p>
                                                    <code>animate__delay-2s, animate__delay-3s,
                                                        animate__delay-4s, animate__delay-5s</code>
                                                    <p class="mt-2">Slow, slower, fast, and Faster classes</p>
                                                    <code>animate__slow, animate__slower, animate__fast,
                                                        animate__faster</code>
                                                    <p class="mt-2">Repeating classes</p>
                                                    <code>animate__repeat-1, animate__repeat-2,
                                                        animate__repeat-3, animate__infinite</code>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-start">
                                            <div class="ms-2 me-auto">
                                                <h6> Using @keyframes</h6>
                                                <p>Even though the library provides you a few helper classes
                                                    like the
                                                    <code>animated</code> class to get you up running quickly,
                                                    you can directly use the
                                                    provided animations <code>keyframes</code>. This provides a
                                                    flexible way to use
                                                    Animate.css with your current projects without having to
                                                    refactor your HTML code.
                                                </p>
                                                <p class="fw-500">Example:</p>
                                                <p>
                                                    <code
                                                        class="d-flex flex-column"> .my-element { <span> display: inline-block; </span><span> margin: 0 0.5rem; </span><span> animation: bounce; /* referring directly to the animation's @keyframe declaration */ </span><span> animation-duration: 2s; /* don't forget to set a duration! */ </span>}
                                                    </code>
                                                </p>
                                            </div>
                                        </li>
                                        <li class="list-group-item d-sm-flex justify-content-between align-items-start">
                                            <div class="ms-2 me-auto">
                                                <h6> CSS Custom Properties (CSS Variables) </h6>
                                                <p>Animate.css uses custom properties (also known as CSS
                                                    variables) to define the
                                                    animation's duration, delay, and iterations. This makes
                                                    Animate.css very flexible and
                                                    customizable. Need to change an animation duration? Just set
                                                    a new value globally or
                                                    locally.</p>
                                                <p class="fw-500">Example:</p>
                                                <p>
                                                    <code class="d-flex flex-column"> /* This only changes this
                                                        particular animation duration */ <span> .animate__animated.animate__bounce { </span><span> --animate-duration: 2s; </span>
                                                        }
                                                        <span> </span><span> /* This changes all the animations globally */ </span><span> :root { </span><span> --animate-duration: 800ms; </span><span> --animate-delay: 0.9s; </span><span> } </span>
                                                    </code>
                                                </p>
                                            </div>
                                        </li>
                                    </ol>
                                </div>
                                <div class="col-6"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- How to use end -->

        </div>
    </div>
@endsection

@section('script')
    <!--customizer-->
    <div id="customizer"></div>

    <!-- masonry js-->
    <script src="{{ asset('assets/vendor/masonry/masonry.pkgd.min.js') }}"></script>

    <!-- animation page js-->
    <script src="{{ asset('assets/js/animation.js') }}"></script>
@endsection
