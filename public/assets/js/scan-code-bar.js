// Prevent duplicate declaration by checking if the module already exists
if (typeof window.BarcodeScannerModule === "undefined") {
    window.BarcodeScannerModule = (function () {
        let isProcessing = false;

        // Consensus buffer: collect multiple reads and accept only when stable
        const REQUIRED_CONSENSUS = 3;
        const MAX_BUFFER_SIZE = 8;
        const ERROR_THRESHOLD = 0.08;
        let scanBuffer = [];

        function createScannerUI() {
            const scannerContainer = document.createElement("div");
            scannerContainer.id = "scanner-container";
            scannerContainer.style.cssText =
                "position:fixed;top:0;left:0;width:100%;height:100%;z-index:9999;" +
                "background:rgba(0,0,0,0.85);display:flex;flex-direction:column;" +
                "align-items:center;justify-content:center;";

            const scannerTitle = document.createElement("h3");
            scannerTitle.textContent = "Scanner un code-barres";
            scannerTitle.style.cssText = "color:white;margin-bottom:10px;";
            scannerContainer.appendChild(scannerTitle);

            const scannerViewport = document.createElement("div");
            scannerViewport.id = "scanner-viewport";
            scannerViewport.style.cssText =
                "width:100%;max-width:500px;height:350px;border:2px solid white;" +
                "border-radius:8px;overflow:hidden;position:relative;";
            scannerContainer.appendChild(scannerViewport);

            // Scan status indicator
            const statusIndicator = document.createElement("div");
            statusIndicator.id = "scan-status";
            statusIndicator.style.cssText =
                "color:white;margin-top:10px;font-size:14px;min-height:20px;";
            statusIndicator.textContent = "Pointez vers le code-barres...";
            scannerContainer.appendChild(statusIndicator);

            const closeButton = document.createElement("button");
            closeButton.textContent = "Annuler";
            closeButton.style.cssText =
                "margin-top:15px;padding:8px 20px;border-radius:4px;" +
                "background:#dc3545;color:white;border:none;cursor:pointer;font-size:16px;";
            closeButton.addEventListener("click", function () {
                Quagga.stop();
                removeScannerFromDOM();
            });
            scannerContainer.appendChild(closeButton);

            return { container: scannerContainer, viewport: scannerViewport };
        }

        function removeScannerFromDOM() {
            const container = document.getElementById("scanner-container");
            if (container && document.body.contains(container)) {
                document.body.removeChild(container);
            }
            scanBuffer = [];
        }

        function updateStatus(text) {
            const el = document.getElementById("scan-status");
            if (el) el.textContent = text;
        }

        /**
         * Calculate average error from Quagga's decodedCodes.
         * Lower = more confident.
         */
        function getAverageError(result) {
            const codes = result.codeResult.decodedCodes;
            if (!codes || !codes.length) return 1;

            const errors = codes
                .filter(function (c) {
                    return c.error !== undefined && c.error !== null;
                })
                .map(function (c) {
                    return c.error;
                });

            if (errors.length === 0) return 1;

            const sum = errors.reduce(function (a, b) {
                return a + b;
            }, 0);
            return sum / errors.length;
        }

        /**
         * Check if the buffer has consensus: same barcode read REQUIRED_CONSENSUS times.
         * Returns the barcode string if consensus reached, null otherwise.
         */
        function getConsensusBarcode() {
            var counts = {};
            for (var i = 0; i < scanBuffer.length; i++) {
                var code = scanBuffer[i];
                counts[code] = (counts[code] || 0) + 1;
                if (counts[code] >= REQUIRED_CONSENSUS) {
                    return code;
                }
            }
            return null;
        }

        function handleBarcodeDetection(result) {
            if (isProcessing) return;

            var avgError = getAverageError(result);
            var barcode = result.codeResult.code;

            // Reject low-confidence reads
            if (avgError > ERROR_THRESHOLD) {
                return;
            }

            // Reject invalid EAN-13 (must be 13 digits)
            if (!barcode || !/^\d{13}$/.test(barcode)) {
                return;
            }

            // Add to consensus buffer
            scanBuffer.push(barcode);
            if (scanBuffer.length > MAX_BUFFER_SIZE) {
                scanBuffer.shift();
            }

            // Check for consensus
            var consensusBarcode = getConsensusBarcode();

            if (!consensusBarcode) {
                updateStatus(
                    "Lecture en cours... (" +
                        scanBuffer.length +
                        "/" +
                        REQUIRED_CONSENSUS +
                        ")",
                );
                return;
            }

            // Consensus reached - accept the barcode
            isProcessing = true;
            updateStatus("Code lu : " + consensusBarcode);

            try {
                Quagga.stop();
            } catch (e) {
                // ignore
            }

            removeScannerFromDOM();

            try {
                Livewire.dispatch("barcode-scanned", [
                    { barcode: consensusBarcode },
                ]);
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

        function initializeQuagga(scannerViewport) {
            // Camera requires a secure context (HTTPS, localhost, or 127.0.0.1)
            if (
                !navigator.mediaDevices ||
                !navigator.mediaDevices.getUserMedia
            ) {
                removeScannerFromDOM();
                alert(
                    "L'acc\u00e8s \u00e0 la cam\u00e9ra n\u00e9cessite HTTPS. Veuillez utiliser l'URL HTTPS du site.",
                );
                return;
            }

            var quaggaConfig = {
                inputStream: {
                    name: "Live",
                    type: "LiveStream",
                    target: scannerViewport,
                    constraints: {
                        width: { ideal: 1280 },
                        height: { ideal: 720 },
                        facingMode: "environment",
                    },
                },
                decoder: {
                    readers: ["ean_reader", "ean_8_reader"],
                },
                locate: true,
                frequency: 15,
            };

            Quagga.init(quaggaConfig, function (err) {
                if (err) {
                    removeScannerFromDOM();

                    if (err.name === "NotAllowedError") {
                        alert(
                            "Acc\u00e8s \u00e0 la cam\u00e9ra refus\u00e9. Veuillez autoriser l'acc\u00e8s dans les param\u00e8tres de votre navigateur.",
                        );
                    } else if (
                        err.name === "NotFoundError" ||
                        err.name === "DevicesNotFoundError"
                    ) {
                        alert("Aucune cam\u00e9ra d\u00e9tect\u00e9e sur cet appareil.");
                    } else {
                        alert(
                            "Erreur lors de l'initialisation du scanner : " +
                                err.message,
                        );
                    }
                    return;
                }
                Quagga.start();
            });

            Quagga.onDetected(handleBarcodeDetection);
        }

        return {
            init: function () {
                scanBuffer = [];
                isProcessing = false;
                var ui = createScannerUI();
                document.body.appendChild(ui.container);
                initializeQuagga(ui.viewport);
            },
        };
    })();

    // Define the global function that's called from the blade file
    window.initBarcodeScanner = function () {
        window.BarcodeScannerModule.init();
    };
} else {
    // Module already exists, just reset state for next scan
    window.initBarcodeScanner = function () {
        window.BarcodeScannerModule.init();
    };
}
