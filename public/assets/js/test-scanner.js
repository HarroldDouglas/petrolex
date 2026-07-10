// Test scanner page — vanilla JS, no Livewire.
// 3 scan modes share a single overlay & camera lifecycle.
//
// Mode "native":  Live decode, useBarCodeDetectorIfSupported = true,  multi-read 3/3
// Mode "jspure":  Live decode, useBarCodeDetectorIfSupported = false, multi-read 3/3
// Mode "capture": Live preview, decode only on tap (single shot per tap)
(function () {
    "use strict";

    // Session id so we can correlate one tester's events in the jslog channel.
    var SESSION_ID = "ts-" + Math.random().toString(36).slice(2, 8) + "-" + Date.now();
    var LOG_PREFIX = "[TestScanner " + SESSION_ID + "]";

    function tlog() {
        try {
            var args = Array.prototype.slice.call(arguments);
            args.unshift(LOG_PREFIX);
            console.log.apply(console, args);
        } catch (e) {}
    }
    function terr() {
        try {
            var args = Array.prototype.slice.call(arguments);
            args.unshift(LOG_PREFIX);
            console.error.apply(console, args);
        } catch (e) {}
    }

    // Boot-time environment dump — sent on first flush so I can see what device
    // the manager is on the moment they open the page.
    (function bootDiagnostics() {
        try {
            tlog("BOOT", JSON.stringify({
                ua: navigator.userAgent,
                platform: navigator.platform || "?",
                lang: navigator.language,
                viewport: window.innerWidth + "x" + window.innerHeight,
                dpr: window.devicePixelRatio || 1,
                hasMediaDevices: !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia),
                hasBarcodeDetector: typeof window.BarcodeDetector !== "undefined",
                hasHtml5Qrcode: typeof window.Html5Qrcode !== "undefined",
                isSecureContext: window.isSecureContext,
                protocol: location.protocol,
            }));
        } catch (e) {
            terr("BOOT failed", String(e));
        }

        // Enumerate cameras (full labels need a prior getUserMedia permission).
        // Populate the picker as soon as labels are available.
        populateCameraPicker();

        // Try BarcodeDetector formats
        try {
            if (typeof window.BarcodeDetector !== "undefined" && BarcodeDetector.getSupportedFormats) {
                BarcodeDetector.getSupportedFormats().then(function (formats) {
                    tlog("BD_FORMATS", JSON.stringify(formats));
                });
            }
        } catch (e) { terr("BarcodeDetector.getSupportedFormats throw", String(e)); }
    })();

    var scanner = null;
    var currentMode = null;
    var stoppingPromise = null;
    var lastStopAt = 0;
    var recentReads = [];
    var captureInFlight = false;
    var isProcessing = false;
    var CAMERA_RELEASE_DELAY_MS = 400;
    var CONFIRMATION_COUNT = 3;

    // Tap-to-capture: on tap, take CAPTURE_BURST_COUNT frames over ~400ms,
    // pick the sharpest, decode it. Frame selection is automatic — the user
    // only triggers the burst, no human judgment on image quality.
    var CAPTURE_BURST_COUNT = 5;
    var CAPTURE_BURST_INTERVAL_MS = 80;
    // Laplacian variance threshold below which the burst is rejected as blurry.
    // Tuned to be lenient — main goal is to refuse OBVIOUSLY blurry frames.
    var BLUR_REJECT_THRESHOLD = 25;

    // Selected camera deviceId (from picker). Empty = use facingMode auto.
    var selectedCameraId = "";
    try { selectedCameraId = localStorage.getItem("petrolex-scanner-camera") || ""; } catch (e) {}

    // Étape 6 handler: send the photo taken by the native camera app to the
    // server-side zbar decoder. This bypasses all browser camera limitations
    // (lens selection, focus distance) since the OS camera app is used.
    function setNativeZbarStatus(text) {
        var el = document.getElementById("native-zbar-status");
        if (el) el.textContent = text;
    }

    async function handleNativeCameraFile(file) {
        setNativeZbarStatus("📤 Envoi au serveur…");
        tlog("NATIVE_ZBAR_SUBMIT", JSON.stringify({
            size: file.size,
            type: file.type,
            name: file.name,
        }));
        try {
            var fd = new FormData();
            fd.append("image", file);
            fd.append("session_id", SESSION_ID);
            fd.append("url", location.href);

            var res = await fetch("/api/decode-barcode", {
                method: "POST",
                headers: { "X-Requested-With": "XMLHttpRequest" },
                body: fd,
            });
            var data = await res.json();
            tlog("NATIVE_ZBAR_RESP", JSON.stringify(data));

            if (data.ok && Array.isArray(data.values) && data.values.length) {
                // Find first valid linear barcode (skip any QR-like content).
                var picked = null;
                for (var i = 0; i < data.values.length; i++) {
                    var v = data.values[i];
                    if (isValidLinearBarcode(v)) { picked = v; break; }
                }
                if (picked) {
                    setNativeZbarStatus("✅ Décodé : " + picked);
                    recordRead("native-zbar", picked);
                    return;
                }
                setNativeZbarStatus("⚠️ Valeurs trouvées mais format non valide: " + data.values.join(", "));
                return;
            }
            setNativeZbarStatus("❌ Aucun code détecté par zbar — image archivée pour analyse");
        } catch (e) {
            terr("NATIVE_ZBAR_THROW", String(e));
            setNativeZbarStatus("❌ Erreur réseau: " + (e && e.message ? e.message : e));
        }
    }

    // Upload a snapshot of the canvas to the server for diagnostic.
    // Downscales to 1024px max, JPEG quality 70%. Fire-and-forget.
    function uploadSnapshot(canvas, label) {
        try {
            var w0 = canvas.width, h0 = canvas.height;
            var maxDim = 1024;
            var scale = Math.min(1, maxDim / Math.max(w0, h0));
            var w = Math.round(w0 * scale), h = Math.round(h0 * scale);
            var dst = document.createElement("canvas");
            dst.width = w; dst.height = h;
            var ctx = dst.getContext("2d");
            ctx.imageSmoothingEnabled = true;
            ctx.imageSmoothingQuality = "medium";
            ctx.drawImage(canvas, 0, 0, w, h);
            var dataUrl = dst.toDataURL("image/jpeg", 0.7);
            fetch("/api/scanner-snapshot", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: JSON.stringify({
                    session_id: SESSION_ID,
                    label: String(label || "snap").replace(/[^A-Za-z0-9_\-]/g, ""),
                    image: dataUrl,
                    url: location.href,
                }),
            }).then(function () { tlog("SNAPSHOT_UPLOADED", label); })
              .catch(function (e) { terr("SNAPSHOT_UPLOAD_FAIL", String(e)); });
        } catch (e) { terr("SNAPSHOT_THROW", String(e)); }
    }

    // ZXing fallback decoder — much more permissive on Code 128 than the
    // native BarcodeDetector. Returns array of {format, rawValue} or [].
    var zxingReader = null;
    function getZxingReader() {
        if (zxingReader) return zxingReader;
        if (typeof ZXing === "undefined" || !ZXing.BrowserMultiFormatReader) return null;
        try {
            var hints = new Map();
            // ZXing.DecodeHintType.TRY_HARDER = 3
            hints.set(3, true);
            // ZXing.DecodeHintType.POSSIBLE_FORMATS = 2
            hints.set(2, [
                ZXing.BarcodeFormat.CODE_128,
                ZXing.BarcodeFormat.CODE_39,
                ZXing.BarcodeFormat.EAN_13,
                ZXing.BarcodeFormat.EAN_8,
                ZXing.BarcodeFormat.UPC_A,
                ZXing.BarcodeFormat.UPC_E,
                ZXing.BarcodeFormat.ITF,
            ]);
            zxingReader = new ZXing.BrowserMultiFormatReader(hints);
            return zxingReader;
        } catch (e) {
            terr("ZXING_INIT_FAIL", String(e));
            return null;
        }
    }

    async function decodeWithZxing(canvas) {
        var reader = getZxingReader();
        if (!reader) return null;
        try {
            // BrowserMultiFormatReader can decode from a canvas via decodeFromCanvas
            // (sync). Wrap in promise for consistency.
            var result = reader.decodeFromCanvas(canvas);
            if (result && result.getText) {
                return { format: result.getBarcodeFormat ? String(result.getBarcodeFormat()) : "?", rawValue: result.getText() };
            }
            return null;
        } catch (e) {
            // ZXing throws NotFoundException when nothing found — normal, not an error.
            return null;
        }
    }

    async function detectMultiScaleZxing(canvas) {
        var scales = [1.0, 0.75, 0.5, 0.33];
        var attempts = [];
        for (var i = 0; i < scales.length; i++) {
            var s = scales[i];
            var input = s === 1.0 ? canvas : cropCenterAndUpscale(canvas, s);
            var r = await decodeWithZxing(input);
            attempts.push({ scale: s, hit: r ? 1 : 0 });
            if (r) return { result: r, scale: s, attempts: attempts };
        }
        return { result: null, scale: null, attempts: attempts };
    }

    // Crop the center of `srcCanvas` to `scaleFactor` of its size, then
    // upscale back to original dimensions. This gives the decoder MORE pixels
    // per barcode bar — essential when the barcode is small in the frame.
    // Nearest-neighbor preserves edge sharpness (decoder-friendly).
    function cropCenterAndUpscale(srcCanvas, scaleFactor) {
        var w0 = srcCanvas.width, h0 = srcCanvas.height;
        var cw = Math.max(1, Math.round(w0 * scaleFactor));
        var ch = Math.max(1, Math.round(h0 * scaleFactor));
        var cx = Math.round((w0 - cw) / 2);
        var cy = Math.round((h0 - ch) / 2);
        var dst = document.createElement("canvas");
        dst.width = w0;
        dst.height = h0;
        var ctx = dst.getContext("2d");
        ctx.imageSmoothingEnabled = false;
        ctx.drawImage(srcCanvas, cx, cy, cw, ch, 0, 0, w0, h0);
        return dst;
    }

    // Try decoding the canvas at multiple "zoom" scales — first the full
    // frame, then progressively tighter center crops upscaled back to full
    // resolution. Returns the first successful detection with metadata about
    // which scale worked.
    async function detectMultiScale(detector, canvas) {
        var scales = [1.0, 0.75, 0.5, 0.33];
        var attempts = [];
        for (var i = 0; i < scales.length; i++) {
            var s = scales[i];
            var input = s === 1.0 ? canvas : cropCenterAndUpscale(canvas, s);
            var results;
            try {
                results = await detector.detect(input);
            } catch (e) {
                attempts.push({ scale: s, error: String(e) });
                continue;
            }
            attempts.push({ scale: s, count: results.length });
            if (results.length) {
                return { results: results, scale: s, attempts: attempts };
            }
        }
        return { results: [], scale: null, attempts: attempts };
    }

    // Compute Laplacian variance on a downsampled grayscale of the canvas.
    // Higher value = sharper image. ~50k pixels processed = fast on mobile.
    function laplacianVariance(canvas) {
        var w0 = canvas.width, h0 = canvas.height;
        // Downsample to ~320 wide for speed; preserves blur characteristics.
        var w = 320;
        var h = Math.round(h0 * w / w0);
        var small = document.createElement("canvas");
        small.width = w; small.height = h;
        var sctx = small.getContext("2d");
        sctx.drawImage(canvas, 0, 0, w, h);
        var data = sctx.getImageData(0, 0, w, h).data;

        // Grayscale (luminance)
        var gray = new Float32Array(w * h);
        for (var i = 0, j = 0; i < gray.length; i++, j += 4) {
            gray[i] = data[j] * 0.299 + data[j + 1] * 0.587 + data[j + 2] * 0.114;
        }

        // 4-neighbor Laplacian, skip 1px border
        var sum = 0, sumSq = 0, n = 0;
        for (var y = 1; y < h - 1; y++) {
            for (var x = 1; x < w - 1; x++) {
                var idx = y * w + x;
                var l = gray[idx - 1] + gray[idx + 1] + gray[idx - w] + gray[idx + w] - 4 * gray[idx];
                sum += l;
                sumSq += l * l;
                n++;
            }
        }
        var mean = sum / n;
        return (sumSq / n) - (mean * mean);
    }

    var MODE_LABELS = {
        baseline:    "Étape 1 — Reproduction du bug",
        native:      "Étape 2 — Live HD + API native",
        jspure:      "Étape 3 — Live HD JS pur",
        capture:     "Étape 4 — Tap-to-capture",
        fullframe:   "Étape 5 — Plein écran (style mobile)",
        "native-zbar": "Étape 6 — Caméra système + zbar serveur",
    };

    // Per-mode state (counters + history)
    var state = {
        baseline:    { count: 0, history: [] },
        native:      { count: 0, history: [] },
        jspure:      { count: 0, history: [] },
        capture:     { count: 0, history: [] },
        fullframe:   { count: 0, history: [] },
        "native-zbar": { count: 0, history: [] },
    };

    function $(sel, root) { return (root || document).querySelector(sel); }
    function $all(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }

    function withTimeout(promise, ms, label) {
        return Promise.race([
            promise,
            new Promise(function (_, reject) {
                setTimeout(function () { reject(new Error(label + " timeout " + ms + "ms")); }, ms);
            }),
        ]);
    }

    function sleep(ms) { return new Promise(function (r) { setTimeout(r, ms); }); }

    function isValidLinearBarcode(text) {
        return /^[A-Z0-9][A-Z0-9\- ]{2,29}$/i.test(text);
    }

    // After html5-qrcode opens the camera, query the underlying video track
    // for advanced capabilities (zoom, focusMode, torch, ...) and apply the
    // optimal constraints. This is the ONLY way to compensate for Chrome's
    // refusal to let us pick a specific lens — we apply a 2x zoom which makes
    // an ultra-wide lens behave roughly like the main 1x lens.
    var currentVideoTrack = null;
    var currentZoomCaps = null;

    async function applyOptimalCameraConstraints() {
        var video = $("#scanner-viewport video");
        if (!video || !video.srcObject) return;
        var stream = video.srcObject;
        var tracks = stream.getVideoTracks ? stream.getVideoTracks() : [];
        var track = tracks[0];
        if (!track) return;
        currentVideoTrack = track;

        var caps = null;
        try { caps = track.getCapabilities ? track.getCapabilities() : null; } catch (e) {}
        var settings = null;
        try { settings = track.getSettings ? track.getSettings() : null; } catch (e) {}

        tlog("CAMERA_CAPS", JSON.stringify({
            zoom: caps && caps.zoom,
            focusMode: caps && caps.focusMode,
            focusDistance: caps && caps.focusDistance,
            torch: caps && caps.torch,
            whiteBalanceMode: caps && caps.whiteBalanceMode,
            settingsWxH: settings ? (settings.width + "x" + settings.height) : null,
            settingsFacingMode: settings && settings.facingMode,
        }));

        var advanced = [];
        if (caps && Array.isArray(caps.focusMode) && caps.focusMode.indexOf("continuous") !== -1) {
            advanced.push({ focusMode: "continuous" });
        }
        if (caps && caps.zoom && typeof caps.zoom.min === "number" && typeof caps.zoom.max === "number") {
            currentZoomCaps = caps.zoom;
            // Default to 2x or max-supported (whichever smaller). User can adjust via slider.
            var target = Math.min(2, caps.zoom.max);
            if (target > caps.zoom.min) {
                advanced.push({ zoom: target });
                wireZoomSlider(caps.zoom, target);
            } else {
                hideZoomSlider();
            }
        } else {
            currentZoomCaps = null;
            hideZoomSlider();
        }

        if (advanced.length) {
            try {
                await track.applyConstraints({ advanced: advanced });
                tlog("CAMERA_CONSTRAINTS_APPLIED", JSON.stringify(advanced));
            } catch (e) {
                tlog("CAMERA_CONSTRAINTS_FAIL", String(e));
            }
        }
    }

    function wireZoomSlider(zoomCaps, initial) {
        var wrap = $("#zoom-slider-wrap");
        var slider = $("#zoom-slider");
        var label = $("#zoom-slider-label");
        if (!wrap || !slider) return;
        wrap.style.display = "flex";
        slider.min = String(zoomCaps.min);
        slider.max = String(zoomCaps.max);
        slider.step = String(zoomCaps.step || 0.1);
        slider.value = String(initial);
        if (label) label.textContent = "Zoom: " + initial.toFixed(1) + "×";
    }

    function hideZoomSlider() {
        var wrap = $("#zoom-slider-wrap");
        if (wrap) wrap.style.display = "none";
    }

    async function setZoom(value) {
        if (!currentVideoTrack || !currentZoomCaps) return;
        try {
            await currentVideoTrack.applyConstraints({ advanced: [{ zoom: value }] });
            var label = $("#zoom-slider-label");
            if (label) label.textContent = "Zoom: " + value.toFixed(1) + "×";
            tlog("ZOOM_SET", value);
        } catch (e) { tlog("ZOOM_SET_FAIL", String(e)); }
    }

    function populateCameraPicker() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices) return;
        navigator.mediaDevices.enumerateDevices().then(function (devices) {
            var videos = devices.filter(function (d) { return d.kind === "videoinput"; });
            tlog("CAMERAS", JSON.stringify(videos.map(function (d) {
                return { id: d.deviceId ? d.deviceId.slice(0, 8) : "no", label: d.label || "(no label)" };
            })));
            var picker = document.getElementById("camera-picker");
            if (!picker) return;
            // Reset (keep "Auto" option in [0])
            while (picker.options.length > 1) picker.remove(1);
            videos.forEach(function (d) {
                var label = d.label || "(caméra sans nom — autorisez la caméra)";
                // Highlight back-facing cameras in the label
                if (label.toLowerCase().indexOf("back") !== -1) label = "⬅️ " + label;
                else if (label.toLowerCase().indexOf("front") !== -1) label = "🤳 " + label;
                var opt = document.createElement("option");
                opt.value = d.deviceId;
                opt.textContent = label;
                if (d.deviceId === selectedCameraId) opt.selected = true;
                picker.appendChild(opt);
            });
        }).catch(function (e) { terr("enumerateDevices failed", String(e)); });
    }

    function setStatus(text) {
        var el = $("#scanner-status");
        if (el) el.textContent = text;
    }

    function renderCard(mode) {
        var card = $('.card[data-mode="' + mode + '"]');
        if (!card) return;

        var s = state[mode];
        var countEl  = $('[data-stat="count"]', card);
        var uniqueEl = $('[data-stat="unique"]', card);
        var logEl    = $('[data-log]', card);

        countEl.textContent = s.count;

        var unique = {};
        s.history.forEach(function (h) { unique[h.value] = true; });
        uniqueEl.textContent = Object.keys(unique).length;

        if (!s.history.length) {
            logEl.innerHTML = '<div class="empty">Aucune lecture pour le moment.</div>';
            return;
        }

        // Show most recent first. Mark each with ok/bad relative to the FIRST read.
        var first = s.history[0].value;
        var rows = s.history.slice().reverse().map(function (h, i) {
            var match = h.value === first;
            var cls = match ? "ok" : "bad";
            var mark = match ? "✓" : "✗";
            return '<div class="log-row">' +
                   '  <span class="v">' + escapeHtml(h.value) + '</span>' +
                   '  <span class="' + cls + '">' + mark + ' #' + (s.history.length - i) + '</span>' +
                   '</div>';
        }).join("");

        logEl.innerHTML = rows;
        logEl.scrollTop = 0;
    }

    function escapeHtml(s) {
        return String(s)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;");
    }

    function recordRead(mode, value) {
        state[mode].count += 1;
        state[mode].history.push({ value: value, t: Date.now() });
        // Keep last 50
        if (state[mode].history.length > 50) {
            state[mode].history = state[mode].history.slice(-50);
        }
        renderCard(mode);
        tlog("READ", JSON.stringify({ mode: mode, value: value, total: state[mode].count }));
    }

    function resetMode(mode) {
        state[mode] = { count: 0, history: [] };
        renderCard(mode);
    }

    function removeOverlay() {
        var overlay = $("#scanner-overlay");
        overlay.classList.remove("on");
    }

    function showOverlay(mode) {
        $("#scanner-mode-label").textContent = MODE_LABELS[mode] || mode;
        $("#scanner-overlay").classList.add("on");
        $("#scanner-capture-btn").style.display = mode === "capture" ? "" : "none";
        setStatus("Caméra…");
    }

    function stopScanner() {
        stopFullframeLoop();
        if (scanner) {
            var local = scanner;
            scanner = null;
            stoppingPromise = (async function () {
                try { await withTimeout(local.stop(),  1500, "stop");  } catch (e) {}
                try { await withTimeout(local.clear(), 1500, "clear"); } catch (e) {}
                lastStopAt = Date.now();
            })();
        }
        removeOverlay();
        return stoppingPromise;
    }

    // Generic onScanSuccess for live modes (native/jspure)
    function onScanSuccessLive(decodedText, decodedResult) {
        if (isProcessing) return;
        if (!decodedText) return;

        // Reject QR formats
        try {
            var fmt = decodedResult && decodedResult.result && decodedResult.result.format;
            var fmtName = fmt && (fmt.formatName || fmt.format);
            if (fmtName && String(fmtName).toUpperCase().indexOf("QR") !== -1) {
                setStatus("⚠️ QR ignoré");
                return;
            }
        } catch (e) {}

        if (!isValidLinearBarcode(decodedText)) {
            setStatus("⚠️ Format ignoré: " + decodedText);
            return;
        }

        // Multi-read confirmation 3/3
        recentReads.push(decodedText);
        if (recentReads.length > CONFIRMATION_COUNT) recentReads.shift();
        var allMatch = recentReads.length === CONFIRMATION_COUNT &&
            recentReads.every(function (v) { return v === decodedText; });
        if (!allMatch) {
            setStatus("🔍 Lecture en cours… (" + recentReads.length + "/" + CONFIRMATION_COUNT + ") : " + decodedText);
            return;
        }

        setStatus("✅ Confirmé : " + decodedText);
        recentReads = [];
        isProcessing = true;

        recordRead(currentMode, decodedText);

        // Auto-stop so user can re-start a fresh scan attempt
        stopScanner();
        setTimeout(function () { isProcessing = false; }, 500);
    }

    // Fullframe live loop (Étape 5):
    //   - Independent JS loop, ignores html5-qrcode's internal decode results.
    //   - Every FULLFRAME_LOOP_INTERVAL_MS: grab frame, sharpness check, decode.
    //   - Tries BarcodeDetector first (fast), then ZXing fallback.
    //   - Dedups identical reads within DEDUP_WINDOW_MS.
    var FULLFRAME_LOOP_INTERVAL_MS = 400;
    var FULLFRAME_DEDUP_MS = 1500;
    var fullframeLoopHandle = null;
    var fullframeBusy = false;
    var fullframeLastValue = "";
    var fullframeLastAt = 0;
    // Snapshot upload pacing: upload sharp-but-failed frames every N seconds
    // (not just once per session) so we can see what the camera saw during
    // ACTUAL bottle scan attempts, not just the first frame after start.
    var FAILED_SNAPSHOT_INTERVAL_MS = 2000;
    var FAILED_SNAPSHOT_MAX_PER_SESSION = 10;
    var failedSnapshotLastAt = 0;
    var failedSnapshotCount = 0;
    // Diagnostic tick counter
    var tickCounter = 0;

    function startFullframeLoop() {
        if (fullframeLoopHandle) return;
        fullframeLastValue = "";
        fullframeLastAt = 0;
        failedSnapshotLastAt = 0;
        failedSnapshotCount = 0;
        tickCounter = 0;
        fullframeLoopHandle = setInterval(fullframeTick, FULLFRAME_LOOP_INTERVAL_MS);
        tlog("FULLFRAME_LOOP_START");
    }

    function stopFullframeLoop() {
        if (fullframeLoopHandle) {
            clearInterval(fullframeLoopHandle);
            fullframeLoopHandle = null;
            tlog("FULLFRAME_LOOP_STOP");
        }
    }

    async function fullframeTick() {
        if (fullframeBusy) return;
        if (currentMode !== "fullframe") return;
        var video = $("#scanner-viewport video");
        if (!video || !video.videoWidth) return;

        fullframeBusy = true;
        tickCounter++;
        try {
            var w = video.videoWidth, h = video.videoHeight;
            var canvas = document.createElement("canvas");
            canvas.width = w; canvas.height = h;
            canvas.getContext("2d").drawImage(video, 0, 0, w, h);
            var blur = laplacianVariance(canvas);

            if (blur < BLUR_REJECT_THRESHOLD) {
                setStatus("🔍 Recherche d'une image nette… (" + Math.round(blur) + ")");
                return;
            }

            // Try BD multi-scale
            var detector = null;
            if (typeof window.BarcodeDetector !== "undefined") {
                try {
                    detector = new BarcodeDetector({
                        formats: ["code_128", "code_39", "ean_13", "ean_8", "upc_a", "upc_e"],
                    });
                } catch (e) {
                    tlog("FF_BD_INIT_FAIL", String(e));
                }
            }

            var foundValue = null;
            var foundVia = "";
            var foundScale = null;
            var bdAttempts = [];
            var bdRawResults = [];

            if (detector) {
                var ms = await detectMultiScale(detector, canvas);
                bdAttempts = ms.attempts;
                bdRawResults = ms.results.map(function (r) {
                    return { format: r.format, value: r.rawValue };
                });
                if (ms.results.length) {
                    for (var i = 0; i < ms.results.length; i++) {
                        var r = ms.results[i];
                        if (r.format && String(r.format).toUpperCase().indexOf("QR") !== -1) continue;
                        if (isValidLinearBarcode(r.rawValue)) {
                            foundValue = r.rawValue;
                            foundVia = "BD";
                            foundScale = ms.scale;
                            break;
                        }
                    }
                }
            }

            var zxingAttempts = [];
            var zxingRaw = null;
            if (!foundValue) {
                var zx = await detectMultiScaleZxing(canvas);
                zxingAttempts = zx.attempts;
                zxingRaw = zx.result;
                if (zx.result && isValidLinearBarcode(zx.result.rawValue)) {
                    foundValue = zx.result.rawValue;
                    foundVia = "ZXING";
                    foundScale = zx.scale;
                }
            }

            if (!foundValue) {
                setStatus("👁️ Image nette (" + Math.round(blur) + ") — aucun code lu");
                // Every Nth failure on a sharp frame: log a diagnostic + upload.
                // Bounded so we don't flood storage.
                var nowF = Date.now();
                if (failedSnapshotCount < FAILED_SNAPSHOT_MAX_PER_SESSION &&
                    (nowF - failedSnapshotLastAt) > FAILED_SNAPSHOT_INTERVAL_MS) {
                    failedSnapshotLastAt = nowF;
                    failedSnapshotCount++;
                    tlog("FULLFRAME_MISS", JSON.stringify({
                        tick: tickCounter,
                        blur: Math.round(blur),
                        videoSize: w + "x" + h,
                        bdAttempts: bdAttempts,
                        bdResults: bdRawResults,
                        zxingAttempts: zxingAttempts,
                        zxingRaw: zxingRaw,
                        zxingAvailable: typeof ZXing !== "undefined",
                    }));
                    uploadSnapshot(canvas, "ff_miss_" + failedSnapshotCount + "_blur" + Math.round(blur));
                }
                return;
            }

            var now = Date.now();
            if (foundValue === fullframeLastValue && (now - fullframeLastAt) < FULLFRAME_DEDUP_MS) {
                setStatus("✅ " + foundVia + " " + Math.round(foundScale * 100) + "% : " + foundValue + " (déjà)");
                return;
            }
            fullframeLastValue = foundValue;
            fullframeLastAt = now;
            setStatus("✅ " + foundVia + " (" + Math.round(foundScale * 100) + "%) : " + foundValue);
            recordRead("fullframe", foundValue);
            tlog("FULLFRAME_HIT", JSON.stringify({ via: foundVia, scale: foundScale, value: foundValue, blur: Math.round(blur) }));
        } catch (e) {
            terr("FULLFRAME_TICK_THROW", String(e));
        } finally {
            fullframeBusy = false;
        }
    }

    // Tap-to-capture (Étape 4):
    //   1. On tap, grab CAPTURE_BURST_COUNT frames spaced by CAPTURE_BURST_INTERVAL_MS.
    //   2. Score each with Laplacian variance, pick the sharpest.
    //   3. If the sharpest is still blurry → auto-reject, no decode attempt
    //      (system decides, no human judgment).
    //   4. Decode the sharpest frame with native BarcodeDetector to capture
    //      raw per-format results for diagnostics.
    //   5. Validate format + content via existing filters; record if valid.
    async function captureOnce() {
        if (captureInFlight) return;
        captureInFlight = true;
        setStatus("📸 Capture en rafale…");

        try {
            var video = $("#scanner-viewport video");
            if (!video || !video.videoWidth) {
                setStatus("⚠️ Vidéo non prête");
                return;
            }
            var w = video.videoWidth, h = video.videoHeight;

            var frames = [];
            for (var i = 0; i < CAPTURE_BURST_COUNT; i++) {
                var canvas = document.createElement("canvas");
                canvas.width = w; canvas.height = h;
                canvas.getContext("2d").drawImage(video, 0, 0, w, h);
                frames.push({ canvas: canvas, blur: laplacianVariance(canvas) });
                if (i < CAPTURE_BURST_COUNT - 1) await sleep(CAPTURE_BURST_INTERVAL_MS);
            }
            frames.sort(function (a, b) { return b.blur - a.blur; });
            var best = frames[0];
            var scoresRounded = frames.map(function (f) { return Math.round(f.blur); });

            tlog("CAPTURE_BURST", JSON.stringify({
                videoSize: w + "x" + h,
                scores: scoresRounded,
                bestBlur: Math.round(best.blur),
                threshold: BLUR_REJECT_THRESHOLD,
                cameraId: selectedCameraId ? selectedCameraId.slice(0, 8) : "auto",
            }));

            if (best.blur < BLUR_REJECT_THRESHOLD) {
                setStatus("🔍 Image floue (score=" + Math.round(best.blur) + ", min=" + BLUR_REJECT_THRESHOLD + "). Restez stable, recadrez, réessayez.");
                return;
            }

            // Decode the sharpest frame with the native BarcodeDetector.
            if (typeof window.BarcodeDetector === "undefined") {
                setStatus("⚠️ BarcodeDetector indisponible sur ce navigateur");
                return;
            }
            var detector;
            try {
                detector = new BarcodeDetector({
                    formats: ["code_128", "code_39", "ean_13", "ean_8", "upc_a", "upc_e"],
                });
            } catch (e) {
                tlog("BD_CONSTRUCT_FAIL", String(e));
                setStatus("⚠️ Décodeur indisponible: " + String(e));
                return;
            }

            // Stage 1: multi-scale BarcodeDetector.
            var ms = await detectMultiScale(detector, best.canvas);
            var results = ms.results;
            tlog("CAPTURE_RAW_BD", JSON.stringify({
                attempts: ms.attempts,
                winningScale: ms.scale,
                resultCount: results.length,
                results: results.map(function (r) { return { format: r.format, value: r.rawValue }; }),
            }));

            // Stage 2: if BarcodeDetector failed OR only returned a QR (no
            // valid linear barcode), fall back to ZXing multi-scale. ZXing is
            // notably more permissive on Code 128 than the native API.
            var hasValidFromBD = false;
            if (results.length) {
                for (var i = 0; i < results.length; i++) {
                    var r = results[i];
                    if (r.format && String(r.format).toUpperCase().indexOf("QR") !== -1) continue;
                    if (isValidLinearBarcode(r.rawValue)) { hasValidFromBD = true; break; }
                }
            }

            if (!hasValidFromBD) {
                setStatus("🔁 Décodeur natif KO — essai ZXing…");
                var zx = await detectMultiScaleZxing(best.canvas);
                tlog("CAPTURE_RAW_ZXING", JSON.stringify({
                    attempts: zx.attempts,
                    winningScale: zx.scale,
                    result: zx.result,
                }));
                if (zx.result && isValidLinearBarcode(zx.result.rawValue)) {
                    setStatus("✅ ZXing (" + Math.round(zx.scale * 100) + "%) : " + zx.result.rawValue);
                    recordRead("capture", zx.result.rawValue);
                    return;
                }
                // Both decoders failed on a sharp image — upload for inspection.
                uploadSnapshot(best.canvas, "capture_fail_blur" + Math.round(best.blur));
                setStatus("❌ BD + ZXing échouent. Image envoyée pour analyse.");
                return;
            }
            // BD found a valid linear code — record the first valid one.
            for (var bi = 0; bi < results.length; bi++) {
                var br = results[bi];
                if (br.format && String(br.format).toUpperCase().indexOf("QR") !== -1) continue;
                if (!isValidLinearBarcode(br.rawValue)) continue;
                setStatus("✅ Natif (" + Math.round(ms.scale * 100) + "%) : " + br.rawValue);
                recordRead("capture", br.rawValue);
                return;
            }
        } catch (e) {
            terr("CAPTURE_THROW", String(e));
            setStatus("❌ Erreur capture: " + (e && e.message ? e.message : e));
        } finally {
            captureInFlight = false;
        }
    }

    async function startScanner(mode) {
        currentMode = mode;
        isProcessing = false;
        recentReads = [];

        // Wait for any pending stop
        if (stoppingPromise) {
            try { await stoppingPromise; } catch (e) {}
            stoppingPromise = null;
        }
        if (scanner) {
            var prev = scanner;
            scanner = null;
            try { await withTimeout(prev.stop(),  1500, "stop");  } catch (e) {}
            try { await withTimeout(prev.clear(), 1500, "clear"); } catch (e) {}
            lastStopAt = Date.now();
        }

        var elapsed = Date.now() - lastStopAt;
        if (lastStopAt && elapsed < CAMERA_RELEASE_DELAY_MS) {
            await sleep(CAMERA_RELEASE_DELAY_MS - elapsed);
        }

        showOverlay(mode);

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            alert("Caméra non disponible. HTTPS requis.");
            removeOverlay();
            return;
        }

        scanner = new Html5Qrcode("scanner-viewport");

        var formatsToSupport = [
            Html5QrcodeSupportedFormats.CODE_128,
            Html5QrcodeSupportedFormats.CODE_39,
            Html5QrcodeSupportedFormats.EAN_13,
            Html5QrcodeSupportedFormats.EAN_8,
            Html5QrcodeSupportedFormats.UPC_A,
            Html5QrcodeSupportedFormats.UPC_E,
        ];

        // Camera selection:
        //   - If user picked a specific camera in the dropdown → use deviceId
        //     (forces the main back lens on multi-camera phones).
        //   - Otherwise fall back to facingMode auto.
        // html5-qrcode 2.3.8 contract: cameraIdOrConfig must be STRING or
        // single-key object. facingMode must be plain string or {exact:...}.
        var cameraIdOrConfig = selectedCameraId
            ? { deviceId: { exact: selectedCameraId } }
            : { facingMode: "environment" };

        var config = {
            fps: mode === "fullframe" ? 25 : 15,
            formatsToSupport: formatsToSupport,
            useBarCodeDetectorIfSupported: mode === "native" || mode === "capture" || mode === "fullframe",
        };

        // qrbox restricts the decode area to a fixed pixel-size rectangle.
        // For "fullframe" mode we OMIT qrbox so the whole viewfinder is decoded
        // (matches the Flutter mobile app behavior). All other modes keep the
        // 300x150 qrbox so we can compare them.
        if (mode !== "fullframe") {
            config.qrbox = { width: 300, height: 150 };
        }

        // Baseline = OLD broken prod config: no HD constraints, JS-only decoder.
        // Other modes request HD via videoConstraints.
        if (mode !== "baseline") {
            config.videoConstraints = {
                facingMode: { ideal: "environment" },
                width:  { ideal: 1920 },
                height: { ideal: 1080 },
            };
        }

        // For capture & fullframe modes, we run our own JS decode loop (see
        // startFullframeLoop / captureOnce). html5-qrcode just owns the
        // camera; its built-in decoder is bypassed via no-op callback.
        var onSuccess = (mode === "capture" || mode === "fullframe")
            ? function () {}
            : onScanSuccessLive;

        tlog("START", JSON.stringify({
            mode: mode,
            useNative: config.useBarCodeDetectorIfSupported,
            cameraIdOrConfig: cameraIdOrConfig,
            videoConstraints: config.videoConstraints || "(default)",
        }));

        try {
            await scanner.start(cameraIdOrConfig, config, onSuccess, function () {});
            setStatus(mode === "capture"
                ? "Pointez puis tapez 📸 Capturer"
                : mode === "fullframe"
                    ? "Auto-scan activé — pointez la bouteille"
                    : "Pointez vers le code-barres…");
            tlog("START_OK", mode);
            // Once permission has been granted, labels become visible —
            // refresh the picker so the user sees real names.
            populateCameraPicker();
            // Apply zoom + continuous focus to compensate for Chrome's lack
            // of explicit lens selection on Android.
            await applyOptimalCameraConstraints();
            if (mode === "fullframe") startFullframeLoop();
        } catch (err) {
            // Build the richest possible diagnostic for whatever this error is.
            var detail = {
                mode: mode,
                name: (err && err.name) || "?",
                message: (err && err.message) || String(err),
                stack: (err && err.stack) ? String(err.stack).split("\n").slice(0, 4).join(" | ") : "",
                constraintName: (err && err.constraint) || "",
            };
            terr("START_FAIL", JSON.stringify(detail));

            // Show the full message on screen so the tester can read & screenshot it.
            setStatus("❌ " + detail.name + ": " + detail.message);
            scanner = null;
            // Don't auto-hide on error — keep visible so user can capture it.
            // They can tap "Fermer" to dismiss.
        }
    }

    // Wire DOM
    document.addEventListener("DOMContentLoaded", function () {
        // Expose session id in the UI so testers can transmit it to the dev
        var sid = document.getElementById("session-id-display");
        if (sid) sid.textContent = SESSION_ID;

        // Camera picker change -> persist + log
        var picker = document.getElementById("camera-picker");
        if (picker) {
            picker.addEventListener("change", function () {
                selectedCameraId = picker.value || "";
                try { localStorage.setItem("petrolex-scanner-camera", selectedCameraId); } catch (e) {}
                tlog("CAMERA_PICKED", selectedCameraId ? selectedCameraId.slice(0, 8) : "auto");
            });
        }

        // Render initial empty states
        ["baseline", "native", "jspure", "capture", "fullframe", "native-zbar"].forEach(renderCard);

        // Étape 6 — native camera trigger
        var nativeBtn = document.querySelector('[data-action="open-native-camera"]');
        var nativeInput = document.getElementById("native-camera-input");
        if (nativeBtn && nativeInput) {
            nativeBtn.addEventListener("click", function () {
                nativeInput.value = ""; // allow re-selecting the same file
                nativeInput.click();
            });
            nativeInput.addEventListener("change", function () {
                if (nativeInput.files && nativeInput.files[0]) {
                    handleNativeCameraFile(nativeInput.files[0]);
                }
            });
        }

        $all("[data-action='start']").forEach(function (btn) {
            btn.addEventListener("click", function () {
                startScanner(btn.getAttribute("data-mode"));
            });
        });

        $all("[data-action='reset']").forEach(function (btn) {
            btn.addEventListener("click", function () {
                resetMode(btn.getAttribute("data-mode"));
            });
        });

        $("#scanner-cancel-btn").addEventListener("click", function () {
            stopScanner();
        });

        $("#scanner-capture-btn").addEventListener("click", function () {
            captureOnce();
        });

        var zoomSlider = $("#zoom-slider");
        if (zoomSlider) {
            zoomSlider.addEventListener("input", function () {
                var v = parseFloat(zoomSlider.value);
                if (!isNaN(v)) setZoom(v);
            });
        }
    });
})();
