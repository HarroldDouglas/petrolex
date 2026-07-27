---
description: Architecture du projet Petrolex — structure en couches, stack technique
globs:
  - "app/**"
  - "routes/**"
---

# Architecture

Stack : Laravel (PHP 8.2), MySQL 8, Redis, Laravel Reverb (WebSockets), queues Supervisor.

## Couches (`app/`)
- `Http/Api/Controllers/` — endpoints app mobile (clients + livreurs), **single-action `__invoke()`**
- `Http/Api/Requests/` — FormRequests de validation
- `Http/Api/Resources/` — transformation JSON sortante
- `Http/Api/Responses/` — enveloppes de réponse (`ApiResponse`, `*Response::withCollection()`)
- `Http/Controllers/Api/Payment/` — callbacks des passerelles (MTN, Orange)
- `Services/` — logique métier (une classe par domaine : `Order/`, `Auth/`, `DistributionCenter/`, …)
- `Repositories/` — accès données via interfaces (`*RepositoryInterface`)
- `DTOs/`, `Enums/`, `Events/`, `Jobs/`, `Listeners/`, `Notifications/`, `Mail/`, `Rules/`, `Casts/`, `Traits/`
- `Livewire/` + `View/` — panneau d'administration (Livewire + Blade)
- `Models/` — Eloquent (namespace `App\Models\`, pas de sous-namespace `Core`)

## Routes
- `routes/api/` est éclaté par domaine : `auth.php`, `customers.php`, `orders.php`, `bottles.php`,
  `payments.php`, `payment-callbacks.php`, `distribution-centers.php`, `delivery.php`, `manager.php`,
  `tracking.php`, `geography.php`, `app.php`.

## Données de référence & seeders
- Prod : `DatabaseSeeder` en env `production` ne seede QUE la référence (géo, rôles, super admin,
  comptes de test, types bouteilles/accessoires, catégories, **centre par défaut = Logbessou**, versions app).
- Les vraies commandes/paiements/bouteilles/produits sont créés à l'exécution, jamais seedés.
