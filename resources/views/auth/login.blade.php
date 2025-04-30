@section('title', 'Login')
@include('layout.head')

@include('layout.css')

<style>
    .login-form-container {
        background-image: none !important;
    }
    a:not(.btn), .link-primary {
        color: rgb(var(--primary)) !important;
    }

    .login-logo{
        width: 100px !important;
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
                                        <img src="{{ asset('../assets/images/logo/isogaz-no-bg.png') }}" width="100"
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

                                        <div class="form-group mb-3">
                                            <label for="password" class="form-label">Mot de passe</label>
                                            <div class="input-group">
                                                <input type="password" name="password"
                                                    class="form-control ps-15 bg-transparent @error('password') is-invalid @enderror"
                                                    placeholder="Password" id="password" wire:model="password">
                                                <span class="input-group-text bg-transparent" id="basic-addon2">
                                                    <i class="fas fa-eye toggle-password" style="cursor: pointer;"></i>
                                                </span>
                                            </div>

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

@include('layout.script')
<!-- Bootstrap js-->
<script src="{{ asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
<!-- Toggle password js-->
<script src="{{ asset('assets/js/password-toggle.js') }}"></script>

