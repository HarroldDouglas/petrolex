# TODO: Implémentation du système multilingue

## Vue d'ensemble
Implémentation d'un système multilingue complet pour Petrolex, permettant aux utilisateurs de choisir leur langue préférée (fr/en) lors de l'inscription et de la mise à jour du profil. Le système doit gérer la localisation des messages API, emails, produits et toutes les réponses utilisateur.

## Analyse de l'existant
- ✅ Laravel i18n déjà configuré (`config/app.php` - locale: fr, fallback: en)
- ✅ Fichiers de traduction existants: `resources/lang/fr/` et `resources/lang/en/`
- ✅ Modèle User sans champ langue
- ✅ API d'inscription client (`StoreCustomerController`)
- ✅ API de mise à jour profil (`UpdateProfileController`)

## Phase 1: Structure de base

### 1.1 Migration base de données
```bash
Modifie directement la migration user et ajoute ce que tu veux! le projet nest pas encore en production. 
```
- [ ] Ajouter colonne `language` (varchar(2), default 'fr', nullable)
- [ ] Index sur la colonne language
- [ ] Mise à jour des utilisateurs existants avec 'fr'

### 1.2 Modèle User
- [ ] Ajouter `'language'` dans `$fillable`
- [ ] Ajouter `'language' => 'string'` dans `$casts`
- [ ] Validation: `in:fr,en`
- [ ] Accesseur pour valeur par défaut

### 1.3 Enum Language
```php
// app/Enums/Language.php
enum Language: string {
    case FRENCH = 'fr';
    case ENGLISH = 'en';
}
```

## Phase 2: API utilisateur

### 2.1 Inscription client
- [ ] Ajouter champ `language` à `StoreCustomerRequest`
- [ ] Validation optionnelle avec défaut 'fr'
- [ ] Mise à jour `CreateCustomerDTO`
- [ ] Test avec différentes langues

### 2.2 Mise à jour profil
- [ ] Ajouter champ `language` à `UpdateProfileRequest`
- [ ] Mise à jour `UpdateUserDTO`
- [ ] Validation avec règle `in:fr,en`

### 2.3 Réponses API localisées
- [ ] Middleware `SetLocale` basé sur `auth()->user()->language`
- [ ] Appliquer à toutes les routes API authentifiées
- [ ] Messages d'erreur localisés
- [ ] Messages de succès localisés

## Phase 3: Système email multilingue

### 3.1 Configuration email
- [ ] Mailables avec support i18n
- [ ] Templates email par langue
- [ ] Service `LocalizedEmailService`

### 3.2 Notifications
- [ ] Notifications Laravel localisées
- [ ] OTP par email en langue utilisateur
- [ ] Notifications push multilingues

## Phase 4: Contenu dynamique multilingue

### 4.1 Migration produits
```bash
php artisan make:migration add_multilingual_fields_to_products
```
- [ ] `name_fr` et `name_en` (remplace `name`)
- [ ] `description_fr` et `description_en` (remplace `description`)
- [ ] Migration des données existantes

### 4.2 Modèles multilingues
- [ ] Trait `HasTranslations` pour Product, BottleType, AccessoryType
- [ ] Accesseurs dynamiques basés sur la langue utilisateur
- [ ] Scopes pour filtrer par langue

### 4.3 Autres contenus
- [ ] Catégories de produits multilingues
- [ ] Messages d'erreur métier
- [ ] Labels et textes statiques

## Phase 5: Frontend et API

### 5.1 API Resources localisées
- [ ] ProductResource avec champs traduits
- [ ] CategoryResource multilingue
- [ ] OrderResource avec textes localisés

### 5.2 Tests multilingues
- [ ] Tests unitaires traductions
- [ ] Tests fonctionnels API avec différentes langues
- [ ] Tests emails/notifications

## Phase 6: Administration

### 6.1 Interface gestion
- [ ] CRUD produits avec traductions
- [ ] Validation des traductions obligatoires
- [ ] Preview par langue

### 6.2 Données de base
- [ ] Seeders avec traductions
- [ ] Commands pour migration existante
- [ ] Validation intégrité multilingue

## Structure recommandée

### Middleware
```php
// app/Http/Middleware/SetLocale.php
class SetLocale {
    public function handle($request, $next) {
        if ($user = auth()->user()) {
            app()->setLocale($user->language ?? 'fr');
        }
        return $next($request);
    }
}
```

### Service de traduction
```php
// app/Services/TranslationService.php
class TranslationService {
    public function getTranslation(Model $model, string $field, ?string $locale = null): ?string
    public function setTranslation(Model $model, string $field, string $value, string $locale): void
}
```

### Trait pour modèles
```php
// app/Traits/HasTranslations.php
trait HasTranslations {
    public function getTranslatedAttribute(string $field): ?string
    public function setTranslation(string $field, string $value, string $locale): void
}
```

## Organisation des fichiers de traduction

```
resources/lang/
├── fr/
│   ├── auth.php (✅ existant)
│   ├── api.php (nouveau - messages API)
│   ├── email.php (nouveau - templates email)
│   ├── products.php (nouveau - labels produits)
│   └── validation.php (nouveau - messages validation)
└── en/
    ├── auth.php (✅ existant)
    ├── api.php
    ├── email.php
    ├── products.php
    └── validation.php
```

## Points d'attention

### Performance
- [ ] Cache des traductions fréquentes
- [ ] Index database sur champs multilingues
- [ ] Eager loading des traductions

### Cohérence
- [ ] Validation traductions obligatoires
- [ ] Fallback automatique vers langue par défaut
- [ ] Tests de régression

### UX
- [ ] Détection langue navigateur (optionnel)
- [ ] Persistence choix langue
- [ ] Interface changement langue temps réel

## Migration des données existantes

### Script de migration
```bash
php artisan make:command MigrateExistingTranslations
```
- [ ] Copie `name` → `name_fr` pour tous les produits
- [ ] Génération traductions automatiques ou manuelles
- [ ] Validation post-migration

### Commandes utiles
```bash
# Vérifier intégrité traductions
php artisan translations:validate

# Générer traductions manquantes
php artisan translations:generate

# Export/Import traductions
php artisan translations:export
php artisan translations:import
```

## Timeline estimée
- **Phase 1**: 2 jours (structure base)
- **Phase 2**: 1-2 jours (API utilisateur)  
- **Phase 3**: 2-3 jours (système email)
- **Phase 4**: 3-4 jours (contenu dynamique)
- **Phase 5**: 1-2 jours (frontend/tests)
- **Phase 6**: 1-2 jours (administration)

**Total estimé: 10-15 jours**

## Risques et mitigation
- **Risque**: Migration données existantes complexe
  - **Mitigation**: Scripts de migration avec rollback, tests extensive
- **Risque**: Performance dégradée avec traductions
  - **Mitigation**: Cache approprié, index database optimisés
- **Risque**: Incohérences traductions
  - **Mitigation**: Validation stricte, processus review traductions