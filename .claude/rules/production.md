---
description: Infos serveur de production et déploiement (SANS secrets)
globs:
  - "deploy-*.sh"
  - "scripts/**"
---

# Production

> Aucun secret ici (mots de passe, tokens, APP_SECRET restent hors repo).

- Domaine : `isogaz.net` — Serveur : Contabo VPS `158.220.121.100`, accès `ssh isogaz`.
- Chemin app : `/var/www/petrolex` — Stack : Ubuntu 22.04, Nginx, PHP 8.2-FPM, MySQL 8, Redis, Supervisor.
- WebSockets : Laravel Reverb (port 8080, proxifié par Nginx sur `/app` et `/apps`).
- Workers Supervisor : `petrolex-worker:*` et `petrolex-reverb`.
- Fichiers appartenant à `www-data` : écrire dans `/tmp` puis `sudo cp` vers la destination.

## Déploiement
- ⚠️ **La prod n'est PAS (encore) un dépôt git** : déploiement historique = **copie de fichiers (rsync)**.
  Rsync vers un dossier de staging FRAIS (`/tmp/ptx-hotfix-<stamp>`), puis script `sudo` :
  backup → rsync dans `/var/www/petrolex` → `chown www-data` → supprimer les fichiers retirés →
  `php artisan config:clear route:clear view:clear queue:restart` → `systemctl reload php8.2-fpm`.
- FOOTGUN : `/tmp/petrolex-deploy` contient une copie COMPLÈTE et PÉRIMÉE du repo — ne jamais la
  re-sync en masse vers la prod (écraserait la prod avec du vieux code).
- Après déploiement : vérifier `curl` route santé = 200, routes supprimées = 404, uptime workers reset.
- Migration prévue vers un déploiement git (type checkers `deploy-from-local.sh`) : voir avec l'équipe.
