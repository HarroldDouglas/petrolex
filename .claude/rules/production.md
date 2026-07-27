---
description: Serveur de production et LE circuit de déploiement à suivre (SANS secrets)
globs:
  - "deploy-*.sh"
  - ".claude/commands/deploy.md"
  - "scripts/**"
---

# Production

> Aucun secret ici (mots de passe, tokens, APP_SECRET restent hors repo).

- Domaine : `isogaz.net` — Serveur : Contabo VPS `158.220.121.100`, accès `ssh isogaz`.
- Chemin app : `/var/www/petrolex` — Stack : Ubuntu 22.04, Nginx, PHP 8.2-FPM, MySQL 8, Redis, Supervisor.
- WebSockets : Laravel Reverb (port 8080, proxifié par Nginx sur `/app` et `/apps`).
- Workers Supervisor : `petrolex-worker:*` (6) et `petrolex-reverb`.
- Fichiers : `isogaz-ptlx:www-data`. Git tourne en `isogaz-ptlx` sans sudo ; php-fpm (www-data) lit via le groupe.

## ✅ LE circuit de déploiement (à suivre — ne pas improviser)

La prod **est un dépôt git** trackant `github/dev` (remote `github`). Déployer **uniquement** ainsi :

```
/deploy                          # déploie dev
./deploy-from-local.sh <ref>     # branche ou tag
```

Le script (`deploy-from-local.sh`) fait, côté serveur : maintenance → `git fetch github` +
`git reset --hard` → `composer install --no-dev` → **`php artisan migrate --force`** → ré-assertion
des permissions `resources/lang` → caches → `queue:restart` → `sudo systemctl reload php8.2-fpm`
→ `sudo supervisorctl restart all` → maintenance off → health check.

Prérequis : la ref doit être poussée sur `github` d'abord (`git push github <ref>`).

## ⛔ Interdits / pièges

- **NE JAMAIS déployer par rsync/copie de fichiers** (ancienne méthode, supprimée).
- **NE JAMAIS `migrate:fresh` via un déploiement.** Un reset BD est manuel, avec backup préalable,
  et nécessite les deps DEV (Faker) le temps du seed (`composer install` puis `--no-dev` après).
- **Toujours reload php-fpm après déploiement** (OPcache périmé → 500) — le script le fait.
- `resources/lang/fr.json` est écrit par l'app au runtime → doit rester writable par www-data
  (ACL par défaut posée ; le script la ré-assère).
- Accès GitHub du serveur = deploy key read-only `~/.ssh/petrolex_deploy`. sudo passwordless
  limité à `reload`/`restart` (`/etc/sudoers.d/petrolex-deploy`).
