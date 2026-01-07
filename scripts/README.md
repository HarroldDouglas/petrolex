# Scripts de Déploiement

## 📦 Déploiement en Production

Pour déployer en production, utilisez simplement:

```bash
./scripts/deploy-production.sh
```

Ce script va automatiquement:
1. Se connecter au serveur de production
2. Faire un `git pull origin dev`
3. Corriger les permissions (www-data)
4. Vider les caches Laravel
5. Reconstruire les caches optimisés

## 🔧 Post-Déploiement Manuel

Si vous avez déjà fait un `git pull` manuellement, vous pouvez juste exécuter le post-deploy:

```bash
ssh root@157.173.104.21 "cd /var/www/isogaz && bash scripts/post-deploy.sh"
```

## ⚠️ Pourquoi ces scripts?

Le problème de permissions (`file_put_contents: Permission denied`) survient car:
- Git peut réinitialiser les permissions après un pull
- Le package `harrold-wafo/laravel-custom-datatable` écrit dans `resources/lang/fr.json`
- Nginx/PHP-FPM tourne avec l'utilisateur `www-data`

Le script `post-deploy.sh` corrige automatiquement ces problèmes après chaque déploiement.

## 📝 Notes

- Ces scripts doivent être exécutés depuis votre machine locale (pas sur le serveur)
- Ils nécessitent un accès SSH configuré vers `root@157.173.104.21`
- Les permissions sont automatiquement corrigées à chaque utilisation
