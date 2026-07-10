<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Test Scanner — Petrolex/Isogaz</title>
    <style>
        :root {
            --bg: #0f172a;
            --card: #1e293b;
            --text: #f1f5f9;
            --muted: #94a3b8;
            --green: #16a34a;
            --blue: #2563eb;
            --orange: #ea580c;
            --red: #dc2626;
            --border: #334155;
        }
        * { box-sizing: border-box; }
        html, body {
            margin: 0; padding: 0;
            background: var(--bg);
            color: var(--text);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 16px;
        }
        .wrap { max-width: 720px; margin: 0 auto; padding: 16px; }
        h1 { font-size: 20px; margin: 0 0 4px; }
        .sub { color: var(--muted); font-size: 13px; margin-bottom: 16px; }
        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
        }
        .card h2 { font-size: 16px; margin: 0 0 4px; }
        .card .desc { color: var(--muted); font-size: 13px; margin-bottom: 12px; }
        .btn {
            display: inline-block;
            padding: 14px 20px;
            border-radius: 8px;
            border: none;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            margin-bottom: 8px;
        }
        .btn-green  { background: var(--green); color: white; }
        .btn-blue   { background: var(--blue);  color: white; }
        .btn-orange { background: var(--orange); color: white; }
        .btn-red    { background: var(--red); color: white; }
        .btn-ghost  { background: transparent; color: var(--text); border: 1px solid var(--border); }
        .stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin: 12px 0;
        }
        .stat {
            background: rgba(255,255,255,0.05);
            border-radius: 8px;
            padding: 10px;
            text-align: center;
        }
        .stat .v { font-size: 22px; font-weight: 700; }
        .stat .l { font-size: 11px; color: var(--muted); text-transform: uppercase; letter-spacing: 0.5px; }
        .log {
            max-height: 200px;
            overflow-y: auto;
            background: rgba(0,0,0,0.3);
            border-radius: 8px;
            padding: 8px;
            font-family: ui-monospace, Menlo, monospace;
            font-size: 12px;
        }
        .log-row {
            padding: 6px 8px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
        }
        .log-row:last-child { border-bottom: none; }
        .log-row .v { font-weight: 600; }
        .ok  { color: #4ade80; }
        .bad { color: #f87171; }
        .empty { color: var(--muted); text-align: center; padding: 12px; font-size: 13px; }

        /* Scanner overlay */
        #scanner-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.95);
            z-index: 9999;
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 12px;
        }
        #scanner-overlay.on { display: flex; }
        #scanner-title {
            color: white; font-size: 14px; margin-bottom: 8px;
        }
        #scanner-viewport {
            width: 100%; max-width: 520px;
            border-radius: 8px;
            overflow: hidden;
            background: #000;
        }
        #scanner-viewport video, #scanner-viewport canvas {
            width: 100%; height: auto; display: block;
        }
        #scanner-status {
            color: white;
            margin-top: 10px;
            font-size: 14px;
            padding: 6px 12px;
            background: rgba(0,0,0,0.5);
            border-radius: 6px;
            text-align: center;
            min-height: 22px;
            width: 100%;
            max-width: 520px;
        }
        #scanner-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
            width: 100%;
            max-width: 520px;
        }
        #scanner-actions .btn { margin-bottom: 0; }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
            background: rgba(255,255,255,0.1);
            color: var(--muted);
            margin-left: 6px;
        }
    </style>
    <script src="{{ asset('assets/js/js-logger.js') }}?v={{ filemtime(public_path('assets/js/js-logger.js')) }}"></script>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script src="https://unpkg.com/@zxing/library@0.21.3/umd/index.min.js"></script>
</head>
<body>

<div class="wrap">
    <h1>Test Scanner Petrolex</h1>
    <p class="sub">Faites <strong>10 essais par étape</strong> en scannant la <strong>même bouteille</strong>. Notez si les valeurs sont identiques (✓) ou différentes (✗).</p>
    <p class="sub">Session ID (à transmettre en cas de problème): <strong id="session-id-display" style="color:#fbbf24;">…</strong></p>

    <!-- Camera picker — let user override the auto-selected back camera.
         On phones with multiple back cameras (ultra-wide / telephoto), the
         browser may pick a non-main lens that can't focus close. -->
    <div class="card" style="border-color:#fbbf24;">
        <h2>📷 Sélection de la caméra</h2>
        <div class="desc">
            <strong>Important pour les téléphones à plusieurs caméras (Samsung A56, iPhone Pro...)</strong>:
            choisissez explicitement la caméra arrière principale ici. Si vous laissez "Auto", le navigateur
            peut sélectionner une ultra-grand-angle qui ne fait pas la mise au point sur les objets proches.
        </div>
        <select id="camera-picker" class="form-select" style="
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: rgba(0,0,0,0.3);
            color: var(--text);
            font-size: 14px;
        ">
            <option value="">⚡ Auto (caméra arrière par défaut)</option>
        </select>
        <p class="sub" style="margin-top:8px;margin-bottom:0;font-size:12px;">
            Astuce: testez avec chaque caméra arrière (camera 0 et camera 2 par exemple) pour voir laquelle fait la mise au point.
        </p>
    </div>

    <!-- BASELINE (reproduction du bug) -->
    <div class="card" data-mode="baseline" style="border-color:#dc2626;">
        <h2>🎯 Étape 1 — Reproduction du problème</h2>
        <div class="desc">
            Configuration d'origine (décodeur JS standard, caméra par défaut). <strong>Cette étape sert à confirmer que le bug est bien présent sur votre téléphone.</strong>
            Si rien n'est lu ou si les valeurs sont incohérentes ici, c'est attendu — passez aux étapes suivantes.
        </div>
        <button class="btn btn-red" data-action="start" data-mode="baseline">▶️ Lancer l'étape de reproduction</button>
        <div class="stats">
            <div class="stat"><div class="v" data-stat="count">0</div><div class="l">Lectures</div></div>
            <div class="stat"><div class="v" data-stat="unique">0</div><div class="l">Valeurs distinctes</div></div>
        </div>
        <div class="log" data-log></div>
        <button class="btn btn-ghost" data-action="reset" data-mode="baseline">Réinitialiser</button>
    </div>

    <!-- OPTION 1 -->
    <div class="card" data-mode="native">
        <h2>Étape 2 — Live HD + API native <span class="badge">actuel prod</span></h2>
        <div class="desc">Décodage continu via l'API <code>BarcodeDetector</code> du navigateur. Caméra 1920×1080.</div>
        <button class="btn btn-green" data-action="start" data-mode="native">▶️ Démarrer Option 1</button>
        <div class="stats">
            <div class="stat"><div class="v" data-stat="count">0</div><div class="l">Lectures</div></div>
            <div class="stat"><div class="v" data-stat="unique">0</div><div class="l">Valeurs distinctes</div></div>
        </div>
        <div class="log" data-log></div>
        <button class="btn btn-ghost" data-action="reset" data-mode="native">Réinitialiser</button>
    </div>

    <!-- OPTION 2 -->
    <div class="card" data-mode="jspure">
        <h2>Étape 3 — Live HD JS pur</h2>
        <div class="desc">Décodage continu mais sans API native (décodeur JS de html5-qrcode). Caméra 1920×1080.</div>
        <button class="btn btn-blue" data-action="start" data-mode="jspure">▶️ Démarrer Option 2</button>
        <div class="stats">
            <div class="stat"><div class="v" data-stat="count">0</div><div class="l">Lectures</div></div>
            <div class="stat"><div class="v" data-stat="unique">0</div><div class="l">Valeurs distinctes</div></div>
        </div>
        <div class="log" data-log></div>
        <button class="btn btn-ghost" data-action="reset" data-mode="jspure">Réinitialiser</button>
    </div>

    <!-- OPTION 3 -->
    <div class="card" data-mode="capture">
        <h2>Étape 4 — Tap-to-capture (rafale intelligente)</h2>
        <div class="desc">
            Pointez la bouteille, appuyez sur <strong>📸 Capturer</strong>. Le système prend <strong>5 frames en rafale</strong>,
            choisit automatiquement la plus nette (filtre Laplacien), et décode. Les images floues sont rejetées
            automatiquement — aucune fausse lecture possible.
        </div>
        <button class="btn btn-orange" data-action="start" data-mode="capture">▶️ Démarrer l'étape 4</button>
        <div class="stats">
            <div class="stat"><div class="v" data-stat="count">0</div><div class="l">Lectures</div></div>
            <div class="stat"><div class="v" data-stat="unique">0</div><div class="l">Valeurs distinctes</div></div>
        </div>
        <div class="log" data-log></div>
        <button class="btn btn-ghost" data-action="reset" data-mode="capture">Réinitialiser</button>
    </div>

    <!-- OPTION 5 — Full frame (closest to Flutter mobile app behavior) -->
    <div class="card" data-mode="fullframe" style="border-color:#16a34a;">
        <h2>⭐ Étape 5 — Plein écran (style app mobile)</h2>
        <div class="desc">
            <strong>Aucun cadre, décodage de toute la caméra.</strong> Identique au comportement de l'app mobile Flutter — pointez n'importe où dans le champ, ça lit dès que le code est visible. Recommandé si les étapes précédentes ratent.
        </div>
        <button class="btn btn-green" data-action="start" data-mode="fullframe">▶️ Démarrer l'étape 5</button>
        <div class="stats">
            <div class="stat"><div class="v" data-stat="count">0</div><div class="l">Lectures</div></div>
            <div class="stat"><div class="v" data-stat="unique">0</div><div class="l">Valeurs distinctes</div></div>
        </div>
        <div class="log" data-log></div>
        <button class="btn btn-ghost" data-action="reset" data-mode="fullframe">Réinitialiser</button>
    </div>

    <!-- OPTION 6 — Native camera + server-side zbar decode -->
    <div class="card" data-mode="native-zbar" style="border-color:#a855f7;">
        <h2>🚀 Étape 6 — Caméra système + zbar serveur</h2>
        <div class="desc">
            <strong>Contourne complètement les limitations du navigateur.</strong>
            Ouvre <strong>l'app caméra native du téléphone</strong> (qui a accès à la bonne lentille, autofocus, zoom natif).
            La photo prise est envoyée au serveur qui la décode avec <strong>zbar</strong> (décodeur industriel C++).
            <br><br>
            <em>Plus lent (~1 s/scan, dépend du réseau), mais marche sur n'importe quel téléphone.</em>
        </div>
        <button class="btn" data-action="open-native-camera" style="background:#a855f7;color:white;">📷 Ouvrir caméra système</button>
        <input id="native-camera-input" type="file" accept="image/*" capture="environment" style="display:none;">
        <div id="native-zbar-status" class="sub" style="margin:8px 0 4px;font-size:13px;"></div>
        <div class="stats">
            <div class="stat"><div class="v" data-stat="count">0</div><div class="l">Lectures</div></div>
            <div class="stat"><div class="v" data-stat="unique">0</div><div class="l">Valeurs distinctes</div></div>
        </div>
        <div class="log" data-log></div>
        <button class="btn btn-ghost" data-action="reset" data-mode="native-zbar">Réinitialiser</button>
    </div>

    <p class="sub" style="text-align:center;">
        Astuce: gardez le téléphone bien stable, faites 10 scans, comparez la cohérence.
    </p>
</div>

<!-- Scanner overlay (shared by all modes) -->
<div id="scanner-overlay">
    <div id="scanner-title">Mode: <span id="scanner-mode-label">—</span></div>
    <div id="scanner-viewport"></div>
    <div id="scanner-status">Caméra…</div>
    <!-- Zoom slider — only visible if track exposes zoom capability.
         Compensates for Chrome's lack of explicit lens selection on Android:
         on phones where Chrome picks the ultra-wide back lens, zooming 2-3x
         crops to behave like the main 1x lens. -->
    <div id="zoom-slider-wrap" style="display:none; flex-direction:column; align-items:center;
        width:100%; max-width:520px; margin-top:10px; padding:8px 12px;
        background:rgba(0,0,0,0.5); border-radius:6px;">
        <div id="zoom-slider-label" style="color:#fbbf24; font-size:13px; margin-bottom:4px; font-weight:600;">Zoom: 2.0×</div>
        <input id="zoom-slider" type="range" min="1" max="5" step="0.1" value="2" style="width:100%; accent-color:#fbbf24;">
    </div>
    <div id="scanner-actions">
        <button class="btn btn-orange" id="scanner-capture-btn" style="display:none;">📸 Capturer</button>
        <button class="btn btn-red" id="scanner-cancel-btn">Fermer</button>
    </div>
</div>

<script src="{{ asset('assets/js/test-scanner.js') }}?v={{ filemtime(public_path('assets/js/test-scanner.js')) }}"></script>
</body>
</html>
