# 🧪 Suite Complète de Tests API Production

Cette suite contient **2 scripts complets** pour tester **TOUS les endpoints** de l'API Isogaz en production.

## 📦 Scripts Disponibles

### 1. Script Client (`test_customer_api.sh`)
Teste **35 endpoints** pour l'application client.

### 2. Script Livreur (`test_delivery_person_api.sh`)
Teste **20 endpoints** pour l'application livreur.

---

## 🚀 Utilisation Rapide

### Test Client
```bash
bash tests/Production/test_customer_api.sh
```

### Test Livreur
```bash
bash tests/Production/test_delivery_person_api.sh
```

---

## 📋 Endpoints Testés

### 🛒 Script Client (35 tests)

#### Authentification (4 tests)
1. ✅ Login
2. ✅ Get User Profile
3. ✅ Check Authentication
4. ✅ Logout

#### Géographie (5 tests)
5. ✅ Get Countries
6. ✅ Get Cities by Country
7. ✅ Get City Details
8. ✅ Get Neighborhoods by City
9. ✅ Get Neighborhood Details

#### Centres de Distribution (3 tests)
10. ✅ Get Distribution Centers
11. ✅ Get Closest Distribution Center
12. ✅ Get Products by Distribution Center

#### Livraison & Paiement (2 tests)
13. ✅ Get Delivery Types
14. ✅ Get Payment Methods

#### Adresses de Livraison (2 tests)
15. ✅ Create Delivery Address
16. ✅ Update Delivery Address

#### Commandes (7 tests)
17. ✅ Get My Orders
18. ✅ Create Order
19. ✅ Get Order Details
20. ✅ Download Invoice
21. ✅ Initiate Payment
22. ✅ Add Customer Feedback
23. ✅ Cancel Order

#### Gestion du Profil (2 tests)
24. ✅ Update Profile
25. ✅ Update Password

#### Bouteilles (1 test)
26. ✅ Verify Bottle Barcode

#### Application (5 tests)
27. ✅ Get App Version
28. ✅ Get Privacy Policy
29. ✅ Get Terms and Conditions
30. ✅ Get Support Contact
31. ✅ Get Advertising Banners

#### Autres (4 tests)
32. ✅ Health Check
33. ✅ Get Filtered Orders
34. ✅ Get Paginated Orders
35. ✅ API Documentation

---

### 🚚 Script Livreur (20 tests)

#### Authentification (2 tests)
1. ✅ Login (Delivery Person)
2. ✅ Get Delivery Person Profile

#### Commandes Assignées (5 tests)
3. ✅ Get Assigned Orders
4. ✅ Get Filtered Orders (by status)
5. ✅ Get Paginated Orders
6. ✅ Get Order Details
7. ✅ Deliver Order

#### Suivi de Livraison (5 tests)
8. ✅ Start Delivery Tracking
9. ✅ Update Delivery Position
10. ✅ Get Tracking Details
11. ✅ Complete Delivery Tracking
12. ✅ Scan Empty Bottle

#### Bouteilles (1 test)
13. ✅ Verify Bottle Barcode

#### Géographie (1 test)
14. ✅ Get Countries

#### Centres de Distribution (1 test)
15. ✅ Get Distribution Centers

#### Gestion du Profil (2 tests)
16. ✅ Update Profile
17. ✅ Update Password

#### Application (1 test)
18. ✅ Get App Version

#### Autres (2 tests)
19. ✅ Check Authentication
20. ✅ Logout

---

## ⚙️ Configuration

### Script Client
Par défaut, utilise:
```bash
EMAIL="customer1@test.com"
PASSWORD="password"
```

### Script Livreur
**⚠️ IMPORTANT**: Modifier les credentials dans le script:
```bash
EMAIL="delivery1@test.com"  # À MODIFIER
PASSWORD="password"          # À MODIFIER
```

Le script vérifie automatiquement que le compte a le rôle `delivery_person`.

---

## 📊 Résultat Attendu

### Script Client
```
================================================
  CUSTOMER API TEST - ISOGAZ
  Testing: https://isogaz.afrik-solutions.com
================================================

[1/35] Testing Login...
✅ Login successful! Token obtained.

[2/35] Getting user profile...
✅ User profile retrieved (ID: 128)

...

[35/35] Logging out...
✅ Logout successful

================================================
  TEST SUMMARY
================================================
✅ Passed: 35
❌ Failed: 0
Total Tests: 35

🎉 ALL CUSTOMER TESTS PASSED!
```

### Script Livreur
```
================================================
  DELIVERY PERSON API TEST - ISOGAZ
  Testing: https://isogaz.afrik-solutions.com
================================================

[1/20] Testing Login (Delivery Person)...
✅ Login successful as delivery person!

[2/20] Getting delivery person profile...
✅ Profile retrieved (Delivery Person ID: 5)

...

[20/20] Logging out...
✅ Logout successful

================================================
  TEST SUMMARY
================================================
✅ Passed: 20
❌ Failed: 0
Total Tests: 20

🎉 ALL DELIVERY PERSON TESTS PASSED!
```

---

## 🔧 Prérequis

- `curl` installé
- `jq` installé (pour parser le JSON)
- Connexion internet
- **Pour script livreur**: compte avec rôle `delivery_person`

### Installation de jq (si nécessaire)

```bash
# Ubuntu/Debian
sudo apt-get install jq

# macOS
brew install jq
```

---

## 🐛 Dépannage

### Script Client

#### Erreur "Login failed"
1. Vérifier que le compte `customer1@test.com` existe
2. Vérifier le mot de passe
3. Vérifier que l'API est accessible: `curl https://isogaz.afrik-solutions.com/api/health`

#### Erreur "Failed to create order"
1. Vérifier que le compte a une adresse de livraison (ID: 1)
2. Vérifier que le centre de distribution (ID: 1) existe
3. Vérifier que les prix sont configurés dans la base de données

### Script Livreur

#### Erreur "User is not a delivery person"
Le compte utilisé n'a pas le rôle `delivery_person`. Solution:
1. Créer un compte livreur via l'interface admin
2. OU utiliser un compte existant avec le bon rôle
3. Mettre à jour les credentials dans le script

#### Erreur "No assigned orders"
C'est **normal** pour un nouveau livreur. Les tests qui nécessitent une commande seront automatiquement sautés avec un avertissement:
```
⚠️  Skipped - no order available
```

---

## 📝 Notes Importantes

### Script Client
- Crée une **commande de test** en production
- Crée une **adresse de livraison** temporaire
- La commande créée est **annulée** à la fin du test
- Tests **read-only** pour la plupart

### Script Livreur
- Nécessite des **commandes assignées** pour tester complètement
- Si aucune commande n'est assignée, certains tests seront sautés
- Les tests de tracking nécessitent une commande en cours
- Tests **partiellement destructifs** (marque commandes comme livrées)

---

## 🎯 Couverture des Tests

| Catégorie | Client | Livreur | Total |
|-----------|--------|---------|-------|
| Authentification | 4 | 2 | 6 |
| Commandes | 7 | 5 | 12 |
| Géographie | 5 | 1 | 6 |
| Distribution Centers | 3 | 1 | 4 |
| Tracking | 0 | 5 | 5 |
| Profil | 2 | 2 | 4 |
| Bouteilles | 1 | 1 | 2 |
| Application | 5 | 1 | 6 |
| Autres | 8 | 2 | 10 |
| **TOTAL** | **35** | **20** | **55** |

---

## 🔄 Comparaison avec l'ancien script

### Ancien Script (`test_production_api.sh`)
- ❌ 12 tests seulement
- ❌ Pas de tests livreur
- ❌ Pas de tests complets de commandes
- ❌ Pas de tests de tracking

### Nouveaux Scripts
- ✅ 55 tests au total
- ✅ Tests client complets (35)
- ✅ Tests livreur complets (20)
- ✅ Tests de toutes les fonctionnalités critiques
- ✅ Création/annulation de commandes
- ✅ Suivi de livraison complet
- ✅ Scan de bouteilles

---

## 🚀 Prochaines Étapes

Pour une couverture complète, ajouter:
1. Tests des callbacks de paiement (MTN, Orange Money)
2. Tests de réception de notifications WebSocket
3. Tests de performance/charge
4. Tests de sécurité (injection, XSS, etc.)
5. Tests de webhooks tiers

---

## 📞 Support

En cas de problème:
1. Vérifier les logs Laravel: `/var/www/isogaz/storage/logs/`
2. Vérifier la connectivité: `curl https://isogaz.afrik-solutions.com/api/health`
3. Vérifier les credentials dans le script
4. Consulter la documentation Swagger: `https://isogaz.afrik-solutions.com/api/documentation`
