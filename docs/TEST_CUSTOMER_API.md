# Test Customer API - Guide de Test

## Vue d'ensemble

Ce document décrit le compte client de test permanent créé pour les validations Google Play Store et comment tester tous les endpoints API critiques.

## 🔑 Credentials du Compte Test

```
Email:    test.customer@petrolex.com
Password: TestPetrolex2026!
Phone:    +237600000001
```

> ⚠️ **IMPORTANT:** Ce compte ne doit JAMAIS être supprimé de la base de données, ni en staging ni en production. Il est utilisé par les robots Google Play Store pour les tests automatisés.

## 📋 Informations du Compte

- **Type:** Client (Customer)
- **Statut:** Actif
- **Email vérifié:** Oui
- **Balance:** 0.00 FCFA
- **Adresse de livraison:** 1 adresse par défaut configurée
- **Langue:** Français (fr)

## 🚀 Script de Test Automatisé

Un script bash complet a été créé pour tester tous les endpoints API critiques.

### Utilisation

```bash
# Test local
./scripts/test-customer-api.sh http://localhost:8000

# Test staging
./scripts/test-customer-api.sh https://staging-api.petrolex.com

# Test production
./scripts/test-customer-api.sh https://api.petrolex.com
```

### Endpoints testés

Le script teste automatiquement les endpoints suivants:

#### 1. **Santé du système**
- `GET /api/health` - Vérification santé de l'API

#### 2. **Authentification**
- `POST /api/login/customer` - Login avec email
- `POST /api/login/customer` - Login avec téléphone
- `GET /api/auth/check` - Vérification authentification
- `POST /api/logout` - Déconnexion

#### 3. **Profil utilisateur**
- `GET /api/user` - Récupération du profil

#### 4. **Géographie**
- `GET /api/geography/countries` - Liste des pays
- `GET /api/geography/countries/{id}/cities` - Liste des villes
- `GET /api/geography/cities/{id}/neighborhoods` - Liste des quartiers

#### 5. **Centres de distribution**
- `GET /api/distribution-centers` - Liste des centres
- `GET /api/distribution-centers/closest` - Centre le plus proche

#### 6. **Commandes**
- `GET /api/my/orders` - Mes commandes

#### 7. **Application**
- `GET /api/app/version` - Version de l'application
- `GET /api/app/terms-and-conditions` - Conditions d'utilisation
- `GET /api/app/privacy-policy` - Politique de confidentialité
- `GET /api/app/support/contact` - Contact support

### Résultat attendu

```
╔══════════════════════════════════════════════════════════════════╗
║  ✓ All tests passed! API is ready for mobile app testing        ║
╚══════════════════════════════════════════════════════════════════╝
Passed: 14
Failed: 0
```

## 🔧 Maintenance du Compte

### Seeder permanent

Le compte est géré par le seeder `TestCustomerSeeder.php` qui:

1. Crée le compte s'il n'existe pas
2. Vérifie et met à jour les données si le compte existe déjà
3. Est **idempotent** - peut être exécuté plusieurs fois sans problème
4. S'exécute automatiquement avec tous les autres seeders

### Mise à jour du compte

Pour mettre à jour ou vérifier le compte:

```bash
php artisan db:seed --class=TestCustomerSeeder
```

Cela va:
- ✅ Vérifier que le mot de passe correspond
- ✅ Vérifier que le rôle `customer` est assigné
- ✅ Vérifier qu'un enregistrement `Customer` existe
- ✅ Vérifier qu'au moins une adresse de livraison existe
- ✅ Vérifier que l'email est vérifié
- ✅ Vérifier que l'utilisateur est actif

## 📱 Endpoints API par Catégorie

### Endpoints publics (sans authentification)
- Health check
- Terms and conditions
- Privacy policy

### Endpoints authentifiés (requièrent un token)
Tous les autres endpoints nécessitent un token Bearer obtenu via `/api/login/customer`

## 🔐 Format d'authentification

```bash
# Login
curl -X POST https://api.petrolex.com/api/login/customer \
  -H "Content-Type: application/json" \
  -d '{"login":"test.customer@petrolex.com","password":"TestPetrolex2026!"}'

# Utilisation du token
curl -X GET https://api.petrolex.com/api/user \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

## ✅ Checklist avant mise en production

Avant de déployer en production, vérifier que:

- [ ] Le compte test existe en staging
- [ ] Tous les tests API passent en staging
- [ ] Le seeder `TestCustomerSeeder` est présent dans `DatabaseSeeder.php`
- [ ] Les credentials sont documentés
- [ ] L'équipe mobile a été informée des credentials

## 🐛 Dépannage

### Le login échoue

```bash
# Vérifier que le compte existe et est actif
php artisan tinker
>>> $user = App\Models\User::where('email', 'test.customer@petrolex.com')->first();
>>> $user->is_active
>>> $user->hasRole('customer')
```

### Mot de passe incorrect

```bash
# Réexécuter le seeder pour réinitialiser le mot de passe
php artisan db:seed --class=TestCustomerSeeder
```

### Tests échouent

```bash
# Vérifier que l'API est accessible
curl -I http://localhost:8000/api/health

# Vérifier les logs Laravel
tail -f storage/logs/laravel.log
```

## 📞 Contact

Pour toute question concernant le compte de test ou les endpoints API, contacter l'équipe backend.

---

**Dernière mise à jour:** 2026-02-02
**Statut:** ✅ Tous les tests passent
