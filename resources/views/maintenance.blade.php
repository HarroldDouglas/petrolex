@section('title', 'Maintenance')
@include('layout.head')

@include('layout.css')

<body>
<!-- maintenance start -->
<div class="bg-light-primary maintenance py-5">
    <div class="container">
        <div class="overlay-maintenance-card text-center">
            <img alt="" class="img-fluid" src="../assets/images/pages/OBJECTS.png">

            <h1 class="text-dark f-w-600 mt-4"> We are <br>under Maintenance</h1>
            <p class=" text-secondary">Someone has kidnapped our site. We are negotiation ransom and
                will resolve this issue in 24/7 hours</p>
            <a class="btn btn-lg btn-light-danger mt-3 b-r-22" href="{{ route('index') }}" role="button">
                <i class="ti ti-home"></i>
                Back To Home </a>

        </div>
    </div>
</div>
<!-- maintenance end -->

</body>
@section('script')

    <!-- Bootstrap js-->
    <script src="{{ asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
@endsection
