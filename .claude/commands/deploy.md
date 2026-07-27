---
description: Déploie Petrolex en production (git-based) via deploy-from-local.sh
---

Déploie Petrolex en production en lançant `./deploy-from-local.sh` depuis la racine du projet.

Référence à déployer (branche ou tag) : `$ARGUMENTS` — si vide, déploie `dev`.

Étapes :
1. Vérifie qu'on est sur la racine du repo et que la ref à déployer est bien poussée sur le remote `github` (`git push github <ref>` si besoin — demande avant de pousser).
2. Lance `./deploy-from-local.sh $ARGUMENTS` (le script demande une confirmation, fait `git fetch` + `reset --hard` côté serveur, composer, migrations `--force`, ré-assertion des permissions `resources/lang`, caches, `queue:restart`, reload php-fpm, restart workers, puis health check).
3. Rapporte le résultat : HEAD déployé + code HTTP du health check. Si échec, montre les logs (`ssh isogaz 'tail -50 /var/www/petrolex/storage/logs/laravel.log'`).

⚠️ Ne JAMAIS utiliser `migrate:fresh` en prod via ce flux (le script fait `migrate --force`, jamais de reset). Un reset BD est une opération manuelle séparée avec backup préalable.
