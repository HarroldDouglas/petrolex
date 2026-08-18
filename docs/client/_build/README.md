# Génération des PDF

Les PDF de `docs/client/pdf/` sont générés depuis les fichiers Markdown de `docs/client/`.

## Chaîne complète

```bash
OUT=/tmp/isogaz-pdf && mkdir -p $OUT

# 1. Markdown -> HTML paginé (+ manifest.json des titres)
node docs/client/_build/build.js $OUT

# 2. HTML -> PDF
cd $OUT
for f in *.html; do
  google-chrome --headless --disable-gpu --no-pdf-header-footer \
    --print-to-pdf="${f%.html}.pdf" "file://$PWD/$f"
done

# 3. Liens cliquables + signets
python3 docs/client/_build/linkify.py $OUT/manifest.json $OUT
```

## Les trois étages

- **`md2html.js`** — convertit le Markdown (titres, tables, listes, blocs de code,
  encadrés) en HTML.
- **`build.js`** — applique la charte : couverture, sommaire, mise en page A4. Écrit un
  HTML par document plus `manifest.json` (la liste des titres et leurs ancres).
- **`linkify.py`** — indispensable : Chrome headless produit des rectangles de lien **sans
  destination**, donc un sommaire non cliquable et aucun signet. Le script relocalise
  chaque titre dans le PDF via `pdftotext -bbox`, pose les destinations `/GoTo`, recâble
  les liens du sommaire, rend les URL externes cliquables et construit l'arborescence de
  signets.

Dépendances : `google-chrome`, `poppler-utils` (`pdftotext`), Python `pypdf`.

## Personnalisation

- Contenu : éditer les `.md`, relancer la chaîne.
- Charte graphique : constantes `BRAND` / `BRAND_DARK` / `ACCENT` en tête de `build.js`.
- Titres/sous-titres/destinataires des couvertures : tableau `DOCS` dans `build.js`.

## Pièges connus (déjà corrigés — ne pas réintroduire)

- Les styles de corps doivent rester scopés à `.content` : une règle `h1` globale porte
  `page-break-before: always` et éjecte le titre de la couverture.
- La couverture est dimensionnée en `100vh`, pas `297mm` : Chrome arrondit au-dessus de la
  feuille et ajoute une page blanche.
- Dans `linkify.py`, la recherche des destinations démarre **après** les pages de sommaire,
  sinon chaque lien pointe vers sa propre ligne de sommaire.
- Éviter le caractère `…` dans les blocs de code : absent de la police monospace, il rend `_`.
