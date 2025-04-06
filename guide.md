# 📋 Cahier des Charges - Projet ISOGAZ

## 🎯 Fonctionnalités Attendues

### 1. Base de Données
- **Modèle de données**
  - Utilisateurs, commandes, bouteilles de gaz, paiements
  - Migrations Laravel
  - Relations entre tables
  - Seeders pour données de test

### 2. API Backend
- **Endpoints principaux**
  - Application mobile grand public
  - Application des livreurs
  - Administration
- **Fonctionnalités clés**
  - Authentification (Sanctum/JWT)
  - Traçabilité des bouteilles (codes-barres)
  - Géolocalisation
  - Intégration paiements (Orange Money, MTN, VISA)

### 3. Interface Web Admin
- Tableau de bord
- CRUD utilisateurs et rôles
- Gestion des commandes
- Gestion des stocks
- Suivi des livraisons
- Rapports et statistiques
- Interface paiements

### 4. Documentation
- API (Swagger/OpenAPI)
- Documentation technique
- Guide utilisateur admin
- Guide déploiement

### 5. Tests
- Tests unitaires
  - PHPUnit pour la logique métier
  - Tests des repositories et services
  - Tests des validations de formulaires
  - Tests des transformations de données
  - Couverture de code > 80%

- Tests d'intégration
  - Tests des endpoints API
  - Tests des flux de données
  - Tests des middleware et guards
  
- Tests End-to-End avec Laravel Dusk
  - Tests des parcours utilisateur complets
  - Tests de l'interface utilisateur
  - Tests des interactions JavaScript
  - Tests des formulaires et validations
  - Tests des redirections et alertes
  - Tests des sessions et authentification

- Tests Postman
  - Collection complète des endpoints API
  - Tests des scénarios d'utilisation
  - Variables d'environnement (dev/prod)
  - Tests automatisés avec Newman
  - Documentation API via Postman

- Tests de performance
  - Tests de charge
  - Tests de stress
  - Benchmarking

## 📅 Phases du Projet

### Phase 1: Conception et Mise en Place
1. **Conception BD**
   - Schéma relationnel
   - Attributs et contraintes
   - Validation du modèle

2. **Environment Setup**
   - Laravel configuration
   - Template Axelit
   - Outils de développement

3. **Documentation API**
   - Setup Swagger/OpenAPI
   - Standards API

### Phase 2: Core Development
1. **Modèles et Migrations**
2. **Contrôleurs API**
3. **Intégrations Externes**

### Phase 3: Interface Admin
1. **Vues Blade**
2. **Fonctionnalités Spécifiques**

### Phase 4: Tests & Optimisation
1. **Suite de Tests**
2. **Optimisations Performances**

### Phase 5: Déploiement
1. **Préparation**
2. **Production**

## 🛠️ Stack Technique

### Sécurité et Traçabilité
- **Spatie Laravel Permission**
  - Gestion fine des rôles et permissions
  - ACL hiérarchique
  - Integration avec les guards Laravel
- **Spatie Laravel Activitylog**
  - Journalisation automatique des activités
  - Suivi des modifications de modèles
  - Audit trail complet

### Documentation API
- Eloquent API Resources
- Laravel Scribe/L5-Swagger
- Postman

### Gestion de Code
- GitHub/GitLab
- CI/CD

### Qualité Code
- PHPStan
- Laravel Pint
- PHP CS Fixer

### Tests
- PHPUnit
- Faker
- Laravel Dusk

## 🚨 Tâches Urgentes

### Migration vers Livewire
1. **Installation et Configuration**
   - Installation de Livewire
   - Configuration des assets
   - Mise en place des composants de base

2. **Migration des Fonctionnalités**
   - Authentication
     - Conversion du formulaire de login en composant Livewire
     - Gestion des états de formulaire
     - Validation en temps réel
     - Messages d'erreur dynamiques
     - Gestion de la redirection post-login
   - Autres fonctionnalités à migrer progressivement

3. **Optimisations Livewire**
   - Lazy loading des composants
   - Polling et rafraîchissement automatique
   - Gestion des événements
   - States et loading states

4. **Tests Livewire**
   - Tests des composants
   - Tests des événements
   - Tests des validations
   - Tests de l'interaction utilisateur