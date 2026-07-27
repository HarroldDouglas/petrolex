---
description: Règles de workflow de développement
globs:
  - "**"
alwaysApply: true
---

# Workflow

- Après une modif PHP : lancer `php -l` sur le fichier, puis `composer phpstan` (au moins sur les fichiers touchés).
- Avant commit : `composer pint -- --test` (style) doit être propre.
- Préférer lancer un test ciblé (`php artisan test --filter=...`) plutôt que toute la suite.
- Ne jamais committer ni pousser sans que l'utilisateur le demande. Toujours brancher hors de `master`.
- `.env`, secrets, mots de passe, tokens : jamais dans le repo ni dans `.claude/`.
- En prod : voir `production.md`. La prod n'est PAS un dépôt git → déploiement par copie de fichiers.
