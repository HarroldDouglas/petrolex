<li class="header-profile">
    <a aria-controls="profilecanvasRight" class="d-block head-icon"
        data-bs-target="#profilecanvasRight" data-bs-toggle="offcanvas" href="#"
        role="button">
        <img alt="avtar" class="b-r-50 h-35 w-35 bg-dark"
            src="{{ asset(auth()->user()->getAvatar()) }}" >
    </a>

    <div aria-labelledby="profilecanvasRight" class="offcanvas offcanvas-end header-profile-canvas"
        id="profilecanvasRight" tabindex="-1" style="max-height: 330px;">
        <div class="offcanvas-body p-3">
            <ul class="m-0 p-0">
                <li class="d-flex align-items-center gap-3 mb-3">
                    <div class="d-flex-center">
                        <span class="h-45 w-45 d-flex-center b-r-10"> 
                            <img alt="" class="img-fluid b-r-10"
                                src="{{ asset(auth()->user()->getAvatar()) }}">
                        </span>
                    </div>
                    <div>
                        <h6 class="mb-0">{{ auth()->user()->first_name}} {{ auth()->user()->last_name}}</h6>
                        <p class="f-s-12 mb-0 text-secondary">{{ auth()->user()->email }}</p>
                    </div>
                </li>

                <li class="mb-2">
                    <a class="f-w-500 d-block rounded hover-bg-light" href="{{ route('users.profile') }}">
                        <i class="iconoir-user-love pe-2 f-s-18"></i>Mon Profile
                    </a>
                </li>

                <li class="mb-2">
                    <a class="f-w-500 d-block rounded hover-bg-light" href="#">
                        <i class="iconoir-help-circle pe-2 f-s-18"></i>Aide
                    </a>
                </li>

                {{-- PWA Install Button - hidden by default, shown via JS when installable --}}
                <li class="mb-2" id="pwa-install-btn" style="display: none;">
                    <a class="f-w-500 d-block rounded hover-bg-light text-primary" href="#"
                        onclick="event.preventDefault(); pwaInstall();">
                        <i class="iconoir-download pe-2 f-s-18"></i>Installer l'appli
                    </a>
                </li>
                <script>
                    (function() {
                        var isStandalone = window.matchMedia('(display-mode: standalone)').matches
                            || window.navigator.standalone === true;
                        var btn = document.getElementById('pwa-install-btn');

                        if (isStandalone) {
                            // Already running as installed app
                            if (btn) btn.remove();
                            localStorage.setItem('pwa_install_status', 'installed');
                            return;
                        }

                        // Clear stale install status (user may have uninstalled)
                        if (localStorage.getItem('pwa_install_status') === 'installed' && !isStandalone) {
                            localStorage.removeItem('pwa_install_status');
                        }

                        // Show button if prompt was already captured
                        if (window.pwaInstallReady && btn) {
                            btn.style.display = '';
                            console.log('[PWA] Install button visible');
                        }
                    })();

                    function pwaInstall() {
                        if (!window.deferredPrompt) {
                            console.warn('[PWA] No deferred prompt available');
                            return;
                        }
                        window.deferredPrompt.prompt();
                        window.deferredPrompt.userChoice.then(function(result) {
                            console.log('[PWA] User choice:', result.outcome);
                            if (result.outcome === 'accepted') {
                                localStorage.setItem('pwa_install_status', 'installed');
                                var btn = document.getElementById('pwa-install-btn');
                                if (btn) btn.style.display = 'none';
                            }
                            window.deferredPrompt = null;
                        });
                    }
                </script>

                <li>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST"
                        class="d-none">
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