# Déploiement Production — isogaz.net

**Serveur** : 158.220.121.100 (Contabo VPS)
**Domaine** : isogaz.net
**OS** : Ubuntu 22.04.5 LTS
**SSH** : `ssh -p 5522 isogaz-ptlx@158.220.121.100`
**Chemin projet** : `/var/www/petrolex`
**Date début** : 2026-03-02

---

## PHASE 1 — Stack Serveur ✅ COMPLÉTÉ

- [x] **PHP 8.2.30** + PHP-FPM installé
- [x] **Extensions PHP** : mbstring, xml, bcmath, curl, zip, gd, intl, pdo, pdo_mysql, redis, fileinfo, tokenizer, ctype
- [x] **Composer 2.9.5** installé
- [x] **MySQL 8.0.45** installé + démarré
- [x] **Redis** installé + démarré
- [x] **Nginx 1.18** installé + démarré
- [x] **Node.js 20.20.0 + NPM 10.8.2** installé
- [x] **Supervisor** installé + démarré
- [x] **Certbot 1.21.0** installé
- [x] **Firewall UFW** : ports 80, 443, 8080, 5522 ouverts

---

## PHASE 2 — Configuration Serveur ✅ COMPLÉTÉ

- [x] Répertoire `/var/www/petrolex` créé
- [x] Base MySQL `petrolex` + user `petrolex_user` créés
- [x] Virtual host Nginx configuré pour `isogaz.net`
- [x] **SSL Certbot** — Let's Encrypt installé (expire 2026-06-07, renouvellement auto)
- [x] Redis configuré (bind local)

---

## PHASE 3 — Déploiement Laravel ✅ COMPLÉTÉ

- [x] Projet uploadé et extrait dans `/var/www/petrolex`
- [x] `composer install --no-dev --optimize-autoloader` (135 packages)
- [x] `npm install && npm run build` (Vite, 54 modules, 3.33s)
- [x] `.env` production créé et configuré
- [x] `php artisan key:generate`
- [x] `php artisan migrate --force` (38 migrations, 46 tables)
- [x] `RolePermissionSeeder` : 8 rôles, 51 permissions ✅
- [x] `BottleTypeSeeder` : 1 type bouteille ✅
- [x] `AccessoryTypeSeeder` : 5 types accessoires ✅
- [x] `ProductCategorySeeder` : 6 catégories produits ✅
- [x] `GeographicSeeder` : 1 pays, 6 villes, 21 communes, 55 quartiers ✅
- [x] `AppVersionSeeder` : 4 versions ✅
- [ ] `ProductSeeder` : ⚠️ Nécessite un Centre de distribution → créer via admin d'abord
- [ ] `UserSeeder` : ignoré (admin créé manuellement)
- [x] `php artisan storage:link`
- [x] `php artisan config:cache && route:cache && view:cache && event:cache`
- [x] Permissions : `chown -R www-data:www-data /var/www/petrolex`
- [x] Compte admin créé : `admin@isogaz.net` / `Admin@Isogaz2026!` (rôle super_admin)

---

## PHASE 4 — Services Continus (Supervisor) ✅ COMPLÉTÉ

- [x] Queue worker : 2 process `petrolex-worker` RUNNING
- [x] Reverb WebSocket : `petrolex-reverb` RUNNING sur port 8080
- [x] `supervisorctl reread && supervisorctl update && start all`

---

## PHASE 5 — Cron (Scheduler Laravel) ✅ COMPLÉTÉ

- [x] Crontab `www-data` configuré :
  ```
  * * * * * cd /var/www/petrolex && php8.2 artisan schedule:run >> /dev/null 2>&1
  ```

---

## PHASE 6 — DNS (côté registrar) ✅ COMPLÉTÉ

- [x] `A  @    →  158.220.121.100` (propagé et vérifié)
- [x] `A  www  →  158.220.121.100` (propagé et vérifié via CNAME)
- [x] Vérification propagation : `dig isogaz.net A` → 158.220.121.100

---

## PHASE 7 — Vérification Finale ✅ COMPLÉTÉ (2026-03-09)

- [x] `https://isogaz.net` accessible — 302 redirect vers /login
- [x] `https://isogaz.net/api/health` répond `200 OK` — `{"status":"healthy"}`
- [x] HTTP → HTTPS redirect automatique (301)
- [x] `https://www.isogaz.net` fonctionne aussi
- [x] Login admin : `admin@isogaz.net` / `Admin@Isogaz2026!`
- [x] Queue worker actif : 2x `petrolex-worker` RUNNING
- [x] Reverb WebSocket actif : `petrolex-reverb` RUNNING (proxy Nginx `/app` configuré)
- [x] WebSocket handshake : code 101 OK
- [x] CSRF endpoint : `/sanctum/csrf-cookie` → 204 OK
- [x] Assets Vite : `/build/manifest.json` → 200 OK
- [x] Certificat SSL : Let's Encrypt, expire 2026-06-07, renouvellement auto
- [x] Failed jobs nettoyés, queue vide (0 pending)
- [x] Logs propres après nettoyage

---

## Infos Serveur Production

| Paramètre        | Valeur                        |
|------------------|-------------------------------|
| IP               | 158.220.121.100               |
| SSH User         | isogaz-ptlx                   |
| SSH Port         | 5522                          |
| Chemin projet    | /var/www/petrolex             |
| URL              | https://isogaz.net            |
| DB               | petrolex (MySQL)              |
| SMTP             | scie.o2switch.net:465         |
| Mail from        | info.isogaz@isogaz.net        |

## Script de déploiement rapide (mises à jour)

```bash
ssh -p 5522 isogaz-ptlx@158.220.121.100 << 'EOF'
cd /var/www/petrolex
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
chown -R www-data:www-data storage bootstrap/cache
supervisorctl restart all
EOF
```

---

**Maintenu par** : AfrikSolutions
**Mise à jour** : 2026-03-09
