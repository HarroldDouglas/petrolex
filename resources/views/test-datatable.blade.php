<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test DataTable Standalone</title>

    <!-- CSS de Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- CSS spécifique pour la table (si nécessaire) -->
    <style>
        .badge.bg-info {
            background-color: var(--bs-primary) !important;
        }

        /* Styles additionnels pour l'UI de la table */
        .dropdown-item:active, .dropdown-item:focus {
            background-color: var(--bs-primary);
        }
    </style>

    <!-- Livewire Styles -->
    @livewireStyles
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white py-3">
                        <h4 class="mb-0">Liste des utilisateurs</h4>
                    </div>
                    <div class="card-body">
                        <!-- Le composant Livewire -->
                        <livewire:users-table />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Livewire Scripts -->
    @livewireScripts
</body>
</html>
