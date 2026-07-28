---
description: Réinitialise la BD de production (baseline Logbessou/9Kg) via reset-prod-db.sh
---

⚠️ DESTRUCTIF. Réinitialise la base de **production** en lançant `./reset-prod-db.sh`.

Le script efface TOUTES les données réelles (clients, commandes, paiements, bouteilles) et recrée le baseline (super admin `admin@isogaz.net`, centre Logbessou stock 0, Bouteille 9Kg + accessoires, géo, rôles, comptes de test). **Un backup BD est fait avant le wipe.**

Avant de lancer : confirme explicitement avec l'utilisateur que le wipe prod est voulu. Le script demande de taper `RESET`. Rapporte le baseline recréé + le health check final.
