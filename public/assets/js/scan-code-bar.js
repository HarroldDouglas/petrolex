// Barcode Scanner Module using Html5-Qrcode (replaces Quagga)
if (typeof window.BarcodeScannerModule === "undefined") {
    window.BarcodeScannerModule = (function () {
        let scanner = null;
        let isProcessing = false;
        let lastStopAt = 0;
        let stoppingPromise = null;
        let recentReads = [];
        const CAMERA_RELEASE_DELAY_MS = 400;
        const CONFIRMATION_COUNT = 3; // require N consecutive identical reads

        function log() {
            // No-op: debug logging removed for production.
        }

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
                "color:white;margin-top:10px;font-size:14px;min-height:20px;padding:4px 8px;" +
                "background:rgba(0,0,0,0.5);border-radius:4px;max-width:500px;width:100%;text-align:center;";
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
            log("stopScanner called, scanner present?", !!scanner);
            if (scanner) {
                var localScanner = scanner;
                scanner = null;
                stoppingPromise = (async function () {
                    try { await withTimeout(localScanner.stop(), 1500, "stop"); log("stopScanner: stop resolved"); } catch (e) { log("stopScanner: stop timeout/err:", String(e)); }
                    try { await withTimeout(localScanner.clear(), 1500, "clear"); log("stopScanner: clear resolved"); } catch (e) { log("stopScanner: clear timeout/err:", String(e)); }
                    lastStopAt = Date.now();
                })();
            }
            removeScannerFromDOM();
            return stoppingPromise;
        }

        function removeScannerFromDOM() {
            var container = document.getElementById("scanner-container");
            if (container && document.body.contains(container)) {
                document.body.removeChild(container);
                log("scanner DOM removed");
            }
        }

        function updateStatus(text) {
            var el = document.getElementById("scan-status");
            if (el) el.textContent = text;
        }

        function isValidLinearBarcode(text) {
            // Linear barcodes (EAN, UPC, Code 128, Code 39): alphanumeric +
            // a few separator chars, 4-30 chars long, no path/URL/JSON syntax.
            // Real bottle barcodes look like "12-E01414" or "09-002633".
            // This still blocks QR content that contains "/", ":", "{", "."
            // (URLs, JSON, etc.).
            return /^[A-Z0-9][A-Z0-9\- ]{2,29}$/i.test(text);
        }

        function onScanSuccess(decodedText, decodedResult) {
            if (isProcessing) return;
            if (!decodedText) return;

            // Hard reject anything reported as QR_CODE by the underlying decoder.
            // Reliable source of truth, doesn't depend on content shape.
            try {
                var fmt = decodedResult && decodedResult.result && decodedResult.result.format;
                var fmtName = fmt && (fmt.formatName || fmt.format);
                if (fmtName && String(fmtName).toUpperCase().indexOf("QR") !== -1) {
                    updateStatus("⚠️ QR code ignoré");
                    log("rejected QR format:", fmtName, "text:", decodedText);
                    return;
                }
            } catch (e) {}

            if (!isValidLinearBarcode(decodedText)) {
                updateStatus("⚠️ Format ignoré: " + decodedText);
                log("rejected non-barcode:", decodedText);
                return;
            }

            // Multi-read confirmation: require N consecutive identical decodes
            // before trusting the value. Mitigates frame-to-frame instability
            // on Code 128 reads where wrong values can occasionally pass.
            recentReads.push(decodedText);
            if (recentReads.length > CONFIRMATION_COUNT) {
                recentReads.shift();
            }

            var allMatch = recentReads.length === CONFIRMATION_COUNT &&
                recentReads.every(function (v) { return v === decodedText; });

            if (!allMatch) {
                updateStatus("🔍 Lecture en cours… (" + recentReads.length + "/" + CONFIRMATION_COUNT + ") : " + decodedText);
                log("partial read", recentReads.length, "of", CONFIRMATION_COUNT, ":", decodedText);
                return;
            }

            log("confirmed:", decodedText);
            updateStatus("✅ Code confirmé : " + decodedText);
            recentReads = [];

            isProcessing = true;
            stopScanner();

            try {
                Livewire.dispatch("barcode-scanned", { barcode: decodedText });
            } catch (error) {
                alert("Erreur Livewire: " + error.message);
            } finally {
                setTimeout(function () { isProcessing = false; }, 500);
            }
        }

        function sleep(ms) {
            return new Promise(function (r) { setTimeout(r, ms); });
        }

        function withTimeout(promise, ms, label) {
            return Promise.race([
                promise,
                new Promise(function (_, reject) {
                    setTimeout(function () { reject(new Error(label + " timeout " + ms + "ms")); }, ms);
                }),
            ]);
        }

        async function startScannerInternal() {
            log("init: entering, stoppingPromise?", !!stoppingPromise, "scanner?", !!scanner);
            isProcessing = false;
            recentReads = [];

            // CRITICAL: wait for any pending stop initiated by onScanSuccess
            // to actually release the camera before reopening.
            if (stoppingPromise) {
                log("init: awaiting pending stoppingPromise");
                try { await stoppingPromise; } catch (e) {}
                stoppingPromise = null;
                log("init: stoppingPromise settled");
            }

            if (scanner) {
                var prev = scanner;
                scanner = null;
                try { await withTimeout(prev.stop(), 1500, "stop"); } catch (e) {}
                try { await withTimeout(prev.clear(), 1500, "clear"); } catch (e) {}
                lastStopAt = Date.now();
            }
            removeScannerFromDOM();

            // Wait for camera to be fully released after a previous stop.
            var elapsed = Date.now() - lastStopAt;
            if (lastStopAt && elapsed < CAMERA_RELEASE_DELAY_MS) {
                var wait = CAMERA_RELEASE_DELAY_MS - elapsed;
                log("init: waiting", wait, "ms for camera release");
                await sleep(wait);
            }

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                alert("Caméra non disponible. HTTPS requis.");
                return;
            }

            var ui = createScannerUI();
            document.body.appendChild(ui);
            updateStatus("Caméra en cours d'ouverture...");
            log("init: UI created, building Html5Qrcode");

            scanner = new Html5Qrcode("scanner-viewport");

            var config = {
                fps: 25,
                // No qrbox = decode the FULL viewfinder, matching the Flutter
                // mobile app behavior. Restricting decode to a fixed pixel-size
                // qrbox made small/far-away barcodes (manager's phones) unscannable.
                //
                // ONLY Code 128 + Code 39 — ISOGAZ bottle labels use Code 128
                // ("12-000395" format). Enabling EAN/UPC lets the scanner
                // accidentally decode the QR-adjacent internal EAN-13 (13-digit)
                // sitting next to the real Code 128, which the 3-read
                // confirmation can't catch (a stable misread just repeats).
                // This mirrors the mobile app's hard-won format restriction.
                formatsToSupport: [
                    Html5QrcodeSupportedFormats.CODE_128,
                    Html5QrcodeSupportedFormats.CODE_39,
                ],
                useBarCodeDetectorIfSupported: true,
                // HD video constraints belong here. html5-qrcode rejects
                // the cameraIdOrConfig arg when it has more than one key.
                videoConstraints: {
                    facingMode: { ideal: "environment" },
                    width:  { ideal: 1920 },
                    height: { ideal: 1080 },
                },
            };

            // cameraIdOrConfig must be a string or a single-key object whose
            // facingMode is a plain string ("environment") or { exact: "..." }.
            // {ideal: "environment"} is rejected by html5-qrcode 2.3.8.
            var cameraIdOrConfig = { facingMode: "environment" };

            try {
                await scanner.start(
                    cameraIdOrConfig,
                    config,
                    onScanSuccess,
                    function () {}
                );
                updateStatus("Pointez vers le code-barres...");
                log("init: scanner started successfully");
            } catch (err) {
                log("init: scanner.start failed:", String(err));
                removeScannerFromDOM();
                scanner = null;
                alert("Erreur démarrage caméra: " + err);
            }
        }

        return {
            init: function () {
                startScannerInternal().catch(function (err) {
                    log("init: unexpected rejection:", String(err));
                    try { removeScannerFromDOM(); } catch (e) {}
                    scanner = null;
                    alert("Erreur scanner: " + (err && err.message ? err.message : err));
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
