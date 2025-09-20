# TODO: Refactoring Complet du Flow de Création de Commande

## 🎯 Objectif
Refactorer complètement le système de création de commande pour respecter les standards du projet avec une architecture propre, des validations robustes, et un endpoint de callback de paiement.

## 📋 Plan d'Implémentation

### 1. **Création du Request Handler**
```php
// app/Http/Api/Requests/Order/CreateOrderRequest.php
```
**Fonctionnalités** :
- Validation complète des données d'entrée
- Règles de validation dynamiques avec les enums
- Messages d'erreur multilingues (FR/EN)
- Validation des items avec quantités
- Validation de l'adresse de livraison appartenant au client
- Validation du centre de distribution disponible

**Champs attendus** :
```php
[
    'delivery_address_id' => 'required|exists:customer_delivery_addresses,id',
    'distribution_center_id' => 'required|exists:distribution_centers,id',
    'delivery_type' => 'required|in:normal,fast',
    'payment_method' => 'required|in:orange_money,mtn_money,credit_card',
    'items' => 'required|array|min:1',
    'items.*.product_category_id' => 'required|exists:product_categories,id',
    'items.*.quantity' => 'required|integer|min:1',
    'items.*.option' => 'nullable|in:new,return',
    'comments' => 'nullable|string|max:500',
]
```

### 2. **Contrôleur Dédié**
```php
// app/Http/Api/Controllers/Order/CreateOrderController.php
```
**Responsabilités** :
- Recevoir et valider la requête
- Créer le DTO à partir des données validées
- Appeler le service métier
- Retourner la réponse formatée

**Structure** :
```php
public function __invoke(CreateOrderRequest $request): CreateOrderResponse
{
    $customer = $request->user()->customer;
    $orderDTO = CreateOrderDTO::from($request->validated());
    
    $result = $this->orderService->createOrder($customer, $orderDTO);
    
    return CreateOrderResponse::withOrderAndPayment($result['order'], $result['payment']);
}
```

### 3. **DTO (Data Transfer Object)**
**Le DTO existe déjà** : `app/DTOs/Order/CreateOrderDTO.php`
**Structure actuelle** :
```php
class CreateOrderDTO extends BaseDTO
{
    public function __construct(
        public int $customer_id,
        public int $delivery_address_id,
        public int $distribution_center_id,
        #[WithCast(SpatieEnumCast::class, DeliveryType::class)]
        public DeliveryType $delivery_type,
        #[WithCast(SpatieEnumCast::class, PaymentMethod::class)]
        public PaymentMethod $payment_method,
        /** @var OrderItemDTO[] */
        public array $items
    ) {}
}
```

**Le OrderItemDTO existe déjà** : `app/DTOs/Order/OrderItemDTO.php`
```php
class OrderItemDTO extends Data
{
    public function __construct(
        public int $product_category_id,
        public int $quantity,
        public ?float $unit_price,
        public ?float $total_price,
        #[WithCast(SpatieEnumCast::class, BottleOrderType::class)]
        public ?BottleOrderType $option
    ) {}
}
```

### 4. **Service Métier Refactorisé**
```php
// app/Services/Order/OrderService.php (méthode createOrder)
```
**Responsabilités** :
- Validation métier (stock, disponibilité, cohérence)
- Calcul des prix et frais de livraison
- Création de la commande et des items
- Génération du numéro de commande unique
- Initiation du processus de paiement
- Gestion transactionnelle

**Flow** :
1. **Validation métier** : Vérifier stock, adresse client, centre disponible
2. **Calcul des montants** : Prix unitaires, sous-total, frais livraison, total
3. **Création commande** : Avec statut `pending` et numéro unique
4. **Création items** : Associer les produits à la commande
5. **Initiation paiement** : Via PaymentService refactorisé
6. **Retour résultat** : Order + OrderPayment

### 5. **PaymentService Refactorisé**
```php
// app/Services/PaymentService.php
```
**Améliorations** :
- Respect des colonnes existantes en DB
- Gestion des erreurs robuste
- Interface claire avec les gateways

**Méthodes** :
```php
public function initiatePayment(Order $order, PaymentMethod $method): OrderPayment;
public function handleCallback(string $reference, array $data): void;
```

### 6. **Endpoint de Callback de Paiement**

#### **Contrôleur de Callback**
```php
// app/Http/Api/Controllers/Payment/PaymentCallbackController.php
```
**Endpoint** : `POST /api/payments/callback`

**Fonctionnalités** :
- Recevoir les callbacks des gateways de paiement
- Valider la référence de paiement
- Déclencher le processus de confirmation
- Mettre à jour les statuts de commande et paiement

#### **Request de Callback**
```php
// app/Http/Api/Requests/Payment/PaymentCallbackRequest.php
```
**Validation** :
```php
[
    'payment_reference' => 'required|exists:order_payments,payment_reference',
    'status' => 'required|in:success,failed,pending',
    'amount' => 'required|numeric|min:0',
    'transaction_id' => 'nullable|string',
]
```

### 7. **Response Classes Dédiées**
```php
// app/Http/Api/Responses/Order/CreateOrderResponse.php
// app/Http/Api/Responses/Payment/PaymentCallbackResponse.php
```

### 8. **Documentation Swagger**
```php
// documentation/Order/CreateOrderControllerDoc.php
// documentation/Payment/PaymentCallbackControllerDoc.php
```

### 9. **Tests Complets**
```php
// tests/Feature/Endpoints/CreateOrderTest.php
// tests/Feature/Endpoints/PaymentCallbackTest.php
```

**Scénarios de test** :
- ✅ Création de commande réussie
- ✅ Validation des données d'entrée
- ✅ Calcul correct des montants
- ✅ Initiation du paiement
- ✅ Callback de succès
- ✅ Callback d'échec
- ✅ Gestion des erreurs métier

## 🔄 Flow Détaillé Final

### **Étape 1 : Création de Commande**
```
POST /api/orders
Content-Type: application/json
Authorization: Bearer {token}

{
    "delivery_address_id": 1,
    "distribution_center_id": 1,
    "delivery_type": "normal",
    "payment_method": "orange_money",
    "items": [
        {
            "product_category_id": 1,
            "quantity": 2,
            "bottle_type": "new"
        }
    ],
    "comments": "Livrer avant 18h"
}
```

**Réponse** :
```json
{
    "_metadata": {
        "success": true,
        "message": "Commande créée avec succès"
    },
    "data": {
        "order": {
            "id": 123,
            "order_number": "ORD-2025-001234",
            "status": "pending",
            "total_amount": 23500,
            "payment_reference": "PETROLEX_67890"
        },
        "payment": {
            "payment_reference": "PETROLEX_67890",
            "payment_status": "pending",
            "payment_url": "https://gateway.orange.cm/pay/xyz",
            "amount_due": 23500
        }
    }
}
```

### **Étape 2 : Callback de Paiement**
```
POST /api/payments/callback
Content-Type: application/json

{
    "payment_reference": "PETROLEX_67890",
    "status": "success",
    "amount": 23500,
    "transaction_id": "TXN_ABC123"
}
```

**Réponse** :
```json
{
    "_metadata": {
        "success": true,
        "message": "Callback de paiement traité avec succès"
    },
    "data": {
        "payment_reference": "PETROLEX_67890",
        "payment_status": "paid",
        "order_status": "confirmed"
    }
}
```

## 🏗️ Architecture Finale

```
POST /api/orders
├── CreateOrderController
│   ├── CreateOrderRequest (validation)
│   ├── CreateOrderDTO (data transfer)
│   └── OrderService::createOrder()
│       ├── Business validation
│       ├── Price calculation
│       ├── Order creation
│       ├── OrderItems creation
│       └── PaymentService::initiatePayment()
│           └── OrderPayment creation
└── CreateOrderResponse

POST /api/payments/callback
├── PaymentCallbackController
│   ├── PaymentCallbackRequest
│   └── PaymentService::handleCallback()
│       ├── Update OrderPayment
│       └── Update Order status
└── PaymentCallbackResponse
```

## 📋 Checklist d'Implémentation

- [ ] Créer CreateOrderRequest avec validations complètes
- [ ] Créer CreateOrderController suivant les standards
- [ ] Les DTOs existent déjà (CreateOrderDTO et OrderItemDTO)
- [ ] Refactorer OrderService::createOrder()
- [ ] Corriger PaymentService (colonnes DB existantes)
- [ ] Créer PaymentCallbackController
- [ ] Créer PaymentCallbackRequest
- [ ] Créer les Response classes
- [ ] Ajouter documentation Swagger
- [ ] Créer tests complets
- [ ] Mettre à jour les routes
- [ ] Tester le flow end-to-end

## 🎯 Priorités

1. **CRITIQUE** : Correction des colonnes DB dans PaymentService
2. **HAUTE** : Création des classes de base (Request, Controller)
3. **HAUTE** : Implémentation du service métier
4. **MOYENNE** : Endpoint de callback de paiement
5. **BASSE** : Documentation et tests

Ce plan respecte tous les standards du projet :
- DTOs avec snake_case et BaseDTO (Spatie Data)
- PaymentService sans méthodes de simulation visibles
- Endpoint de callback standard `/api/payments/callback`
- Architecture propre et maintenable pour la création de commandes