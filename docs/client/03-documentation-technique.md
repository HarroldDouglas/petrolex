# Isogaz — Documentation technique

**Version :** 1.0 — 18/08/2026
**Version applicative de référence :** v1.7.0
**Public :** équipe technique, prestataire de maintenance, DSI

---

## 1. Vue d'ensemble

```
┌─────────────────────┐   ┌──────────────────────┐
│  App mobile Client  │   │ App mobile Livreur / │
│      (Flutter)      │   │  Manager (Flutter)   │
└──────────┬──────────┘   └───────────┬──────────┘
           │        HTTPS / REST      │
           └────────────┬─────────────┘
                        ▼
        ┌───────────────────────────────────┐
        │      API Laravel  (isogaz.net)    │
        │  Controllers → Services → Repos   │
        └───┬───────────┬──────────┬────────┘
            │           │          │
      ┌─────▼────┐ ┌────▼────┐ ┌───▼────────────┐
      │ MySQL 8  │ │  Redis  │ │ Reverb (WS)    │
      └──────────┘ └────┬────┘ └───────┬────────┘
                        │              │
                  ┌─────▼──────┐  suivi GPS temps réel
                  │ Supervisor │  → apps mobiles
                  │  workers   │
                  └─────┬──────┘
                        │
        ┌───────────────┼───────────────┐
        ▼               ▼               ▼
   MTN MoMo       Orange Money       Twilio (SMS/OTP)

        ┌─────────────────────────────┐
        │ Admin web (Livewire+Blade)  │  ← même application Laravel
        └─────────────────────────────┘
```

---

## 2. Stack technique

| Couche | Technologie | Version |
|---|---|---|
| Langage | PHP | 8.2 |
| Framework | Laravel | 11 |
| Base de données | MySQL | 8.0 |
| Cache / files d'attente | Redis | — |
| WebSockets | Laravel Reverb | 1.x |
| Interface admin | Livewire 3 + Blade + Tailwind CSS | — |
| Authentification API | Laravel Sanctum | 4.x |
| Permissions | spatie/laravel-permission | 6.x |
| Énumérations | spatie/laravel-enum | 3.x |
| DTO | spatie/laravel-data | 4.x |
| Médias | spatie/laravel-medialibrary | — |
| Journal d'activité | spatie/laravel-activitylog | 4.x |
| SMS / OTP | Twilio SDK | 8.x |
| Documentation API | L5-Swagger (OpenAPI) | 9.x |
| Serveur | Ubuntu 22.04 + Nginx + PHP-FPM + Supervisor | — |

**Applications mobiles :** Flutter (dépôts séparés).

---

## 3. Architecture applicative

L'application suit une architecture en couches stricte. Les dépendances vont toujours du
haut vers le bas ; on ne remonte jamais.

```
Route
  └─▶ Controller (single-action, __invoke)
        └─▶ FormRequest        (validation d'entrée)
        └─▶ Service            (logique métier, transactions)
              └─▶ Repository   (accès aux données, via interface)
                    └─▶ Model  (Eloquent)
        └─▶ Resource           (sérialisation JSON de sortie)
```

### Arborescence `app/`

| Dossier | Rôle |
|---|---|
| `Http/Api/Controllers/` | Endpoints des applications mobiles — **une classe = une action** (`__invoke()`) |
| `Http/Api/Requests/` | Validation des requêtes entrantes |
| `Http/Api/Resources/` | Transformation des modèles en JSON |
| `Http/Api/Responses/` | Enveloppes de réponse normalisées (`ApiResponse`) |
| `Http/Controllers/Api/Payment/` | Points d'entrée des passerelles de paiement |
| `Services/` | Logique métier, un sous-dossier par domaine |
| `Repositories/` | Accès aux données derrière des interfaces (`Contracts/` + `Eloquent/`) |
| `Models/` | Entités Eloquent |
| `DTOs/` | Objets de transfert (spatie/laravel-data) |
| `Enums/` | Énumérations métier |
| `Events/` `Listeners/` | Événements domaine et diffusion WebSocket |
| `Jobs/` | Traitements asynchrones |
| `Notifications/` `Mail/` | Notifications et e-mails |
| `Rules/` `Casts/` `Traits/` | Utilitaires transverses |
| `Livewire/` `View/` | Composants du panneau d'administration |

### Conventions imposées

- **Contrôleurs API single-action** : une classe, une méthode `__invoke()`, un docblock `Route:` / `Name:`.
- **Aucune logique métier dans un contrôleur** — elle vit dans `Services/`.
- **Aucune requête brute dans un service** dès lors qu'un repository existe.
- **Énumérations Spatie v3** : appel *méthode* `ProductType::BOTTLE()`, jamais la constante ; valeur via `->value`.
- **Commentaires en anglais uniquement**, minimalistes, expliquant le *pourquoi* et non le *quoi* ;
  multi-lignes en bloc `/* … */`.

---

## 4. Modèle de données

### 4.1 Domaines

**Géographie** — `countries` → `cities` → `municipalities` → `neighborhoods`
Les quartiers portent `latitude` / `longitude` en **NOT NULL** (contrainte volontaire, voir §9).

**Organisation** — `distribution_centers` (rattachés à un quartier),
`user_distribution_centers` (affectation du personnel),
`product_category_distribution_centers` (catalogue actif par centre).

**Catalogue** — `product_categories`, `bottle_types`, `accessory_types`, `accessories`,
`products`, `product_category_city_prices` (surcharge tarifaire par ville).

**Parc bouteilles** — `bottles` (une ligne = une bouteille physique, clé `barcode`),
`bottle_movements` (historique), `order_bottle_scans` (rattachement bouteille ↔ ligne de commande),
`supplier_deliveries`, `supplier_delivery_bottles`, `supplier_delivery_product_types`.

**Commercial** — `customers`, `customer_delivery_addresses`, `orders`, `order_items`,
`order_payments`, `refunds`, `wallet_transactions`.

**Livraison** — `delivery_people`, `delivery_trackings`.

**Système** — `users`, `roles`, `permissions` (Spatie), `app_versions`, `mobile_app_logs`,
`activity_log`, `jobs`, `cache`.

### 4.2 Relations structurantes

```
Customer ──1:N──▶ Order ──1:N──▶ OrderItem ──1:N──▶ OrderBottleScan ──▶ Bottle
    │                │
    │                ├──1:N──▶ OrderPayment
    │                └──1:1──▶ DeliveryTracking ──▶ DeliveryPerson
    └──1:N──▶ WalletTransaction

DistributionCenter ──1:N──▶ Bottle
        │
        ├──N:N──▶ ProductCategory
        └──N:N──▶ User (personnel affecté)

ProductCategory ──1:N──▶ ProductCategoryCityPrice ──▶ City
```

### 4.3 Énumérations

| Énumération | Valeurs |
|---|---|
| `OrderStatus` | `pending`, `paid`, `processing`, `delivered`, `cancelled`, `failed` |
| `PaymentStatus` | `pending`, `processing`, `paid`, `failed`, `refunded` |
| `PaymentMethod` | `mtn_money`, `orange_money`, `wallet`, `credit_card` |
| `BottleStatus` | `in_stock`, `with_delivery_person`, `with_client`, `pending_reception`, `returned_to_supplier`, `lost_stolen` |
| `BottleOrderType` | `full`, `recharge` |
| `ProductType` | `bottle`, `accessory` |
| `DeliveryStatus` | `in_progress`, `completed`, `cancelled` |
| `DeliveryType` | `normal`, `fast` |
| `SupplierDeliveryStatus` | `in_progress`, `completed`, `cancelled` |
| `WalletTransactionType` | `credit`, `debit` |
| `UserRole` | `super_admin`, `admin`, `manager`, `center_manager`, `gas_manager`, `accountant`, `delivery_person`, `customer` |

---

## 5. API REST

**Base :** `https://isogaz.net/api`
**Authentification :** `Authorization: Bearer <token>` (Sanctum)
**Documentation interactive :** Swagger UI généré depuis les annotations de `documentation/`

Les routes sont éclatées par domaine dans `routes/api/`.

### 5.1 Authentification — `auth.php`
| Méthode | Chemin | Rôle |
|---|---|---|
| POST | `/register/customer` | Inscription client |
| POST | `/login/customer` | Connexion client |
| POST | `/login/delivery` | Connexion livreur |
| POST | `/login` | Connexion générique |
| POST | `/verify-otp` | Vérification du code OTP |
| POST | `/resend-otp` | Renvoi du code |
| POST | `/forgot-password` | Réinitialisation de mot de passe |
| GET | `/auth/check` | Validité du jeton |
| GET | `/user` | Profil courant |
| PATCH | `/profile` | Mise à jour du profil |
| PATCH | `/password` | Changement de mot de passe |
| POST | `/logout` | Déconnexion |

### 5.2 Géographie — `geography.php`
`GET /countries` · `GET /countries/{country}/cities` · `GET /cities/{cityId}` ·
`GET /cities/{cityId}/neighborhoods` · `GET /neighborhoods/{neighborhoodId}`

### 5.3 Centres et catalogue — `distribution-centers.php`
`GET /` · `GET /closest` (centre le plus proche par coordonnées) · `GET /{id}/products`

### 5.4 Clients — `customers.php`
`POST /` · `GET /` · `GET /{customerId}` ·
`POST /delivery-addresses` · `PUT /delivery-addresses/{deliveryAddress}`

### 5.5 Commandes — `orders.php`
| Méthode | Chemin | Rôle |
|---|---|---|
| POST | `/` | Créer une commande |
| GET | `/my/orders` | Historique du client |
| GET | `/{order}` | Détail |
| POST | `/{order}/payment` | Déclencher le paiement |
| PATCH | `/{order}/cancel` | Annuler |
| POST | `/{order}/scan-empty-bottle` | Scanner la bouteille vide reprise |
| PATCH | `/{order}/deliver` | Clôturer la livraison |
| GET | `/{order}/download/invoice` | Facture PDF |
| POST | `/{order}/customer-feedback` | Évaluation client |

### 5.6 Livraison et suivi — `delivery.php`, `tracking.php`
`GET /delivery-types` ·
`POST /{orderId}/start` · `PATCH /{orderId}/position` · `GET /{orderId}` · `PATCH /{orderId}/complete`

### 5.7 Manager / centre — `manager.php`
`GET /supplies` · `GET /supplies/{supply}` · `POST /supplies/{supply}/scan-bottle` ·
`POST /supplies/{supply}/remove-bottles` ·
`GET /orders` · `GET /orders/{order}` · `POST /orders/{order}/scan-full-bottle`

### 5.8 Bouteilles et paiements
`GET /bottles/{barcode}/verify` · `GET /payments/payment-methods` ·
`POST /payment-callbacks/momo` · `POST /payment-callbacks/orange`

### 5.9 Service applicatif — `app.php`
`GET /terms-and-conditions` · `GET /privacy-policy` · `GET /support/contact` ·
`GET /advertising/banners` · `GET /version` · `PUT /version/{app_type}` ·
**`POST /logs`** — remontée des erreurs des applications Flutter

---

## 6. Processus métier détaillés

### 6.1 Création d'une commande

`OrderService::create()` / `createWithoutPayment()` — le tout dans une transaction :

1. **Validation de cohérence géographique** — toutes les lignes doivent relever de la même commune.
2. **Tarification** — le prix est recalculé côté serveur dans la ville du centre :
   `product_category_city_prices` en priorité, repli sur le prix de base du `bottle_type`
   (`content_price` pour une recharge, `full_price` pour une bouteille complète).
3. **Calcul du sous-total** à partir des prix validés.
4. **Création de la commande** au statut `pending`, émission de `OrderCreatedEvent`.
5. **Application du portefeuille** via `WalletService::calculatePaymentBreakdown()` :
   - solde suffisant → débit immédiat, statut `paid`, `paid_at` renseigné ;
   - solde partiel → montant mémorisé mais **non débité** ; le débit aura lieu au moment
     du paiement externe, pour éviter un débit orphelin si la passerelle échoue.

### 6.2 Paiement Mobile Money

```
Client                API                    Passerelle
  │  POST /payment     │                          │
  ├───────────────────▶│                          │
  │                    │  demande de paiement     │
  │                    ├─────────────────────────▶│
  │◀──── notification push de l'opérateur ────────┤
  │  (saisie du code secret sur le téléphone)     │
  │                    │                          │
  │            VerifyPaymentStatusJob             │
  │                    │  interrogation du statut │
  │                    ├─────────────────────────▶│
  │                    │◀──── statut définitif ───┤
  │                    │                          │
  │            Order → paid / failed              │
```

- `PaymentGatewayFactory` sélectionne l'implémentation (`MTNMoneyGateway`, `OrangeMoneyGateway`).
- Les contrôleurs de callback sont des **stubs** : la source de vérité est le job de vérification.
- **MTN** : `proxy.momoapi.mtn.com`, montant transmis en **chaîne entière sans décimale**
  (`(string)(int)$amount`) — le XAF n'a pas de centimes.
- **Orange** : `api-s1.orange.cm`, jeton obtenu sur `/token`.
- Préfixes opérateurs Cameroun — MTN : `67x`, `650-654`, `676-679` · Orange : `655-659`, `690-699`.

### 6.3 Cycle de vie d'une bouteille

```
                 scan réception (appro)
   fournisseur ─────────────────────────▶ IN_STOCK
                                             │
                          scan chargement    │
                                             ▼
                                   WITH_DELIVERY_PERSON
                                             │
                             livraison       │
                                             ▼
                                       WITH_CLIENT
                                             │
                          scan reprise vide  │
                                             ▼
                                        IN_STOCK (vide)
                                             │
                                             ▼
                                  RETURNED_TO_SUPPLIER
```

Invariants protégés par `IncomingScanGuard` et `BottleReleaseService` :
- Une bouteille ne peut être réceptionnée dans deux approvisionnements simultanément.
- `OrderService::handleEmptyBottleReturn()` refuse une bouteille pleine et refuse un
  doublon sur la même ligne de commande ; un code-barres inconnu déclenche la création
  automatique de la bouteille dans le centre de la commande.

### 6.4 Suivi temps réel

`DeliveryTrackingService` persiste les positions ; l'événement `DeliveryPositionUpdated` est
diffusé par **Laravel Reverb** sur un canal privé par commande. Les applications mobiles
s'y abonnent. Reverb écoute sur le port `8080`, proxifié par Nginx sur `/app` et `/apps`.

### 6.5 Traitements asynchrones

| Job | Rôle |
|---|---|
| `VerifyPaymentStatusJob` | Interrogation du statut auprès de l'opérateur jusqu'à résolution |
| `GenerateInvoicePdfJob` | Génération de la facture PDF après livraison |

Exécutés par Supervisor (`petrolex-worker:*`, 6 processus) sur Redis.

---

## 7. Sécurité

| Mesure | Mise en œuvre |
|---|---|
| **Authentification API** | Jetons Sanctum, révocables |
| **Double facteur** | OTP par SMS (Twilio) à l'inscription et à la connexion |
| **Limitation de fréquence** | Sur la connexion et la vérification OTP |
| **Cloisonnement du panneau web** | Middleware `staff` — un compte client authentifié ne peut atteindre aucune page d'administration |
| **Anti-IDOR** | L'accès aux ressources d'un client est restreint à son propriétaire ou au staff |
| **Permissions fines** | ~50 permissions unitaires, attribuées par rôle (Spatie) |
| **Agrégations** | Colonnes et fonctions d'agrégation sur liste blanche, pas d'injection possible |
| **Journal d'audit** | `spatie/laravel-activitylog` sur les entités sensibles |
| **Secrets** | Hors dépôt, exclusivement dans le `.env` du serveur |

> Un ancien point d'entrée de callback de paiement non authentifié a été supprimé
> (exploitable). **Il ne doit jamais être réintroduit** : la confirmation de paiement
> passe uniquement par la vérification active côté serveur.

---

## 8. Infrastructure et déploiement

### 8.1 Serveur de production

| Élément | Valeur |
|---|---|
| Domaine | `isogaz.net` |
| Hébergement | VPS Contabo, Ubuntu 22.04 |
| Chemin applicatif | `/var/www/petrolex` |
| Serveur web | Nginx → PHP 8.2-FPM |
| Base de données | MySQL 8 |
| Cache / files | Redis |
| Processus longs | Supervisor : `petrolex-worker:*` (6), `petrolex-reverb` |
| Propriété des fichiers | `isogaz-ptlx:www-data` |

### 8.2 Procédure de déploiement

La production **est un dépôt Git**. Le déploiement se fait **uniquement par tag versionné** —
jamais par branche ; le script refuse toute référence qui n'est pas de la forme `vX.Y.Z`.

```bash
git tag -a v1.X.Y <commit> -m "Description de la version"
git push github v1.X.Y
./deploy-from-local.sh v1.X.Y
```

Séquence exécutée côté serveur :

1. Passage en **mode maintenance**
2. `git fetch github` + `git reset --hard <tag>`
3. `composer install --no-dev`
4. `php artisan migrate --force`
5. Ré-assertion des permissions sur `resources/lang`
6. Reconstruction des caches (config, routes, vues)
7. `php artisan queue:restart`
8. `systemctl reload php8.2-fpm` *(indispensable : OPcache périmé ⇒ erreurs 500)*
9. `supervisorctl restart all`
10. Sortie du mode maintenance + contrôle de santé

**Interdits formels :**
- Déploiement par rsync ou copie de fichiers (méthode historique, supprimée).
- `migrate:fresh` dans un déploiement. Une réinitialisation de base est une opération
  manuelle, précédée d'une sauvegarde.
- Toute écriture en production sans validation explicite préalable.

### 8.3 Environnements

| Environnement | Usage |
|---|---|
| Local | Développement |
| Staging | Recette (`deploy-staging.sh`) |
| Production | `isogaz.net`, déploiement par tag |

---

## 9. Points de vigilance

### 9.1 Données géographiques et applications mobiles
Les applications Flutter sont fortement typées : une valeur nulle inattendue provoque une
erreur fatale (`type 'Null' is not a subtype of…`). **Toute donnée géographique exposée à
l'API mobile doit être non nulle.** Les coordonnées des quartiers sont donc `NOT NULL` en
base, obligatoires dans le formulaire d'administration et complètes dans les seeders — ce
verrouillage à trois niveaux est délibéré.

### 9.2 Observabilité mobile
Les plantages mobiles ne remontent nulle part par défaut. Les applications doivent poster
leurs erreurs sur `POST /api/app/logs`, consultables dans l'administration (*Logs mobiles*).

### 9.3 Envois d'e-mail et transactions
**Ne jamais envoyer un e-mail à l'intérieur d'une transaction de paiement** : un échec
d'envoi provoquerait le rollback du paiement. Les e-mails sont émis après validation de
la transaction (incident corrigé).

### 9.4 Fichier de traductions au runtime
`resources/lang/fr.json` est écrit par l'application en fonctionnement. Il doit rester
accessible en écriture à `www-data` ; une ACL par défaut est posée et le script de
déploiement la ré-assère.

### 9.5 Données de référence
En environnement de production, les seeders n'alimentent **que** les données de référence :
géographie, rôles, super administrateur, comptes de test, types de bouteilles et
d'accessoires, catégories, centre par défaut (Logbessou), versions d'application. Les
commandes, paiements, bouteilles et produits réels sont créés à l'exécution et ne sont
jamais seedés. Le seeder de types de bouteilles ne contient que la **9 Kg** ; les 12/15 Kg
sont des ajouts manuels via l'administration.

---

## 10. Qualité et outillage

| Objectif | Commande |
|---|---|
| Vérifier le style (sans modifier) | `composer pint -- --test` |
| Corriger le style | `composer pint` (ou `composer format` : PHP + Blade) |
| Analyse statique | `composer phpstan` (Larastan, niveau 3) |
| Vérifier la syntaxe d'un fichier | `php -l <fichier>` |
| Lancer les tests | `php artisan test` |
| Lancer un test ciblé | `php artisan test --filter=NomDuTest` |
| Lister les routes | `php artisan route:list` |
| Vider les caches | `php artisan optimize:clear` |

**Règle de contribution :** après toute modification PHP, exécuter `php -l` puis
`composer phpstan` sur les fichiers touchés ; avant tout commit, `composer pint -- --test`
doit être propre. Les développements se font hors de la branche `master`.

---

## 11. Documentation associée dans le dépôt

| Fichier | Contenu |
|---|---|
| `CLAUDE.md` | Point d'entrée du projet et commandes |
| `.claude/rules/architecture.md` | Structure en couches détaillée |
| `.claude/rules/gotchas.md` | Pièges critiques |
| `.claude/rules/code-style.md` | Standards de code |
| `.claude/rules/production.md` | Serveur et circuit de déploiement |
| `documentation/` | Annotations OpenAPI par domaine (source du Swagger) |
| `docs/ORANGE_MONEY.md` | Intégration Orange Money |
| `BESOIN-MOBILE.md`, `BESOIN-MOBILE-LIVREUR.md` | Spécifications des applications mobiles |
| `CHANGELOG.md` | Historique des versions |
