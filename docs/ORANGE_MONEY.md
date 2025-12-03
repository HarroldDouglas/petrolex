# Orange Money API Integration

## Vue d'ensemble

L'API Orange Money utilise un flux en **4 étapes** pour les paiements par push USSD:

```
1. TOKEN    → Obtenir un access token OAuth2
2. INIT     → Initialiser le paiement (obtenir payToken)
3. PAY      → Lancer le paiement (envoyer push USSD au client)
4. STATUS   → Vérifier le statut du paiement
```

## Configuration

### Variables d'environnement (.env)

```env
# Orange Money API Configuration (Sandbox)
OM_BASE_URL=https://api-s1.orange.cm
OM_CLIENT_ID=5xpOluguHcEp6XGLZue3JQII2tsa
OM_CLIENT_SECRET=O_6smU_H1AOrRAnQdG72hX5m8I8a
OM_API_USERNAME=OMSANDBOXAPI
OM_API_PASSWORD=OMS@NDBOX@PI
OM_CHANNEL_USER_MSISDN=691301143
OM_PIN=2222
OM_DESCRIPTION="Paiement Petrolex"
OM_NOTIF_URL="${APP_URL}/api/payment/callback/orange"
```

### Fichier de configuration (config/orangemoney.php)

```php
<?php

return [
    'base_url' => env('OM_BASE_URL', 'https://api-s1.orange.cm'),
    'client_id' => env('OM_CLIENT_ID'),
    'client_secret' => env('OM_CLIENT_SECRET'),
    'api_username' => env('OM_API_USERNAME'),
    'api_password' => env('OM_API_PASSWORD'),
    'channel_user_msisdn' => env('OM_CHANNEL_USER_MSISDN'),
    'pin' => env('OM_PIN'),
    'description' => env('OM_DESCRIPTION', 'Paiement Petrolex'),
    'notif_url' => env('OM_NOTIF_URL'),

    'endpoints' => [
        'token' => '/token',
        'init' => '/omcoreapis/1.0.2/mp/init',
        'pay' => '/omcoreapis/1.0.2/mp/pay',
        'status' => '/omcoreapis/1.0.2/mp/paymentstatus/',
    ],
];
```

---

## Tests avec cURL

### Étape 1: Obtenir le Token

```bash
curl -s -X POST "https://api-s1.orange.cm/token" \
  -H "Authorization: Basic NXhwT2x1Z3VIY0VwNlhHTFp1ZTNKUUlJMnRzYTpPXzZzbVVfSDFBT3JSQW5RZEc3MmhYNW04SThh" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=client_credentials" | jq .
```

**Réponse:**
```json
{
  "access_token": "eyJ4NXQiOiJPREU...",
  "scope": "am_application_scope default",
  "token_type": "Bearer",
  "expires_in": 3600
}
```

### Étape 2: Initialiser le Paiement

```bash
# Remplacer ACCESS_TOKEN par le token obtenu à l'étape 1
ACCESS_TOKEN="eyJ4NXQiOiJPREU..."

curl -s -X POST "https://api-s1.orange.cm/omcoreapis/1.0.2/mp/init" \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "X-AUTH-TOKEN: $(echo -n 'OMSANDBOXAPI:OMS@NDBOX@PI' | base64)" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "amount": "100",
    "currency": "XAF",
    "orderId": "TEST_'$(date +%Y%m%d%H%M%S)'",
    "description": "Test Petrolex"
  }' | jq .
```

**Réponse:**
```json
{
  "status": 200,
  "message": "OK",
  "data": {
    "payToken": "AB1234567890CDEF"
  }
}
```

### Étape 3: Lancer le Paiement (Push USSD)

```bash
# Remplacer PAY_TOKEN par le payToken obtenu à l'étape 2
PAY_TOKEN="AB1234567890CDEF"
PHONE="655332183"

curl -s -X POST "https://api-s1.orange.cm/omcoreapis/1.0.2/mp/pay" \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "X-AUTH-TOKEN: $(echo -n 'OMSANDBOXAPI:OMS@NDBOX@PI' | base64)" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "notifUrl": "https://petrolex.test/api/payment/callback/orange",
    "channelUserMsisdn": "691301143",
    "amount": "100",
    "subscriberMsisdn": "'$PHONE'",
    "pin": "2222",
    "orderId": "TEST_'$(date +%Y%m%d%H%M%S)'",
    "description": "Test Petrolex",
    "payToken": "'$PAY_TOKEN'"
  }' | jq .
```

**Réponse (push envoyé):**
```json
{
  "status": 200,
  "message": "OK",
  "data": {
    "txnid": "MP241203.1234.A56789",
    "status": "PENDING"
  }
}
```

### Étape 4: Vérifier le Statut

```bash
curl -s -X GET "https://api-s1.orange.cm/omcoreapis/1.0.2/mp/paymentstatus/$PAY_TOKEN" \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "X-AUTH-TOKEN: $(echo -n 'OMSANDBOXAPI:OMS@NDBOX@PI' | base64)" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" | jq .
```

**Réponse (paiement réussi):**
```json
{
  "status": 200,
  "message": "OK",
  "data": {
    "status": "SUCCESSFULL",
    "txnid": "MP241203.1234.A56789",
    "orderId": "TEST_20241203120000",
    "amount": "100",
    "confirmtxnmessage": "Transaction confirmed"
  }
}
```

---

## Script de Test Interactif

Un script PHP interactif est disponible pour tester le flux complet:

```bash
php test_orange.php
```

Ce script:
1. Demande le numéro de téléphone du client
2. Demande le montant (par défaut 100 FCFA)
3. Exécute les 4 étapes automatiquement
4. Affiche le statut en temps réel avec des couleurs
5. Attend la confirmation du paiement (polling)

---

## Statuts de Paiement

| Statut Orange | Description | Statut Petrolex |
|---------------|-------------|-----------------|
| `PENDING` | En attente de validation client | `PENDING` |
| `INITIATED` | Paiement initié | `PENDING` |
| `SUCCESSFULL` | Paiement réussi | `PAID` |
| `SUCCESS` | Paiement réussi (alternative) | `PAID` |
| `FAILED` | Paiement échoué | `FAILED` |
| `EXPIRED` | Délai expiré | `FAILED` |

---

## Headers Requis

### Authorization Header
- **Token endpoint**: `Basic {base64(client_id:client_secret)}`
- **Autres endpoints**: `Bearer {access_token}`

### X-AUTH-TOKEN Header
```
base64(api_username:api_password)
```

Exemple:
```bash
echo -n 'OMSANDBOXAPI:OMS@NDBOX@PI' | base64
# Résultat: T01TQU5EQk9YQVBJOk9NU0BOREJPWEBQSQ==
```

---

## Callback (Notification URL)

Orange Money peut envoyer une notification à l'URL spécifiée dans `notifUrl`.

**Endpoint Petrolex:**
```
POST /api/payment/callback/orange
```

**Payload attendu:**
```json
{
  "status": "SUCCESSFULL",
  "txnid": "MP241203.1234.A56789",
  "orderId": "ORD_123456",
  "amount": "5000",
  "payToken": "AB1234567890CDEF"
}
```

> **Note:** Le callback n'est pas toujours fiable en sandbox. Petrolex utilise le polling comme mécanisme principal via `VerifyPaymentStatusJob`.

---

## Numéros de Test (Sandbox)

Pour le sandbox Orange Money Cameroun:
- **Numéro marchand (channel):** 691301143
- **PIN marchand:** 2222
- **Numéro client test:** Utilisez votre propre numéro Orange

---

## Troubleshooting

### Erreur "OrderId must be at most 20 characters"
- L'orderId ne doit **jamais dépasser 20 caractères**
- Format recommandé: `PTX` + uniqid() = 16 caractères
- Exemple: `PTX6930537938f37`

### Erreur "Invalid credentials"
- Vérifiez que `X-AUTH-TOKEN` est correctement encodé en base64
- Le format est `api_username:api_password` (avec les deux points)

### Erreur "Token expired"
- Le token expire après 3600 secondes (1 heure)
- Le système cache le token pour 3500 secondes

### Le client ne reçoit pas le push USSD
- Vérifiez que le numéro est au format local (655332183, pas +237655332183)
- Le numéro doit être un numéro Orange Money actif

### Statut reste en PENDING
- Le client doit valider avec son code secret Orange Money
- Alternative: Composer `#150*50#` et suivre les instructions

---

## Flux de Paiement Petrolex

```
┌─────────────┐      ┌─────────────┐      ┌─────────────┐
│   Client    │      │   Petrolex  │      │  Orange CM  │
└──────┬──────┘      └──────┬──────┘      └──────┬──────┘
       │                    │                    │
       │  Initie paiement   │                    │
       │───────────────────>│                    │
       │                    │   GET /token       │
       │                    │───────────────────>│
       │                    │   access_token     │
       │                    │<───────────────────│
       │                    │                    │
       │                    │   POST /mp/init    │
       │                    │───────────────────>│
       │                    │   payToken         │
       │                    │<───────────────────│
       │                    │                    │
       │                    │   POST /mp/pay     │
       │                    │───────────────────>│
       │   Push USSD        │                    │
       │<───────────────────│────────────────────│
       │                    │   status: PENDING  │
       │                    │<───────────────────│
       │                    │                    │
       │  Valide (PIN)      │                    │
       │────────────────────│───────────────────>│
       │                    │                    │
       │                    │ GET /paymentstatus │
       │                    │───────────────────>│
       │                    │ status: SUCCESSFULL│
       │                    │<───────────────────│
       │                    │                    │
       │  Confirmation      │                    │
       │<───────────────────│                    │
       │                    │                    │
```

---

## Fichiers Clés

| Fichier | Description |
|---------|-------------|
| `app/Services/PaymentGateways/OrangeMoneyGateway.php` | Gateway principal |
| `config/orangemoney.php` | Configuration |
| `app/Jobs/VerifyPaymentStatusJob.php` | Polling du statut |
| `test_orange.php` | Script de test CLI |
| `.env` | Credentials |
