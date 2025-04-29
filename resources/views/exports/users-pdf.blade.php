<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page {
            margin: 15mm;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }
        th, td {
            padding: 6px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            word-wrap: break-word;
        }
        th {
            background-color: #f8f9fa;
            font-size: 11px;
        }
        td {
            font-size: 10px;
        }
        .text-center { text-align: center; }
        .text-success { color: #28a745; }
        .text-danger { color: #dc3545; }

        /* Largeurs de colonnes fixes */
        .col-nom { width: 15%; }
        .col-prenom { width: 15%; }
        .col-email { width: 20%; }
        .col-telephone { width: 15%; }
        .col-statut { width: 10%; }
        .col-connexion { width: 25%; }
    </style>
</head>
<body>
    <h2 class="text-center">Liste des Utilisateurs</h2>
    <table>
        <thead>
            <tr>
                <th class="col-nom">Nom</th>
                <th class="col-prenom">Prénom</th>
                <th class="col-email">Email</th>
                <th class="col-telephone">Téléphone</th>
                <th class="col-statut">Statut</th>
                <th class="col-connexion">Dernière connexion</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $user)
                <tr>
                    <td>{{ $user->last_name }}</td>
                    <td>{{ $user->first_name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->phone_number }}</td>
                    <td>{{ $user->is_active ? 'Actif' : 'Inactif' }}</td>
                    <td>{{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
