@extends('layout.master')
@section('title', 'Offcanvas')
@section('css')

@endsection
@section('main-content')
    <div class="container-fluid ui-section">
        <!-- Breadcrumb start -->
        <div class="row m-1">
            <div class="col-12 ">
                <h4 class="main-title">Offcanvas</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a class="f-s-14 f-w-500" href="#">
                                <span>
                                  <i class="ph-duotone  ph-briefcase-metal f-s-16"></i> Advance UI
                                </span>
                        </a>
                    </li>
                    <li class="active">
                        <a class="f-s-14 f-w-500" href="#">Offcanvas</a>
                    </li>
                </ul>
            </div>
        </div>
        <!-- Breadcrumb end -->
        <div class="row">
            <!-- Live Demo start -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Live Demo</h5>
                        <p>Use the buttons below to show and hide an offcanvas element via JavaScript that
                            toggles the .show
                            class on an element with the <span class="text-danger">.offcanvas </span>class.
                        </p>
                    </div>

                    <div class="card-body">
                        <a aria-controls="offcanvas_box" class="btn btn-light-primary m-2"
                           data-bs-toggle="offcanvas" href="#offcanvas_box"
                           role="button">
                            Link with href
                        </a>
                        <button aria-controls="offcanvas_box" class="btn btn-light-primary m-2"
                                data-bs-target="#offcanvas_box"
                                data-bs-toggle="offcanvas" type="button">
                            Button with data-bs-target
                        </button>

                        <div aria-labelledby="offcanvas_box" class="offcanvas offcanvas-start"
                             id="offcanvas_box"
                             tabindex="-1">
                            <div class="offcanvas-header">
                                <h5 class="offcanvas-title" id="offcanvas_box1">Offcanvas</h5>
                                <button aria-label="Close" class="btn-close text-reset fs-5 fs-6"
                                        data-bs-dismiss="offcanvas"
                                        type="button"></button>
                            </div>
                            <div class="offcanvas-body">
                                <div>
                                    Some text as placeholder. In real life you can have the elements you
                                    have chosen. Like, text,
                                    images, lists, etc.
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Live Demo end -->
            <!-- Placement start -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Placement</h5>
                        <p>Offcanvas Diffrent Placement Example: Left, Right & Bottom</p>
                    </div>

                    <div class="card-body">
                        <button aria-controls="offcanvasTops" class="btn btn-light-primary"
                                data-bs-target="#offcanvasTops"
                                data-bs-toggle="offcanvas" type="button">Toggle top
                            offcanvas
                        </button>

                        <div aria-labelledby="offcanvasTopsLabel" class="offcanvas offcanvas-top"
                             id="offcanvasTops"
                             tabindex="-1">
                            <div class="offcanvas-header">
                                <h5 class="offcanvas-title" id="offcanvasTopsLabel">Offcanvas top</h5>
                                <button aria-label="Close" class="btn-close" data-bs-dismiss="offcanvas"
                                        type="button"></button>
                            </div>
                            <div class="offcanvas-body">
                                ...
                            </div>
                        </div>

                        <button aria-controls="offcanvasEnd" class="btn btn-light-primary m-2"
                                data-bs-target="#offcanvasEnd"
                                data-bs-toggle="offcanvas" type="button">Toggle right
                            offcanvas
                        </button>

                        <div class="offcanvas offcanvas-end" id="offcanvasEnd" tabindex="-1">
                            <div class="offcanvas-header">
                                <h5 id="offcanvasRightLabel3">Offcanvas right</h5>
                                <button aria-label="Close" class="btn-close text-reset fs-5"
                                        data-bs-dismiss="offcanvas"
                                        type="button"></button>
                            </div>
                            <div class="offcanvas-body">
                                ...
                            </div>
                        </div>

                        <button aria-controls="offcanvasBottom" class="btn btn-light-primary m-2"
                                data-bs-target="#offcanvasBottom"
                                data-bs-toggle="offcanvas" type="button">Toggle
                            bottom offcanvas
                        </button>

                        <div aria-labelledby="offcanvasBottomLabel" class="offcanvas offcanvas-bottom"
                             id="offcanvasBottom"
                             tabindex="-1">
                            <div class="offcanvas-header">
                                <h5 class="offcanvas-title4" id="offcanvasBottomLabel">Offcanvas bottom</h5>
                                <button aria-label="Close" class="btn-close text-reset fs-5"
                                        data-bs-dismiss="offcanvas"
                                        type="button"></button>
                            </div>
                            <div class="offcanvas-body small">
                                ...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Placement end -->
            <!-- Backdrop start -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Backdrop</h5>
                        <p>Scrolling the <span class="text-danger">
                      </span> element is disabled when an offcanvas and its backdrop are visible. Use the
                            <span class="text-danger"> data-bs-scroll </span>
                            attribute to toggle scrolling and <span
                                class="text-danger"> data-bs-backdrop </span>to toggle the
                            backdrop.
                        </p>
                    </div>
                    <div class="card-body">
                        <button aria-controls="offcanvasScrolling" class="btn btn-light-primary m-2"
                                data-bs-target="#offcanvasScrolling"
                                data-bs-toggle="offcanvas" type="button">
                            Enable body
                            scrolling
                        </button>
                        <button aria-controls="offcanvasWithBackdrop" class="btn btn-light-primary m-2"
                                data-bs-target="#offcanvasWithBackdrop"
                                data-bs-toggle="offcanvas"
                                type="button">Enable backdrop
                            (default)
                        </button>
                        <button aria-controls="offcanvasWithBothOptions" class="btn btn-light-primary m-2"
                                data-bs-target="#offcanvasWithBothOptions"
                                data-bs-toggle="offcanvas"
                                type="button">Enable both
                            scrolling & backdrop
                        </button>

                        <div aria-labelledby="offcanvasScrollingLabel" class="offcanvas offcanvas-start"
                             data-bs-backdrop="false" data-bs-scroll="true"
                             id="offcanvasScrolling2" tabindex="-1">
                            <div class="offcanvas-header">
                                <h5 class="offcanvas-title" id="offcanvasScrollingLabel">Colored with
                                    scrolling</h5>
                                <button aria-label="Close" class="btn-close text-reset fs-5"
                                        data-bs-dismiss="offcanvas"
                                        type="button"></button>
                            </div>
                            <div class="offcanvas-body">
                                <p>Try scrolling the rest of the page to see this option in action.</p>
                            </div>
                        </div>
                        <div class="offcanvas offcanvas-start" id="offcanvasWithBackdrop" tabindex="-1">
                            <div class="offcanvas-header">
                                <h5 class="offcanvas-title" id="offcanvasWithBackdropLabel5">Offcanvas with
                                    backdrop</h5>
                                <button aria-label="Close" class="btn-close text-reset fs-5"
                                        data-bs-dismiss="offcanvas"
                                        type="button"></button>
                            </div>
                            <div class="offcanvas-body">
                                <p>.....</p>
                            </div>
                        </div>
                        <div class="offcanvas offcanvas-start" data-bs-scroll="true"
                             id="offcanvasWithBothOptions2"
                             tabindex="-1">
                            <div class="offcanvas-header">
                                <h5 class="offcanvas-title" id="offcanvasWithBothOptionsLabel6">Backdroped
                                    with scrolling</h5>
                                <button aria-label="Close" class="btn-close text-reset fs-5"
                                        data-bs-dismiss="offcanvas"
                                        type="button"></button>
                            </div>
                            <div class="offcanvas-body">
                                <p>Try scrolling the rest of the page to see this option in action.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Backdrop end -->
            <!-- Body scrolling start -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Body Scrolling </h5>
                        <p>Scrolling the element is disabled when an offcanvas and its backdrop are visible.
                        </p>
                    </div>

                    <div class="card-body">
                        <button aria-controls="offcanvasScrolling" class="btn btn-light-primary m-2"
                                data-bs-target="#offcanvasScrolling"
                                data-bs-toggle="offcanvas" type="button">
                            Enable body
                            scrolling
                        </button>

                        <div aria-labelledby="offcanvasScrollingLabel" class="offcanvas offcanvas-start"
                             data-bs-backdrop="false" data-bs-scroll="true"
                             id="offcanvasScrolling" tabindex="-1">
                            <div class="offcanvas-header">
                                <h5 class="offcanvas-title" id="offcanvasScrollingLabel7">Offcanvas with
                                    body scrolling</h5>
                                <button aria-label="Close" class="btn-close" data-bs-dismiss="offcanvas"
                                        type="button"></button>
                            </div>
                            <div class="offcanvas-body">
                                <p>Try scrolling the rest of the page to see this option in action.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Body scrolling end -->
            <!-- Static backdrop start -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Static Backdrop </h5>
                        <p>When backdrop is set to static, the offcanvas will not close when clicking
                            outside of it.</p>
                    </div>

                    <div class="card-body">
                        <button aria-controls="staticBackdrop" class="btn btn-light-primary m-2"
                                data-bs-target="#staticBackdrop"
                                data-bs-toggle="offcanvas" type="button">
                            Toggle static offcanvas
                        </button>

                        <div class="offcanvas offcanvas-start" data-bs-backdrop="static" id="staticBackdrop"
                             tabindex="-1">
                            <div class="offcanvas-header">
                                <h5 class="offcanvas-title" id="staticBackdropLabel9">Offcanvas</h5>
                                <button aria-label="Close" class="btn-close fs-5"
                                        data-bs-dismiss="offcanvas"
                                        type="button"></button>
                            </div>
                            <div class="offcanvas-body">
                                <div>
                                    I will not close if you click outside of me.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Static backdrop end -->

            <!-- Body scrolling and backdrop start -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Body Scrolling And Backdrop </h5>
                        <p>
                            Scrolling the element is disabled when an offcanvas and its backdrop are
                            visible. Use the
                            <span class="text-danger"> data-bs-scroll </span>attribute to toggle scrolling
                            and
                            <span class="text-danger"> data-bs-backdrop </span>to toggle the backdrop.
                        </p>
                    </div>

                    <div class="card-body">
                        <button aria-controls="offcanvasWithBothOptions" class="btn btn-light-primary m-2"
                                data-bs-target="#offcanvasWithBothOptions"
                                data-bs-toggle="offcanvas"
                                type="button">Enable both
                            scrolling & backdrop
                        </button>

                        <div class="offcanvas offcanvas-start" data-bs-scroll="true"
                             id="offcanvasWithBothOptions"
                             tabindex="-1">
                            <div class="offcanvas-header">
                                <h5 class="offcanvas-title" id="offcanvasWithBothOptionsLabel8">Backdrop
                                    with scrolling</h5>
                                <button aria-label="Close" class="btn-close" data-bs-dismiss="offcanvas"
                                        type="button"></button>
                            </div>
                            <div class="offcanvas-body">
                                <p>Try scrolling the rest of the page to see this option in action.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Body scrolling and backdrop end -->

        </div>
    </div>
@endsection

@section('script')
<!--customizer-->
<div id="customizer"></div>

@endsection
