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

<!-- PWA: Service Worker disabled temporarily -->
<script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.getRegistrations().then(function(registrations) {
            registrations.forEach(function(registration) { registration.unregister(); });
        });
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
