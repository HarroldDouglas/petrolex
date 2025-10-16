<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://api.google_maps.com/google_maps-gl-js/v2.15.0/google_maps-gl.css' rel='stylesheet' />
    @livewireStyles
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">Dashboard Manager - Suivi des Livraisons</h2>
            </div>
        </div>
        
        <div id="app">
            <livewire:delivery-dashboard />
        </div>
    </div>
    
    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src='https://api.google_maps.com/google_maps-gl-js/v2.15.0/google_maps-gl.js'></script>
    
    @stack('scripts')
</body>
</html>
