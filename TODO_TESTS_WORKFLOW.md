# 🚀 Tests de Workflow Métier - Feuille de Route

Ce document détaille tous les tests de workflow métier (end-to-end) à implémenter pour valider les processus business critiques de l'application Petrolex.

## 📋 **Vue d'Ensemble**

**Objectif :** Tester les scénarios complets d'utilisation qui impliquent plusieurs services, modèles et processus métier pour garantir que l'application fonctionne correctement dans des conditions réelles.

**Approche :** Tests d'intégration complets avec base de données réelle, sans mocking des services internes, mais avec mocking des services externes (SMS, email, APIs tierces).

---

## 🎯 **Tests à Implémenter**

### ✅ **1. CompleteOrderLifecycleTest**
**Fichier :** `tests/Feature/Workflows/CompleteOrderLifecycleTest.php`

**Description :** Teste le cycle de vie complet d'une commande depuis la création jusqu'à la livraison.

**Scénarios à tester :**

#### Test 1 : `test_complete_order_lifecycle_with_normal_delivery`
- **Setup :** Créer client, adresse livraison, centre distribution, livreur, produits
- **Actions :**
  1. Client crée une commande avec 2 bouteilles + 1 accessoire
  2. Vérifier statut PENDING
  3. Manager confirme la commande → statut PAID
  4. Système assigne automatiquement un livreur → statut PROCESSING
  5. Livreur démarre le tracking → DeliveryTracking créé
  6. Livreur met à jour position plusieurs fois
  7. Livreur complète la livraison → statut DELIVERED
- **Vérifications :**
  - Commande passe par tous les statuts correctement
  - OrderItems créés avec bons prix
  - DeliveryTracking créé et mis à jour
  - Notifications envoyées aux bonnes personnes
  - Stock décrémenté correctement
  - Timestamps mis à jour (confirmed_at, processing_at, delivered_at)

#### Test 2 : `test_order_cancellation_workflow`
- **Actions :**
  1. Créer commande → PENDING
  2. Manager confirme → PAID  
  3. Annuler avant assignation livreur → CANCELLED
- **Vérifications :**
  - cancelled_at timestamp défini
  - Stock restauré
  - Notifications d'annulation envoyées

#### Test 3 : `test_order_lifecycle_with_fast_delivery`
- **Actions :** Même que test 1 mais avec DeliveryType::FAST
- **Vérifications :** Frais de livraison différents, délais respectés

---

### ✅ **2. DeliveryTrackingWorkflowTest**
**Fichier :** `tests/Feature/Workflows/DeliveryTrackingWorkflowTest.php`

**Description :** Teste le système de suivi de livraison en temps réel.

**Scénarios à tester :**

#### Test 1 : `test_complete_delivery_tracking_workflow`
- **Setup :** Commande en statut PROCESSING avec livreur assigné
- **Actions :**
  1. Livreur démarre le tracking avec position initiale
  2. Vérifier création DeliveryTracking avec statut STARTED
  3. Livreur met à jour position 5 fois (simule trajet)
  4. Calculer progression, distance restante, ETA
  5. Livreur arrive à destination → statut COMPLETED
- **Vérifications :**
  - Toutes les positions enregistrées
  - Progression calculée correctement (0% → 100%)
  - ETA mis à jour à chaque position
  - Events WebSocket diffusés
  - Commande passe en DELIVERED à la fin

#### Test 2 : `test_delivery_tracking_with_route_deviation`
- **Actions :** Livreur dévie de la route prévue
- **Vérifications :** ETA recalculé, distance ajustée

#### Test 3 : `test_multiple_deliveries_concurrent_tracking`
- **Actions :** 3 livreurs trackent simultanément 3 commandes différentes
- **Vérifications :** Pas de collision de données, performances maintenues

---

### ✅ **3. CustomerRegistrationToFirstOrderTest**
**Fichier :** `tests/Feature/Workflows/CustomerRegistrationToFirstOrderTest.php`

**Description :** Teste le parcours complet d'un nouveau client.

**Scénarios à tester :**

#### Test 1 : `test_customer_complete_onboarding_workflow`
- **Actions :**
  1. Client s'inscrit avec email/téléphone
  2. Vérification OTP email
  3. Client complète son profil
  4. Client ajoute adresse de livraison
  5. Client parcourt catalogue produits
  6. Client crée sa première commande
  7. Client reçoit confirmation
- **Vérifications :**
  - Customer créé avec bon statut
  - OTP validé et supprimé
  - CustomerDeliveryAddress liée
  - Première commande créée correctement
  - Notifications de bienvenue envoyées

#### Test 2 : `test_customer_registration_with_phone_verification`
- **Actions :** Même workflow mais avec vérification SMS
- **Mock :** TwilioService pour éviter vrais SMS

---

### ✅ **4. PaymentToDeliveryWorkflowTest**
**Fichier :** `tests/Feature/Workflows/PaymentToDeliveryWorkflowTest.php`

**Description :** Teste l'intégration paiement → traitement → livraison.

**Scénarios à tester :**

#### Test 1 : `test_successful_payment_triggers_processing`
- **Actions :**
  1. Commande créée avec paiement CREDIT_CARD
  2. Simulation paiement réussi
  3. Vérifier commande passe en PAID automatiquement
  4. Livreur assigné automatiquement
  5. Processus livraison démarre
- **Vérifications :**
  - Transitions de statut automatiques
  - Livreur le moins occupé sélectionné

#### Test 2 : `test_failed_payment_workflow`
- **Actions :** Simulation échec paiement
- **Vérifications :** Commande reste PENDING, notifications d'échec

#### Test 3 : `test_mobile_money_payment_workflow`
- **Actions :** Test avec PaymentMethod::MOBILE_MONEY
- **Mock :** Services de paiement mobile

---

### ✅ **5. StockManagementWorkflowTest**
**Fichier :** `tests/Feature/Workflows/StockManagementWorkflowTest.php`

**Description :** Teste la gestion automatique des stocks.

**Scénarios à tester :**

#### Test 1 : `test_stock_depletion_and_restocking_workflow`
- **Setup :** Produit avec stock = 5
- **Actions :**
  1. Créer 3 commandes consommant tout le stock
  2. Vérifier stock = 0
  3. Tentative commande supplémentaire → échec
  4. Livraison fournisseur → stock restauré
  5. Nouvelle commande possible
- **Vérifications :**
  - Stock décrémenté à chaque commande
  - Commandes bloquées si stock insuffisant
  - Alerts manager envoyées

#### Test 2 : `test_concurrent_orders_stock_race_condition`
- **Actions :** 10 commandes simultanées sur produit avec stock=5
- **Vérifications :** Seulement 5 commandes acceptées, pas de stock négatif

---

### ✅ **6. MultiUserConcurrencyWorkflowTest**
**Fichier :** `tests/Feature/Workflows/MultiUserConcurrencyWorkflowTest.php`

**Description :** Teste les scénarios multi-utilisateurs simultanés.

**Scénarios à tester :**

#### Test 1 : `test_multiple_customers_ordering_simultaneously`
- **Actions :** 20 clients créent des commandes en même temps
- **Vérifications :** Toutes les commandes traitées, pas de corruption données

#### Test 2 : `test_delivery_person_assignment_concurrency`
- **Actions :** 10 commandes créées simultanément avec 3 livreurs disponibles
- **Vérifications :** Répartition équitable, pas de double assignation

---

### ✅ **7. NotificationWorkflowTest**
**Fichier :** `tests/Feature/Workflows/NotificationWorkflowTest.php`

**Description :** Teste le système complet de notifications.

**Scénarios à tester :**

#### Test 1 : `test_order_status_change_notifications_workflow`
- **Actions :** Commande passe par tous les statuts
- **Vérifications :**
  - Client reçoit notifications à chaque étape
  - Manager reçoit notifications appropriées
  - Livreur reçoit notifications d'assignation
  - Format notifications correct

#### Test 2 : `test_delivery_tracking_real_time_notifications`
- **Actions :** Suivi livraison avec mises à jour position
- **Vérifications :** Client reçoit mises à jour temps réel

---

### ✅ **8. ErrorHandlingWorkflowTest**
**Fichier :** `tests/Feature/Workflows/ErrorHandlingWorkflowTest.php`

**Description :** Teste la gestion des erreurs dans les workflows.

**Scénarios à tester :**

#### Test 1 : `test_order_creation_rollback_on_failure`
- **Actions :** Simuler échec lors création OrderItems
- **Vérifications :** Transaction rollback, pas de données corrompues

#### Test 2 : `test_delivery_tracking_resilience_to_failures`
- **Actions :** Simuler perte réseau pendant tracking
- **Vérifications :** Données préservées, reprise possible

---

## 📊 **Métriques de Succès**

Pour chaque test, on validera :
- ✅ **Intégrité des données** : Pas de corruption, contraintes respectées
- ✅ **Performance** : Temps d'exécution raisonnables (<5s par test)
- ✅ **Couverture** : Tous les chemins critiques testés
- ✅ **Robustesse** : Gestion correcte des cas d'erreur
- ✅ **Notifications** : Messages envoyés aux bons utilisateurs
- ✅ **État final** : Système dans l'état attendu après chaque workflow

---

## 🛠 **Configuration Technique**

**Base de données :** SQLite en mémoire pour rapidité  
**Mocking :** Services externes uniquement (Twilio, emails)  
**Events :** Tests avec events réels (pas de fake)  
**Cache :** Redis mocké ou désactivé  
**Files d'attente :** Synchrone pour tests  

---

## 📅 **Planning d'Implémentation**

**Phase 1 (Priorité Haute) :**
1. CompleteOrderLifecycleTest
2. DeliveryTrackingWorkflowTest  
3. CustomerRegistrationToFirstOrderTest

**Phase 2 (Priorité Moyenne) :**
4. PaymentToDeliveryWorkflowTest
5. StockManagementWorkflowTest

**Phase 3 (Priorité Faible) :**
6. MultiUserConcurrencyWorkflowTest
7. NotificationWorkflowTest
8. ErrorHandlingWorkflowTest

---

## ✅ **État d'Avancement**

- [ ] CompleteOrderLifecycleTest (0/3 tests)
- [ ] DeliveryTrackingWorkflowTest (0/3 tests)  
- [ ] CustomerRegistrationToFirstOrderTest (0/2 tests)
- [ ] PaymentToDeliveryWorkflowTest (0/3 tests)
- [ ] StockManagementWorkflowTest (0/2 tests)
- [ ] MultiUserConcurrencyWorkflowTest (0/2 tests)
- [ ] NotificationWorkflowTest (0/2 tests)
- [ ] ErrorHandlingWorkflowTest (0/2 tests)

**Total : 0/19 tests workflow implémentés**

---

*Une fois ce plan validé, nous implémenterons chaque test un par un en suivant l'ordre de priorité !* 🚀