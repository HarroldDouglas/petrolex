{{--
    Modal incitant les gestionnaires à passer par l'application mobile
    « Isogaz Scanner » pour scanner. Le scan web reste disponible en secours
    (bouton « Continuer sur le web »). Affiché une fois par session.

    Utilisation : inclure ce partial dans la vue, puis, au clic « Scanner »,
    appeler window.promptMobileAppThenScan(fnQuiLanceLeScan).
--}}
<div class="modal fade" id="mobileAppScanPrompt" tabindex="-1" aria-labelledby="mobileAppScanPromptLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div style="font-size: 2.75rem; line-height: 1;">📱</div>
                <h5 class="mt-3 mb-2" id="mobileAppScanPromptLabel">Scannez avec l'application Isogaz Scanner</h5>
                <p class="text-muted mb-4">
                    Pour des scans plus rapides et plus fiables, installez et utilisez l'application mobile
                    <strong>Isogaz Scanner</strong>. Le scan depuis le navigateur reste disponible en secours.
                </p>
                <div class="d-grid gap-2">
                    <a href="{{ route('mobile-app.install') }}" target="_blank" rel="noopener"
                        class="btn btn-primary">
                        <i class="ti ti-download me-1"></i>Installer l'application
                    </a>
                    <button type="button" class="btn btn-link text-muted" id="continueWebScanBtn">
                        Continuer sur le web
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            // Affiche le rappel « installez l'appli » une fois par session, puis
            // lance le scan web demandé. Si Bootstrap n'est pas disponible, on
            // n'empêche jamais l'utilisateur de scanner.
            window.promptMobileAppThenScan = function (scanFn) {
                if (typeof scanFn !== 'function') {
                    return;
                }

                var alreadyShown = false;
                try {
                    alreadyShown = sessionStorage.getItem('mobileAppScanPromptShown') === '1';
                } catch (e) { /* sessionStorage indisponible : on affichera le modal */ }

                var modalEl = document.getElementById('mobileAppScanPrompt');

                if (alreadyShown || !modalEl || typeof bootstrap === 'undefined' || !bootstrap.Modal) {
                    scanFn();
                    return;
                }

                var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                var continueBtn = document.getElementById('continueWebScanBtn');

                var proceed = function () {
                    try { sessionStorage.setItem('mobileAppScanPromptShown', '1'); } catch (e) {}
                    modal.hide();
                    scanFn();
                };

                if (continueBtn) {
                    continueBtn.onclick = proceed;
                }

                modal.show();
            };
        </script>
    @endpush
@endonce
