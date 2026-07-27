# Petrolex / Isogaz — API de distribution de gaz (Cameroun)

Backend Laravel : app mobile clients + livreurs, admin Livewire, paiements Mobile Money (MTN / Orange), cycle de vie des bouteilles de gaz consignées.

## Commands
- Lint PHP (dry-run) : `composer pint -- --test`
- Fix style : `composer pint`  (ou `composer format` = pint + blade)
- Analyse statique : `composer phpstan`  (Larastan, level 3)
- Lint un seul fichier : `php -l <file>`
- Tests : `php artisan test` (Pest/PHPUnit) — préférer un test ciblé : `php artisan test --filter=NomDuTest`
- Routes : `php artisan route:list`
- Nettoyer les caches après déploiement : `php artisan optimize:clear`

## Rules (.claude/rules/)
- `architecture.md` — Structure en couches, stack technique
- `gotchas.md` — Pièges critiques à éviter (ALWAYS APPLY)
- `code-style.md` — Standards PHP + conventions commentaires
- `workflow.md` — Règles de dev (ALWAYS APPLY)
- `production.md` — Serveur, déploiement (⚠️ prod = copie de fichiers, PAS git)
- `graphify.md` — Knowledge graph

## Self-improvement
Quand je corrige ton approche ou que tu découvres un pattern projet non-évident, propose de mettre à jour le fichier de règle concerné dans `.claude/rules/`.
