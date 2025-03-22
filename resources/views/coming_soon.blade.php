@section('title', 'Coming Soon')
@include('layout.head')

@include('layout.css')

<div class="w-100">
    <div class="container-fluid">
        <!-- axelit Comming Soon start -->
        <div class="row">
            <div class="col-12 p-0">
                <div class="coming-soon">
                    <div class="coundown-timmer p-5">
                        <div class="content">
                            <h2 class="text-dark">Axelit Comming Soon</h2>
                        </div>
                        <div class="timmer-content">
                            <div class=" numbers">
                                <span class="" id="days">00</span>
                                <span>Days</span>
                            </div>
                            <span>:</span>
                            <div class="numbers">
                                <span class="" id="hours">00</span>
                                <span>Hours</span>
                            </div>
                            <span>:</span>
                            <div class="numbers">
                                <span class="" id="minutes">00</span>
                                <span>Minutes</span>
                            </div>
                            <span>:</span>
                            <div class="numbers">
                                <span class="" id="seconds">00</span>
                                <span>Second</span>
                            </div>
                        </div>

                        <p class="font-coming-p mt-4 f-w-500"> Email Notifications for Coming Soon Pages:</p>
                        <div class="app-form mb-3 mt-2 rounded-control">
                            <div class="d-flex align-items-center flex-wrap gap-2 justify-content-center">
                                <input class="form-control form-control-lg  text-center coming-soon-input"
                                       id="username"
                                       placeholder="Enter an Email" type="email">
                                <a class="btn btn-light-primary btn-xl" href="#">Subscribe</a>
                            </div>

                        </div>
                        <div class="copy-right-section mt-3">
                            <p class="f-w-500 mb-0 f-s-18">Copyright © 2025 axelit. All rights reserved <a
                                    class="f-w-600 text-primary-dark"
                                    href="#">
                                    V1.0.0 </a> ✨</p>

                        </div>


                    </div>
                </div>
            </div>
        </div>
        <!-- axelit Comming Soon end -->
    </div>
</div>

@section('script')
<!--js-->
<script src="{{ asset('assets/js/coming_soon.js') }}"></script>

<!-- Bootstrap js-->
<script src="{{ asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
@endsection


