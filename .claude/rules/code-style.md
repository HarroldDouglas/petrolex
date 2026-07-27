---
description: Standards de code PHP et conventions de commentaires
globs:
  - "app/**/*.php"
  - "database/**/*.php"
  - "routes/**/*.php"
---

# Code Style

- PHP : Laravel Pint (`composer pint`) — respecter les defaults du projet (`pint.json`).
- Analyse statique : Larastan level 3 (`composer phpstan`) doit passer sur les fichiers modifiés.
- Contrôleurs API **single-action** : une classe = un `__invoke()`, avec docblock `Route:` / `Name:`.
- Logique métier dans les `Services/`, pas dans les contrôleurs.
- Accès données via `Repositories/` (interfaces), pas de requêtes brutes dans les services quand un repo existe.
- Enums : **Spatie Enum** (`Spatie\Enum\Laravel\Enum`) → appels **méthode** `ProductType::BOTTLE()`,
  jamais des constantes `ProductType::BOTTLE`. Pour la valeur : `->value`.

## Commentaires (STRICT — always apply)
- **Langue : anglais uniquement.** Jamais de commentaires de code en français.
- **Minimalistes.** Uniquement le *pourquoi* (décision non-évidente, contournement, incident). Ne jamais
  narrer *ce que* fait le code. En cas de doute, supprimer.
- **Multi-lignes : bloc `/* ... */`, jamais des `//` empilés.** Une seule ligne `//` est OK ; deux `//`
  consécutifs ou plus doivent devenir un bloc `/* ... */`.
- Documentation classe/méthode : PHPDoc `/** ... */`.
