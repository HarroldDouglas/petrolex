// Barcode Scanner Module using Html5-Qrcode (replaces Quagga)
if (typeof window.BarcodeScannerModule === "undefined") {
    window.BarcodeScannerModule = (function () {
        let scanner = null;
        let isProcessing = false;

        function createScannerUI() {
            var scannerContainer = document.createElement("div");
            scannerContainer.id = "scanner-container";
            scannerContainer.style.cssText =
                "position:fixed;top:0;left:0;width:100%;height:100%;z-index:9999;" +
                "background:rgba(0,0,0,0.9);display:flex;flex-direction:column;" +
                "align-items:center;justify-content:center;";

            var scannerTitle = document.createElement("h3");
            scannerTitle.textContent = "Scanner un code-barres";
            scannerTitle.style.cssText = "color:white;margin-bottom:10px;";
            scannerContainer.appendChild(scannerTitle);

            var scannerViewport = document.createElement("div");
            scannerViewport.id = "scanner-viewport";
            scannerViewport.style.cssText =
                "width:100%;max-width:500px;border-radius:8px;overflow:hidden;";
            scannerContainer.appendChild(scannerViewport);

            var statusIndicator = document.createElement("div");
            statusIndicator.id = "scan-status";
            statusIndicator.style.cssText =
                "color:white;margin-top:10px;font-size:14px;min-height:20px;";
            statusIndicator.textContent = "Pointez vers le code-barres...";
            scannerContainer.appendChild(statusIndicator);

            var closeButton = document.createElement("button");
            closeButton.textContent = "Annuler";
            closeButton.style.cssText =
                "margin-top:15px;padding:10px 30px;border-radius:4px;" +
                "background:#dc3545;color:white;border:none;cursor:pointer;font-size:16px;";
            closeButton.addEventListener("click", function () {
                stopScanner();
            });
            scannerContainer.appendChild(closeButton);

            return scannerContainer;
        }

        function stopScanner() {
            if (scanner) {
                scanner
                    .stop()
                    .then(function () {
                        scanner.clear();
                        scanner = null;
                    })
                    .catch(function () {
                        scanner = null;
                    });
            }
            removeScannerFromDOM();
        }

        function removeScannerFromDOM() {
            var container = document.getElementById("scanner-container");
            if (container && document.body.contains(container)) {
                document.body.removeChild(container);
            }
        }

        function updateStatus(text) {
            var el = document.getElementById("scan-status");
            if (el) el.textContent = text;
        }

        function onScanSuccess(decodedText) {
            if (isProcessing) return;

            // Validate EAN-13: must be exactly 13 digits
            if (!/^\d{13}$/.test(decodedText)) {
                return;
            }

            isProcessing = true;
            updateStatus("Code lu : " + decodedText);

            stopScanner();

            try {
                Livewire.dispatch("barcode-scanned", { barcode: decodedText });
            } catch (error) {
                alert(
                    "Erreur lors du traitement du code-barres: " +
                        error.message,
                );
            } finally {
                setTimeout(function () {
                    isProcessing = false;
                }, 500);
            }
        }

        return {
            init: function () {
                isProcessing = false;

                // Clean up any previous scanner
                if (scanner) {
                    try {
                        scanner.stop();
                        scanner.clear();
                    } catch (e) {
                        // ignore
                    }
                    scanner = null;
                }
                removeScannerFromDOM();

                // Check for camera support
                if (
                    !navigator.mediaDevices ||
                    !navigator.mediaDevices.getUserMedia
                ) {
                    alert(
                        "L'acc\u00e8s \u00e0 la cam\u00e9ra n\u00e9cessite HTTPS. Veuillez utiliser l'URL HTTPS du site.",
                    );
                    return;
                }

                var ui = createScannerUI();
                document.body.appendChild(ui);

                scanner = new Html5Qrcode("scanner-viewport");

                var config = {
                    fps: 15,
                    qrbox: { width: 300, height: 150 },
                    formatsToSupport: [
                        Html5QrcodeSupportedFormats.EAN_13,
                        Html5QrcodeSupportedFormats.EAN_8,
                    ],
                    // Prefer native BarcodeDetector API when available
                    useBarCodeDetectorIfSupported: true,
                };

                scanner
                    .start(
                        { facingMode: "environment" },
                        config,
                        onScanSuccess,
                        function () {
                            // ignore scan failures (no barcode in frame)
                        },
                    )
                    .catch(function (err) {
                        removeScannerFromDOM();
                        scanner = null;

                        if (
                            err.toString().indexOf("NotAllowedError") !== -1
                        ) {
                            alert(
                                "Acc\u00e8s \u00e0 la cam\u00e9ra refus\u00e9. Veuillez autoriser l'acc\u00e8s dans les param\u00e8tres de votre navigateur.",
                            );
                        } else if (
                            err.toString().indexOf("NotFoundError") !== -1
                        ) {
                            alert(
                                "Aucune cam\u00e9ra d\u00e9tect\u00e9e sur cet appareil.",
                            );
                        } else {
                            alert(
                                "Erreur lors de l'initialisation du scanner : " +
                                    err,
                            );
                        }
                    });
            },
        };
    })();

    window.initBarcodeScanner = function () {
        window.BarcodeScannerModule.init();
    };
} else {
    window.initBarcodeScanner = function () {
        window.BarcodeScannerModule.init();
    };
}
