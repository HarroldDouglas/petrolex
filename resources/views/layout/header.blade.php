<!-- Header Section starts -->
<header class="header-main">
    <div class="container-fluid">
        <div class="row">
            <div class="col-6 col-sm-4 d-flex align-items-center header-left p-0">
                <span class="header-toggle me-3">
                    <i class="iconoir-view-grid"></i>
                </span>
            </div>

            <div class="col-6 col-sm-8 d-flex align-items-center justify-content-end header-right p-0">
                <ul class="d-flex align-items-center">
                    <li class="header-apps">
                        <a class="d-block head-icon" href="#" onclick="toggleFullScreen()" role="button"
                            data-bs-toggle="tooltip" data-bs-placement="bottom" title="Passer en mode plein écran">
                            <i class="iconoir-key-command"></i>
                        </a>
                    </li>

                    <li class="header-profile">
                        <a aria-controls="profilecanvasRight" class="d-block head-icon"
                            data-bs-target="#profilecanvasRight" data-bs-toggle="offcanvas" href="#"
                            role="button">
                            <img alt="avtar" class="b-r-50 h-35 w-35 bg-dark" src="../assets/images/avtar/woman.jpg">
                        </a>

                        <div aria-labelledby="profilecanvasRight" class="offcanvas offcanvas-end header-profile-canvas"
                            id="profilecanvasRight" tabindex="-1" style="max-height: 280px;">
                            <div class="offcanvas-body p-3">
                                <ul class="m-0 p-0">
                                    <li class="d-flex align-items-center gap-3 mb-3">
                                        <div class="d-flex-center">
                                            <span class="h-45 w-45 d-flex-center b-r-10">
                                                <img alt="" class="img-fluid b-r-10"
                                                    src="../assets/images/avtar/woman.jpg">
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">Laura Monaldo</h6>
                                            <p class="f-s-12 mb-0 text-secondary">lauradesign@gmail.com</p>
                                        </div>
                                    </li>

                                    <li class="mb-2">
                                        <a class="f-w-500 d-block rounded hover-bg-light" href="{{ route('profile') }}">
                                            <i class="iconoir-user-love pe-2 f-s-18"></i>Mon Profile
                                        </a>
                                    </li>

                                    <li class="mb-2">
                                        <a class="f-w-500 d-block rounded hover-bg-light" href="{{ route('faq') }}">
                                            <i class="iconoir-help-circle pe-2 f-s-18"></i>Aide
                                        </a>
                                    </li>

                                    <li>
                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                            @csrf
                                        </form>
                                        <a class="btn btn-light-danger btn-sm w-100" href="#"
                                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                            <i class="ph-duotone ph-sign-out pe-2"></i>Se déconnecter
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>

<script>
    function toggleFullScreen() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen();
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            }
        }
    }

    // Initialiser les tooltips Bootstrap
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>
<!-- Header Section ends -->
