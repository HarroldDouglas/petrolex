---
description: Pièges critiques Petrolex — erreurs qui cassent la prod ou l'argent
globs:
  - "**"
alwaysApply: true
---

# Gotchas — IMPORTANT

## Enums & conventions
- Spatie Enum v3 : `ProductType::BOTTLE()` (méthode), pas `ProductType::BOTTLE` (constante). Valeur via `->value`.
- Contrôleurs API single-action `__invoke()` uniquement.

## Prix produits (source de vérité à deux niveaux)
- Le prix affiché au mobile vient d'abord de `product_category_city_prices` (surcharge **par ville**),
  et **retombe sur le prix de base du `bottle_type`** (`content_price` / `full_price`) si aucune ligne ville.
  Voir `ProductResource::getBottleDetails()` (`?? $bottleType->content_price`).
- La ville est résolue via : centre → quartier → municipalité → ville (`resolveCityIdForDistributionCenter`).
  Le prix montré doit matcher celui que la validation de commande attend pour cette ville.

## Paiements Mobile Money
- Les contrôleurs de callback (`Http/Controllers/Api/Payment/*CallbackController`) sont des **stubs** :
  la confirmation réelle passe par `VerifyPaymentStatusJob` (polling du statut côté passerelle).
- **MTN** : montant = chaîne **entière** sans décimales (XAF n'a pas de centimes) → `(string)(int)$amount`.
  Endpoint prod `proxy.momoapi.mtn.com`. Préfixes CM : 67x, 650-654, 676-679.
- **Orange** : endpoint prod `api-s1.orange.cm`, token sur `/token`. Préfixes CM : 655-659, 690-699.
- **NE JAMAIS envoyer un mail à l'intérieur d'une transaction DB de paiement** : un échec d'envoi
  rollback le paiement. Envoyer les mails APRÈS commit (incident corrigé).

## Cycle de vie des bouteilles
- Invariants de scan/réception/libération protégés par `IncomingScanGuard` + `BottleReleaseService`.
  Une même bouteille ne peut pas être reçue dans deux approvisionnements à la fois. Statuts :
  `in_stock`, `with_delivery_person`, `returned_to_supplier`, `pending_reception`, …

## Données géo & apps mobiles (incident 2026-08)
- **Toute donnée géographique exposée aux apps mobiles doit être non-nulle** : les apps Flutter
  sont fortement typées et crashent sur un `null` inattendu (« type 'Null' is not a subtype… »).
  Les quartiers exigent lat/lng (BD NOT NULL + formulaire admin + seeder complet — ne pas détricoter).
- Les crashs mobiles ne remontent nulle part par défaut → les apps doivent poster leurs erreurs
  sur `POST /api/app/logs` (visualisation : admin → Logs mobiles).

## Sécurité
- Accès aux ressources client verrouillé au staff (anti-IDOR). Login + OTP rate-limités.
- L'ancien endpoint de callback paiement non-authentifié (exploit) a été **supprimé** — ne pas le réintroduire.

## Seeders / reset
- `migrate:fresh --seed` en prod ne restaure QUE la référence (voir `architecture.md`). Tout backup avant.
- Le `BottleTypeSeeder` ne contient que la **9Kg** (baseline client). Les 12/15Kg sont des ajouts admin manuels,
  volontairement hors seeder.
