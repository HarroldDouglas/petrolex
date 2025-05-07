<!DOCTYPE html>
<html lang="fr" dir="ltr" data-nav-layout="vertical" data-vertical-style="overlay" data-theme-mode="light" data-header-styles="light" data-menu-styles="light" data-toggled="close">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="Author" content="Afrik Solutions">

        <title>Connexion - Isogaz</title>

        <!-- Favicon -->
        <link rel="icon" href="{{ asset('assets/images/mini-logo.ico') }}" type="image/x-icon">

        <!-- CSS Files -->
        <link href="{{ asset('assets/css/css2.css') }}" rel="stylesheet">
        @livewireStyles
        <link href="{{ asset('assets/vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/css/all.min.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/css/login.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/vendor/ionio-icon/css/iconoir.css') }}" rel="stylesheet">
    </head>

    <body style="background-color: var(--primary-color)"> <!-- Utilisation de la variable CSS primary -->
        <div class="container-fluid custom-page">
            <div class="row justify-content-center align-items-center min-vh-100">
                <div class="col-md-8 col-lg-6">
                    <div class="login-container">
                        <div class="main-signup-header">
                            <div class="welcome-header">
                                <h4>Bienvenue sur <span class="epena-red">l'administration</span></h4>
                                <div class="logo-badge">
                                    <img src="{{ asset('assets/images/logo/isogaz-no-bg.png') }}" alt="logo" class="welcome-logo">
                                </div>
                            </div>
                            <p>Suivez en temps réel les ventes, livraisons, et gestion des points de distribution, connectez-vous à votre compte pour débuter svp</p>
                            @livewire('auth.login-form')
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scripts -->
        <script src="{{ asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('assets/js/login.js') }}"></script>
        @livewireScripts
    </body>
</html>
