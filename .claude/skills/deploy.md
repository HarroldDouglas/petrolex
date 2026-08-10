---
description: Déploie Petrolex en production (git-based, PAR TAG UNIQUEMENT) via deploy-from-local.sh
---

Déploie Petrolex en production en lançant `./deploy-from-local.sh` depuis la racine du projet.

Tag à déployer : `$ARGUMENTS` — **OBLIGATOIRE, format `vX.Y.Z`**. La prod ne se déploie
JAMAIS par branche (décision du 2026-08-10) : une branche est mouvante, un tag est traçable
et rollbackable. Si aucun tag n'est fourni, proposer d'en créer un sur le commit à déployer
(`git tag -a vX.Y.Z <commit> -m "..."`) et **attendre la validation de l'utilisateur**.

Étapes :
1. Vérifie qu'on est sur la racine du repo et que le tag est bien poussé sur le remote
   `github` (`git push github <tag>` si besoin — demande avant de pousser).
2. **Demande confirmation explicite à l'utilisateur avant de lancer** (règle : aucune action
   sur la prod sans son feu vert), puis lance `./deploy-from-local.sh $ARGUMENTS`
   (le script refuse toute ref non-tag, fait `git fetch` + `reset --hard` côté serveur,
   composer, migrations `--force`, ré-assertion des permissions `resources/lang`, caches,
   `queue:restart`, reload php-fpm, restart workers, puis health check).
3. Rapporte le résultat : HEAD déployé (= le tag) + code HTTP du health check. Si échec,
   montre les logs (`ssh isogaz 'tail -50 /var/www/petrolex/storage/logs/laravel.log'`).

⚠️ Ne JAMAIS utiliser `migrate:fresh` en prod via ce flux (le script fait `migrate --force`,
jamais de reset). Un reset BD est une opération manuelle séparée avec backup préalable.
