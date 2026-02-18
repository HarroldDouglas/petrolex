<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="author" content="Afrik-Solutions">
<link rel="icon" href="{{ '../assets/images/logo/isogaz-no-bg.png' }}" type="image/x-icon">
<link rel="shortcut icon" href="{{ '../assets/images/logo/isogaz-no-bg.png' }}" type="image/x-icon">
<title>@yield('title') | Isogaz</title>

<!-- PWA Meta Tags -->
<meta name="application-name" content="Isogaz">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="Isogaz">
<meta name="mobile-web-app-capable" content="yes">
<meta name="theme-color" content="#0089B7">
<link rel="manifest" href="/manifest.json">
<link rel="apple-touch-icon" href="/assets/images/logo/isogaz-no-bg.png">

<!-- PWA: Capture install prompt early (must be in head to not miss the event) -->
<script>
    window.deferredPrompt = null;
    window.pwaInstallReady = false;

    window.addEventListener('beforeinstallprompt', function(e) {
        e.preventDefault();
        window.deferredPrompt = e;
        window.pwaInstallReady = true;
        var btn = document.getElementById('pwa-install-btn');
        if (btn) btn.style.display = '';
    });

    window.addEventListener('appinstalled', function() {
        window.deferredPrompt = null;
        window.pwaInstallReady = false;
        localStorage.setItem('pwa_install_status', 'installed');
        var btn = document.getElementById('pwa-install-btn');
        if (btn) btn.style.display = 'none';
    });
</script>
