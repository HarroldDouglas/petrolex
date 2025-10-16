# 🔧 Configuration du Système de Livraison

## 📁 Structure de Configuration

```
delivery-tracking/
├── shared-config.js          # ✅ Configuration GLOBALE (à utiliser)
├── client/
│   └── js/config.js          # ❌ OBSOLÈTE (à supprimer)
└── delivery/
    └── js/config.js          # ❌ OBSOLÈTE (à supprimer)
```

## 🎯 Configuration Centralisée

**Un seul fichier à modifier :** `shared-config.js`

### 🔗 API Configuration
```javascript
SHARED_CONFIG.API.BASE_URL = 'http://127.0.0.1:8001/api'
```

### 🗺️ Google Maps
```javascript
SHARED_CONFIG.GOOGLE_MAPS.API_KEY = 'votre-clé-api'
```

### 📡 WebSocket
```javascript
SHARED_CONFIG.WEBSOCKET.PORT = 8080
```

## 🚀 Comment Utiliser

### 1. Dans vos fichiers HTML
```html
<!-- Charger AVANT tous les autres scripts -->
<script src="../shared-config.js"></script>
```

### 2. Dans vos fichiers JavaScript
```javascript
// Utiliser directement
const apiUrl = SHARED_CONFIG.API.BASE_URL;
const googleMapsKey = SHARED_CONFIG.GOOGLE_MAPS.API_KEY;

// Ou via les alias
const config = CUSTOMER_CONFIG; // Pour côté client
const config = DELIVERY_CONFIG; // Pour côté livreur
```

## ✅ Avantages

1. **Configuration unique** : Un seul endroit à modifier
2. **Pas de duplication** : Fini les erreurs de synchronisation
3. **Maintenance facile** : Changement global en une fois
4. **Compatibilité** : Alias pour garder le code existant

## 🔄 Migration

1. ✅ `shared-config.js` créé avec toute la configuration
2. ⏳ Mettre à jour les imports HTML
3. ⏳ Supprimer les anciens fichiers config.js
4. ⏳ Tester tous les modules

## 🛠️ Pour Changer l'URL API

**Avant (3 fichiers à modifier) :**
```bash
# client/js/config.js
# delivery/js/config.js  
# autre-module/config.js
```

**Maintenant (1 seul fichier) :**
```bash
# shared-config.js ligne 8
BASE_URL: 'http://127.0.0.1:8001/api'
```

## 🎉 Résultat

- ✅ **Configuration unifiée**
- ✅ **Plus d'erreurs de synchro**
- ✅ **Maintenance simplifiée**
- ✅ **Deploy plus fiable**