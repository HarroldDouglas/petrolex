# Laravel Admin Dashboard

![Laravel v11.x](https://img.shields.io/badge/Laravel-v11.x-FF2D20?logo=laravel)
![PHP v8.1+](https://img.shields.io/badge/PHP-v8.1+-777BB4?logo=php)
![License](https://img.shields.io/badge/License-MIT-yellow.svg)

## 📋 Vue d'ensemble

Axelit est un tableau de bord d'administration moderne pour Laravel offrant :
- Des interfaces riches et intuitives
- Une architecture robuste et extensible
- Des outils de développement intégrés

## 🚀 Démarrage Rapide

### Prérequis

- PHP 8.1 ou supérieur
- Composer
- Node.js et NPM
- MySQL ou autre SGBD compatible
- Git

### Installation

1. **Cloner le projet**
```bash
git clone https://github.com/votre-compte/axelit-laravel.git
cd axelit-laravel
```

2. **Installer les dépendances**
```bash
composer install
npm install
```

3. **Configuration de l'environnement**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configurer la base de données**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nom_de_votre_base
DB_USERNAME=votre_utilisateur
DB_PASSWORD=votre_mot_de_passe

# Configuration Admin
ADMIN_EMAIL=admin@petrolex.com
ADMIN_PASSWORD=votre_mot_de_passe_securise
ADMIN_NAME="Super Admin"
```

5. **Finaliser l'installation**
```bash
php artisan migrate --seed
php artisan storage:link
npm run build
```

6. **Lancer l'application**
```bash
php artisan serve
```
Visitez http://localhost:8000 ou le lien généré dans votre navigateur.

## 🛠️ Outils de Qualité de Code

### Outils Principaux

| Outil | Usage | Description |
|-------|--------|-------------|
| Laravel Pint | Formatage PHP | Wrapper pour PHP-CS-Fixer |
| Duster | Suite d'outils | Orchestration d'analyse de code |
| PHPStan + Larastan | Analyse statique | Détection d'erreurs |
| Blade Formatter | Templates | Formatage Blade |
| Spatie Permissions | Gestion des rôles | ACL pour Laravel |
| Spatie Activitylog | Journalisation | Suivi des activités |

### Installation des Outils

```bash
# Outils PHP
composer intall

# Outils JS
npm install
```

### Commandes Utiles

```bash
# Formatage
composer pint              # PHP
composer format:blade      # Blade
composer format           # Tout formater

# Analyse
composer phpstan          # Analyse statique
composer duster           # Suite complète
```

## 🧪 Tests

Le projet utilise plusieurs niveaux de tests pour assurer la qualité du code :

### Tests Unitaires et d'Intégration (PHPUnit)

Les tests sont organisés dans le dossier `tests/` :
- `tests/Unit/` : Tests unitaires
- `tests/Feature/` : Tests d'intégration
- `tests/Browser/` : Tests E2E avec Laravel Dusk

Commandes pour exécuter les tests :
```bash
# Lancer tous les tests
php artisan test

# Lancer les tests avec couverture de code
php artisan test --coverage

# Lancer un test spécifique
php artisan test --filter=NomDuTest

# Lancer les tests en parallèle
php artisan test --parallel
```

### Tests API (Curl Scripts)

Le dossier `tests/Curl/` contient des scripts shell pour tester les endpoints API manuellement.

1. Rendre les scripts exécutables :
```bash
chmod +x tests/Curl/*.sh
```

2. Lancer les tests API :
```bash
# Test d'authentification
./tests/Curl/test_auth.sh

# Autres tests disponibles
./tests/Curl/test_users.sh
./tests/Curl/test_products.sh
```

### Tests E2E (Laravel Dusk)

> ⚠️ En cours d'intégration

Laravel Dusk est utilisé pour les tests de navigation web.

Installation et configuration :
```bash
# Installation
composer require --dev laravel/dusk
php artisan dusk:install

# Création d'un test Dusk
php artisan dusk:make NomDuTest

# Exécution des tests Dusk
php artisan dusk
```

### Bonnes Pratiques de Test

- Créer un test pour chaque nouvelle fonctionnalité
- Maintenir une couverture de code > 80%
- Utiliser des données de test cohérentes
- Nettoyer l'environnement après chaque test
- Documenter les cas de test complexes

## 📚 Documentation & Ressources

- [Documentation Laravel](https://laravel.com/docs)
- [Guide de Contribution](CONTRIBUTING.md)
- [Documentation API](API.md)
- [Guide du Projet](guide.md) - Document essentiel détaillant les spécifications techniques, 
  les phases du projet et la stack technique complète du projet ISOGAZ

## 🧰 Maintenance

### Gestion des Logs

Les logs sont automatiquement:
- Générés quotidiennement dans `/storage/logs/petrolex-YYYY-MM-DD.log`
- Conservés pendant 30 jours
- Formatés avec horodatage et niveau de gravité

### Commandes Essentielles

```bash
# Gestion du cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Cache pour production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

