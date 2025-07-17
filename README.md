# Laravel Admin Dashboard

![Laravel v11.x](https://img.shields.io/badge/Laravel-v11.x-FF2D20?logo=laravel)
![PHP v8.1+](https://img.shields.io/badge/PHP-v8.1+-777BB4?logo=php)
![License](https://img.shields.io/badge/License-MIT-yellow.svg)

## 📋 Vue d'ensemble
Pour avoir une idée du contexte de ce projet, parcourez les documents ici
https://drive.google.com/drive/u/3/folders/1os9Y6VpL0Jyb7VTC9U6nkuYl-1hmj-44
Si vous n'avez pas d'accès, contactez l'administrateur du projet.
Pour ce qui est de la partie web, nous avons utilisé un template nommé Axelit.
Axelit est un tableau de bord d'administration moderne pour Laravel offrant :
- Des interfaces riches et intuitives
- Une architecture robuste et extensible
- Des outils de développement intégrés

## 🚀 Démarrage Rapide

### Prérequis

- PHP 8.1 ou supérieur
- Composer
- Node.js 18.19 et NPM
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
# Environnement principal
cp .env.example .env
php artisan key:generate

# Environnement de test
cp .env.example .env.testing
php artisan key:generate --env=testing
```

Configurez votre `.env.testing` avec une base de données dédiée aux tests :
```env
# .env.testing
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=petrolex_testing
DB_USERNAME=votre_utilisateur
DB_PASSWORD=votre_mot_de_passe

# Configuration Admin de test
ADMIN_EMAIL=admin@test.com
ADMIN_PHONE=237699999999
ADMIN_PASSWORD=password
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

Le dossier `tests/Curl/` contient des scripts shell pour tester les endpoints API.

1. Rendre les scripts exécutables :
```bash
chmod +x tests/Curl/*.sh
```

2. Lancer les tests API individuellement :
```bash
# Test d'authentification
./tests/Curl/auth.sh

# Récupération de la liste des clients
./tests/Curl/customers.sh

# Récupération de la liste des centres de distribution
./tests/Curl/distribution_centers.sh

# Récupération des adresses d'un client (exemple avec ID 1)
./tests/Curl/customer_addresses.sh 1

# Création d'une adresse (exemple avec ID client 1 et données de test)
./tests/Curl/create_address.sh 1 "Maison principale" "123 Rue des Palmiers" "Bonanjo" "Douala" "Cameroun" "+237612345678" "Jean" "Dupont" "jean.dupont@email.com" "Près de la pharmacie centrale" true
```

3. **Exécuter tous les tests API automatiquement**:

Le script `tests/Curl/run_all_tests.sh` permet de lancer l'ensemble des tests API de manière automatisée. Il gère l'authentification, l'exécution séquentielle des tests et fournit un résumé des succès et des échecs. C'est un outil essentiel pour la validation rapide des endpoints après chaque modification.

Pour lancer tous les tests :
```bash
./tests/Curl/run_all_tests.sh
```

**Bonnes Pratiques pour les Tests API :**
- Pour chaque nouveau endpoint ou modification d'un endpoint existant, un test `curl` correspondant doit être ajouté dans le dossier `tests/Curl/`.
- Ces tests peuvent être facilement générés avec l'aide de l'IA en lui fournissant les spécifications précises de l'endpoint (méthode HTTP, URL, paramètres, corps de la requête, headers attendus, etc.).
- Assurez-vous que les tests couvrent les cas de succès et les cas d'erreur (ex: validation, authentification).


Tests E2E (Laravel Dusk)
Ce projet utilise Laravel Dusk pour les tests d'interface utilisateur automatisés avec Chrome for Testing.

1. Installation et Configuration de Dusk
Bash

composer require --dev laravel/dusk
php artisan dusk:install
2. Téléchargement et "Installation" de Chrome for Testing et ChromeDriver
Laravel Dusk utilise un navigateur réel et son pilote (driver) pour simuler les interactions utilisateur. Nous utilisons Chrome for Testing, une version de Chrome dédiée aux tests, et son pilote ChromeDriver.

Créez un dossier bin à la racine de votre projet :
C'est là que nous stockerons les exécutables de Chrome for Testing et ChromeDriver.

Bash

mkdir -p bin/chrome-for-testing
Téléchargez les binaires :
Rendez-vous sur le tableau de bord officiel de Chrome for Testing pour télécharger la version Stable de Chrome et ChromeDriver pour votre plateforme linux64 :

Chrome for Testing (linux64): Recherchez la version linux64/chrome-linux64.zip sous la section "Stable".

ChromeDriver (linux64): Recherchez la version linux64/chromedriver-linux64.zip sous la section "Stable" (doit correspondre à la même version que Chrome).

Vous pouvez généralement trouver les liens directs ici (vérifiez toujours les versions les plus récentes sur le site) :

https://googlechromelabs.github.io/chrome-for-testing/

Exemple de commandes pour télécharger la version Stable (ajustez les numéros de version si elles ont évolué) :

Bash

# Téléchargez Chrome for Testing (linux64)
wget https://storage.googleapis.com/chrome-for-testing-public/138.0.7204.92/linux64/chrome-linux64.zip -P ~/Downloads/

# Téléchargez ChromeDriver (linux64)
wget https://storage.googleapis.com/chrome-for-testing-public/138.0.7204.92/linux64/chromedriver-linux64.zip -P ~/Downloads/
(Note : Remplacez 138.0.7204.92 par la version stable la plus récente si elle a changé.)

Déplacez et décompressez les binaires dans le dossier bin du projet :

Bash

# Déplacez et décompressez Chrome for Testing
mv ~/Downloads/chrome-linux64.zip bin/chrome-for-testing/
cd bin/chrome-for-testing/
unzip chrome-linux64.zip
rm chrome-linux64.zip # Supprime l'archive après l'extraction

# Retournez au dossier 'bin'
cd ../

# Déplacez et décompressez ChromeDriver
mv ~/Downloads/chromedriver-linux64.zip bin/
unzip chromedriver-linux64.zip
rm chromedriver-linux64.zip # Supprime l'archive après l'extraction
Après ces étapes, vous devriez avoir la structure suivante :

votre-projet/
├── bin/
│   └── chrome-for-testing/
│       ├── chrome-linux64/
│       │   └── chrome  <-- L'exécutable Chrome for Testing
│       └── chromedriver-linux64/
│           └── chromedriver <-- L'exécutable ChromeDriver
└── ...
Rendez ChromeDriver exécutable :

Bash

chmod +x bin/chrome-for-testing/chromedriver-linux64/chromedriver
3. Configuration de Dusk pour Chrome for Testing
Ouvrez le fichier tests/DuskTestCase.php et assurez-vous que la méthode driver() est configurée pour utiliser Chrome for Testing et ChromeDriver comme suit :

PHP

<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\TestCase as BaseTestCase;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Configuration pour Chrome Driver avec Chrome for Testing
     */
    protected function driver(): RemoteWebDriver
    {
        // ASSUREZ-VOUS QUE CES CHEMINS SONT CORRECTS !
        // Ils doivent pointer vers les exécutables que vous avez décompressés dans votre dossier 'bin'.
        $chromeBinaryPath = base_path('bin/chrome-for-testing/chrome-linux64/chrome');
        $chromeDriverPath = base_path('bin/chrome-for-testing/chromedriver-linux64/chromedriver');

        $options = (new ChromeOptions)
            ->addArguments(collect([
                '--disable-gpu',
                '--headless=new', // Utilisez '--headless=new' pour la nouvelle version du mode headless (Chrome 112+).
                '--window-size=1920,1080',
                '--no-sandbox', // Essentiel sur Linux, surtout dans les environnements CI.
            ])->filter()->all());

        // Définir le chemin de l'exécutable Chrome for Testing
        $options->setBinary($chromeBinaryPath);

        return RemoteWebDriver::create(
            'http://localhost:9515', // Port par défaut de ChromeDriver
            DesiredCapabilities::chrome()->setCapability(ChromeOptions::CAPABILITY, $options),
            60000, // Timeout de connexion en millisecondes (60 secondes)
            60000  // Timeout de requête en millisecondes (60 secondes)
        );
    }

    // ... (Le reste de votre fichier DuskTestCase.php)

}
4. Configuration des variables d'environnement
Assurez-vous que votre fichier .env est configuré avec l'URL de votre application Laravel :

Extrait de code

# Configuration Dusk
APP_URL=http://127.0.0.1:8000 # Ou l'URL de votre application si différente

# Données de test (utilisez vos vraies données admin ou des données de test)
ADMIN_EMAIL=admin@petrolex.com
ADMIN_PASSWORD=votre_mot_de_passe_admin
ADMIN_PHONE=+237655332183
5. Lancement des Tests E2E
Pour exécuter les tests Dusk, vous devez d'abord lancer votre serveur Laravel et ChromeDriver.

Lancez votre serveur Laravel (dans un terminal) :

Bash

php artisan serve
Laissez ce terminal ouvert.

Lancez ChromeDriver (dans un nouveau terminal) :

Bash

cd bin/chrome-for-testing/chromedriver-linux64/
./chromedriver --port=9515
Laissez ce terminal ouvert.

Exécutez vos tests Dusk (dans un troisième terminal) :

Bash

php artisan dusk
Autres commandes utiles pour les tests Dusk :

Bash

# Lancer un test spécifique
php artisan dusk tests/Browser/LoginTest.php

# Lancer une méthode spécifique
php artisan dusk tests/Browser/LoginTest.php --filter=test_user_can_login_with_email

# Tests avec sortie détaillée
php artisan dusk --verbose
Bonnes Pratiques de Test
Créer un test pour chaque nouvelle fonctionnalité

Maintenir une couverture de code > 80%

Utiliser des données de test cohérentes

Nettoyer l'environnement après chaque test

Documenter les cas de test complexes

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

