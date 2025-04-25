<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test DataTable</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome pour les icônes -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    @livewireStyles
</head>
<body class="bg-light">
    <div class="container my-5">
        <h1 class="mb-4">Liste des utilisateurs</h1>

        <div class="card">
            <div class="card-body">
                <livewire:users-table />
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @livewireScripts

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('notify', (event) => {
                Swal.fire({
                    icon: event[0].type,
                    title: event[0].message,
                    showConfirmButton: false,
                    timer: 3000
                });
            });
        });
    </script>
</body>
</html>
