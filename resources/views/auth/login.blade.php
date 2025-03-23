@section('title', 'Login')
@include('layout.head')

@include('layout.css')
<link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet">

<style>
    .login-form-container {
        background-image: none !important;
        background-color: rgba(125, 158, 24, 0.336) !important;
    }
</style>

<x-sweet-alert />
<body>
    <div class="app-wrapper d-block">
        <div class="">
            <!-- Body main section starts -->
            <main class="w-100 p-0">
                <!-- Login to your Account start -->
                <div class="container-fluid">
                    <div class="row">

                        <div class="col-12 p-0">
                            <div class="login-form-container">
                                <div class="mb-4">
                                    <a class="logo d-inline-block" href="{{ route('index') }}">
                                        <img src="{{ asset('../assets/images/logo/isogaz-no-bg.png') }}" width="250"
                                            alt="#">
                                    </a>
                                </div>
                                <div class="form_container">

                                    <form class="app-form" method="POST" action="{{ route('login') }}">
                                        @csrf
                                        <div class="mb-3 text-center">
                                            <h3>Connectez vous à votre compte</h3>
                                            <p class="f-s-12 text-secondary">Saisissez vos informations pour vous
                                                connecter</p>
                                        </div>

                                        <div class="mb-3">
                                            <label for="email" class="form-label">Adresse Email</label>
                                            <input type="email"
                                                class="form-control @error('email') is-invalid @enderror" id="email"
                                                name="email" value="{{ old('email') }}" required autofocus>
                                            @error('email')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="password" class="form-label">Mot de passe</label>
                                            <input type="password"
                                                class="form-control @error('password') is-invalid @enderror"
                                                id="password" name="password" required>
                                            @error('password')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="mb-3 form-check">
                                            <input type="checkbox" class="form-check-input" id="remember"
                                                name="remember" {{ old('remember') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="remember">Se souvenir de moi</label>
                                        </div>

                                        <div>
                                            <button type="submit" class="btn btn-primary w-100">Connexion</button>
                                        </div>
                                    </form>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Login to your Account end -->
            </main>
            <!-- Body main section ends -->
        </div>
    </div>

</body>
@section('script')

    <!-- Bootstrap js-->
    <script src="{{ asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
@endsection
