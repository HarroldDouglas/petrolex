# Production API Test Suite

Ce dossier contient les scripts de test pour l'API en production.

## 🚀 Utilisation

### Test complet de l'API en production

```bash
bash tests/Production/test_production_api.sh
```

## 📋 Ce qui est testé

Le script teste **12 endpoints critiques** avec authentification réelle :

1. **Login** - Connexion utilisateur
2. **Get User Profile** - Récupération du profil
3. **Auth Check** - Vérification de l'authentification
4. **Countries** - Liste des pays
5. **Delivery Types** - Types de livraison
6. **Payment Methods** - Méthodes de paiement
7. **Distribution Centers** - Centres de distribution
8. **My Orders** - Commandes de l'utilisateur
9. **Create Order** - Création d'une commande complète
10. **Get Order Details** - Récupération des détails d'une commande
11. **App Version** - Version de l'application
12. **Logout** - Déconnexion

## ⚙️ Configuration

Les credentials par défaut sont dans le script :

```bash
EMAIL="customer1@test.com"
PASSWORD="password"
```

Pour utiliser d'autres credentials, modifiez ces valeurs dans le script ou créez votre propre compte via l'API de registration.

## 📊 Résultat attendu

```
================================================
  PRODUCTION API TEST - ISOGAZ
  Testing: https://isogaz.afrik-solutions.com
================================================

[1/12] Testing Login...
✅ Login successful! Token obtained.

[2/12] Getting user profile...
✅ User profile retrieved

...

[9/12] Testing order creation...
✅ Order created successfully (ID: 302)

[10/12] Getting order details...
✅ Order details retrieved

...

================================================
  TEST SUMMARY
================================================
✅ Passed: 12
❌ Failed: 0
Total Tests: 12

🎉 ALL TESTS PASSED! Production API is working correctly.
```

## 🔧 Prérequis

- `curl` installé
- `jq` installé (pour parser le JSON)
- Connexion internet

### Installation de jq (si nécessaire)

```bash
# Ubuntu/Debian
sudo apt-get install jq

# macOS
brew install jq
```

## 🐛 Dépannage

### Erreur "Cannot authenticate"

Si le login échoue, le script tente automatiquement de créer un compte de test. Si cela échoue aussi, vérifiez :

1. L'API est accessible : `curl https://isogaz.afrik-solutions.com/api/health`
2. Les credentials sont corrects
3. Le compte n'est pas bloqué

### Erreur "jq: command not found"

Installez jq avec les commandes ci-dessus.

## 📝 Notes

- Ce script crée une **commande de test** en production (qui peut être supprimée ensuite)
- La plupart des tests sont **read-only**
- Le script peut être lancé **depuis n'importe où** (pas besoin d'être sur le serveur)
- Une adresse de livraison par défaut (ID: 1) doit exister pour customer1@test.com
