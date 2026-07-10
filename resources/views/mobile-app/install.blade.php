<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Isogaz Manager — Installation</title>
    <style>
        :root {
            --red: #E63946;
            --dark: #1D3557;
            --bg: #f8f9fa;
            --muted: #6c757d;
            --border: #dee2e6;
        }
        * { box-sizing: border-box; }
        html, body {
            margin: 0; padding: 0;
            background: var(--bg);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #212529;
        }
        .wrap { max-width: 480px; margin: 0 auto; padding: 24px 20px; }
        .logo {
            display: flex; align-items: center; justify-content: center;
            width: 88px; height: 88px;
            margin: 24px auto 16px;
            background: var(--red); border-radius: 20px;
            font-size: 44px;
        }
        h1 { font-size: 24px; text-align: center; margin: 0 0 6px; }
        .sub { text-align: center; color: var(--muted); margin: 0 0 24px; font-size: 14px; }

        .card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 16px;
        }
        .card h2 {
            font-size: 16px;
            margin: 0 0 10px;
            color: var(--dark);
        }
        .meta {
            display: flex; gap: 12px; font-size: 13px; color: var(--muted);
            margin-bottom: 14px;
        }
        .meta span { background: var(--bg); padding: 4px 10px; border-radius: 999px; }

        .btn {
            display: block; width: 100%;
            padding: 16px; text-align: center;
            background: var(--red); color: white;
            text-decoration: none; font-weight: 700; font-size: 16px;
            border-radius: 10px; border: none; cursor: pointer;
        }
        .btn:active { transform: translateY(1px); }
        .btn.disabled {
            background: #adb5bd; pointer-events: none;
        }

        ol.steps { padding-left: 22px; margin: 0; }
        ol.steps li { margin-bottom: 10px; font-size: 14px; line-height: 1.4; }
        ol.steps li b { color: var(--dark); }

        .warn {
            background: #fff3cd; border: 1px solid #ffe69c;
            border-radius: 8px; padding: 12px;
            font-size: 13px; line-height: 1.4;
            margin-top: 12px;
        }
        .footer {
            text-align: center; font-size: 12px; color: var(--muted);
            margin-top: 32px;
        }
        .footer a { color: var(--red); text-decoration: none; }
    </style>
</head>
<body>
<div class="wrap">

    <img src="/assets/images/logo/isogaz-no-bg.png" alt="Isogaz"
         style="display:block; width:140px; height:auto; margin: 24px auto 16px;">
    <h1>Isogaz Manager</h1>
    <p class="sub">Application Android dédiée aux gestionnaires<br>pour scanner les approvisionnements & commandes</p>

    <div class="card">
        @if($available)
            <div class="meta">
                @if($version)<span>v{{ $version }}</span>@endif
                @if($buildDate)<span>{{ \Carbon\Carbon::parse($buildDate)->format('d/m/Y') }}</span>@endif
                <span>{{ $sizeMb }} MB</span>
            </div>
            <a href="{{ route('mobile-app.download') }}" class="btn">📥 Télécharger l'application</a>
        @else
            <button class="btn disabled" disabled>⏳ Application bientôt disponible</button>
            <p style="text-align:center; margin: 12px 0 0; color: var(--muted); font-size: 13px;">
                L'APK sera disponible ici dans quelques jours.
            </p>
        @endif
    </div>

    <div class="card">
        <h2>📲 Comment installer</h2>
        <ol class="steps">
            <li><b>Tape "Télécharger"</b> ci-dessus. Le fichier <code>petrolex-manager.apk</code> est enregistré dans tes téléchargements.</li>
            <li>Ouvre le fichier APK. Android demandera ta permission pour installer une app hors Play Store.</li>
            <li><b>Active "Sources inconnues"</b> ou <b>"Autoriser cette source"</b> dans les paramètres si demandé.</li>
            <li>Confirme l'installation. L'app apparaît dans ton menu d'apps.</li>
            <li>Ouvre <b>Isogaz Manager</b> et connecte-toi avec tes identifiants habituels.</li>
        </ol>
        <div class="warn">
            ℹ️ Si Chrome affiche un avertissement "Fichier potentiellement dangereux", tape <b>Conserver</b>. C'est normal pour les apps hors Play Store — ton fichier est sain.
        </div>
    </div>

    <div class="card">
        <h2>✨ Fonctionnalités</h2>
        <ol class="steps">
            <li><b>Approvisionnements</b> — liste, détail, scan rapide des bouteilles entrantes & sortantes</li>
            <li><b>Commandes</b> — scan des bouteilles vides retournées par les clients</li>
            <li><b>Caméra native</b> — accès direct à la lentille principale, autofocus, flash. Fini les limitations du navigateur.</li>
        </ol>
    </div>

    <div class="footer">
        Besoin d'aide ? <a href="mailto:contact@isogaz.net">contact@isogaz.net</a>
    </div>
</div>
</body>
</html>
