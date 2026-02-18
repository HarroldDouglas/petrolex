<!-- scripts start-->
@livewireScripts
<!-- latest jquery-->
<script src="{{ asset('assets/js/jquery-3.6.3.min.js') }}"></script>

<!-- Bootstrap js-->
<script src="{{ asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>

<!-- Simple bar js-->
<script src="{{ asset('assets/vendor/simplebar/simplebar.js') }}"></script>

<!-- phosphor js -->
<script src="{{ asset('assets/vendor/phosphor/phosphor.js') }}" defer></script>

<!-- Custom scripts -->
<script src="{{ asset('assets/js/custom/sidebar-active-state.js') }}" defer></script>
<script src="{{ asset('assets/js/custom/nav-state-manager.js') }}" defer></script>

<!-- App js-->
<script src="{{ asset('assets/js/script.js') }}"></script>

<!-- Toatify js-->
<script src="{{ asset('assets/vendor/notifications/toastify-js.js') }}" defer></script>

<!-- sweetalert js-->
<script src="{{ asset('assets/vendor/sweetalert/sweetalert.js') }}" defer></script>

<!-- PWA: Service Worker Registration -->
<script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js')
            .then(function(registration) {
                setInterval(function() { registration.update(); }, 60 * 60 * 1000);
                registration.addEventListener('updatefound', function() {
                    var newWorker = registration.installing;
                    newWorker.addEventListener('statechange', function() {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            if (confirm('Une nouvelle version est disponible. Mettre à jour ?')) {
                                newWorker.postMessage({ type: 'SKIP_WAITING' });
                                window.location.reload();
                            }
                        }
                    });
                });
            })
            .catch(function(err) { console.error('[PWA] SW registration failed:', err); });
    }

    // Standalone mode detection
    if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true) {
        document.body.classList.add('pwa-mode');
        localStorage.setItem('pwa_install_status', 'installed');
    }

    // Show install button if prompt was already captured
    var installBtn = document.getElementById('pwa-install-btn');
    if (window.pwaInstallReady && installBtn) {
        installBtn.style.display = '';
    }
</script>
