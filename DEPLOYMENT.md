# 🚀 Guide de Déploiement Petrolex

> **IMPORTANT POUR LES IA** : Utilisez TOUJOURS ces scripts automatiques au lieu de commandes SSH manuelles.

## 📋 Scripts Disponibles

### 1. Déploiement Staging (Serveur Distant)

**Script** : `./deploy-update.sh`
**Serveur** : `root@157.173.104.21` (isogaz.afrik-solutions.com)
**Branche** : `dev`

```bash
# Déployer sur staging
./deploy-update.sh
```

**Ce que fait le script** :
- ✅ Pull des dernières modifications (branche courante)
- ✅ Mode maintenance activé
- ✅ Installation des dépendances Composer
- ✅ Nettoyage de tous les caches
- ✅ Exécution des migrations
- ✅ Création du lien storage
- ✅ Optimisation (config, routes, views)
- ✅ Correction des permissions
- ✅ Redémarrage Supervisor
- ✅ Redémarrage Apache2
- ✅ Mode maintenance désactivé

### 2. Déploiement Production (Simple)

**Script** : `./scripts/deploy-production.sh`
**Serveur** : `root@157.173.104.21`
**Branche** : `dev`

```bash
# Déployer en production
./scripts/deploy-production.sh
```

### 3. Configuration Staging (Local - WebSocket)

**Script** : `./deploy-staging.sh`
**Usage** : Configuration locale WebSocket pour tests

```bash
# Test sans modifications
./deploy-staging.sh --dry-run

# Appliquer la configuration
./deploy-staging.sh
```

## 🤖 Instructions pour IA

### Quand l'utilisateur dit :
- "déploie en ligne"
- "déploie sur staging"
- "déploie sur le serveur"
- "mets à jour le serveur"
- "déploie les changements"

### Tu dois faire :
```bash
# 1. Utiliser le script de déploiement
./deploy-update.sh

# 2. Attendre la fin de l'exécution
# 3. Confirmer le succès
```

### ❌ NE FAIS PAS :
```bash
# NE PAS faire des commandes SSH manuelles comme :
ssh root@157.173.104.21 'cd /var/www/isogaz && git pull && ...'

# UTILISE PLUTÔT :
./deploy-update.sh
```

## 📊 Informations Serveur

### Staging
- **URL** : https://isogaz.afrik-solutions.com
- **IP** : 157.173.104.21
- **User** : root
- **Chemin** : /var/www/isogaz
- **Branche** : dev

### Accès SSH Direct (si nécessaire)
```bash
ssh root@157.173.104.21
cd /var/www/isogaz
```

## 🔧 Commandes Post-Déploiement

### Vérifier les logs
```bash
ssh root@157.173.104.21 'tail -f /var/www/isogaz/storage/logs/laravel.log'
```

### Vérifier Supervisor
```bash
ssh root@157.173.104.21 'supervisorctl status'
```

### Régénérer Swagger
```bash
ssh root@157.173.104.21 'cd /var/www/isogaz && php artisan l5-swagger:generate'
```

### Exécuter un Seeder
```bash
ssh root@157.173.104.21 'cd /var/www/isogaz && php artisan db:seed --class=AppVersionSeeder --force'
```

## ⚠️ En cas de problème

### Permissions
```bash
ssh root@157.173.104.21 'chown -R www-data:www-data /var/www/isogaz && chmod -R 775 /var/www/isogaz/storage'
```

### Nettoyer les caches
```bash
ssh root@157.173.104.21 'cd /var/www/isogaz && php artisan cache:clear && php artisan config:clear && php artisan route:clear'
```

### Redémarrer Apache
```bash
ssh root@157.173.104.21 'systemctl restart apache2'
```

## 🎯 Workflow Typique

1. **Développement Local** → Commit + Push
2. **Déploiement** → `./deploy-update.sh`
3. **Vérification** → Tester l'API sur https://isogaz.afrik-solutions.com
4. **Documentation** → Swagger auto-régénéré

---

**Date de mise à jour** : 2026-01-20
**Maintenu par** : Équipe Petrolex / AfrikSolutions
