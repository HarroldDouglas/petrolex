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
Visitez http://localhost:8000 dans votre navigateur.

## 🛠️ Outils de Qualité de Code

### Outils Principaux

| Outil | Usage | Description |
|-------|--------|-------------|
| Laravel Pint | Formatage PHP | Wrapper pour PHP-CS-Fixer |
| Duster | Suite d'outils | Orchestration d'analyse de code |
| PHPStan + Larastan | Analyse statique | Détection d'erreurs |
| Blade Formatter | Templates | Formatage Blade |

### Installation des Outils

```bash
# Outils PHP
composer require --dev laravel/pint tightenco/duster phpstan/phpstan nunomaduro/larastan

# Blade Formatter
npm install -g blade-formatter
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

## 📚 Documentation & Ressources

- [Documentation Laravel](https://laravel.com/docs)
- [Guide de Contribution](CONTRIBUTING.md)
- [Documentation API](API.md)

## 🧰 Maintenance

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

## 📝 Licence

Ce projet est sous licence [MIT](LICENSE).

## 🤝 Contribution

Les contributions sont bienvenues ! Consultez notre [Guide de Contribution](CONTRIBUTING.md).