# 📡 Guide des Canaux WebSocket - Système de Suivi

## 🎯 Vue d'Ensemble des Canaux

Le système utilise **Laravel Reverb** avec le protocole Pusher pour la communication temps réel.

---

## 🔧 Configuration de Connexion

### URL de Connexion
```
wss://mondomaine.com:8080/app/petro-key-12345?protocol=7&client=js&version=8.3.0&flash=false
```

### Paramètres
- **App Key**: `petro-key-12345`
- **Host**: `mondomaine.com` (production) / `127.0.0.1` (dev)
- **Port**: `8080`
- **Protocol**: `7` (Pusher protocol v7)
- **SSL**: Obligatoire en production (`wss://`)

---

## 📡 Canaux Disponibles

### 1. 📦 Canal Spécifique par Commande (RECOMMANDÉ)  
**Nom**: `delivery-{ORDER_NUMBER}`

**Exemples**:
- `delivery-TEST-C1-1759146129-001`
- `delivery-CMD-2025-001234`

**Événements diffusés**:
- `delivery-position-updated`
- `delivery-status-updated`

**Usage**: Suivi spécifique d'une commande - SEULE MÉTHODE RECOMMANDÉE

**Avantages**:
- ✅ Confidentialité : Chaque client ne reçoit que SES données
- ✅ Performance : Trafic minimal 
- ✅ Sécurité : Pas d'exposition de données d'autres clients
- ✅ Scalabilité : Performance constante même avec 1000+ livraisons

**Souscription**:
```json
{
    "event": "pusher:subscribe", 
    "data": {
        "channel": "delivery-TEST-C1-1759146129-001"
    }
}
```

### 2. ⚠️ Canal Global (SUPPRIMÉ - FAILLE DE SÉCURITÉ)

**Nom**: `delivery-tracking`

**Faille critique corrigée**:
- ❌ **EXPOSITION MASSIVE DE DONNÉES**: Tous les clients recevaient TOUTES les données de TOUTES les livraisons
- ❌ **VIOLATION DE CONFIDENTIALITÉ**: Les clients pouvaient voir les positions et statuts des autres livraisons
- ❌ **RISQUE SÉCURITAIRE MAJEUR**: Données sensibles (adresses, noms clients, positions GPS) exposées
- ❌ **PERFORMANCE DÉGRADÉE**: Trafic réseau excessif

**Status**: **COMPLÈTEMENT SUPPRIMÉ** ✅
- Serveur: Événements ne diffusent plus sur canal global
- Client: Toutes les souscriptions globales supprimées
- Delivery: Interface livreur utilise uniquement canaux spécifiques

---

## 📨 Format des Événements

### 🚚 `delivery-position-updated`

**Structure complète**:
```json
{
    "event": "delivery-position-updated",
    "channel": "delivery-TEST-C1-1759146129-001",
    "data": {
        "order_id": 456,
        "order_number": "TEST-C1-1759146129-001",
        "driver_lat": 3.8485,
        "driver_lng": 11.5025,
        "driver_position": {
            "lat": 3.8485,
            "lng": 11.5025
        },
        "progress_percentage": 45.2,
        "distance_remaining": 2.8,
        "estimated_duration": 12,
        "current_speed": 35,
        "timestamp": "2025-01-29T15:32:45.000Z",
        "driver_name": "Jean Livreur",
        "simulation": false,
        "destination": {
            "lat": 3.8480,
            "lng": 11.5020
        }
    }
}
```

**Champs obligatoires**:
- `order_id`: ID numérique de la commande
- `order_number`: Numéro de commande (string)
- `driver_lat/driver_lng`: Position GPS du livreur
- `timestamp`: Horodatage ISO 8601

**Champs optionnels**:
- `progress_percentage`: Progression (0-100%)
- `distance_remaining`: Distance restante en km
- `estimated_duration`: Temps estimé en minutes
- `current_speed`: Vitesse actuelle en km/h
- `driver_name`: Nom du livreur
- `simulation`: `true` si données simulées

### 📊 `delivery-status-updated`

**Structure**:
```json
{
    "event": "delivery-status-updated",
    "channel": "delivery-TEST-C1-1759146129-001",
    "data": {
        "order_id": 456,
        "order_number": "TEST-C1-1759146129-001",
        "status": "processing",
        "status_label": "En cours de livraison",
        "timestamp": "2025-01-29T15:30:00.000Z",
        "previous_status": "confirmed",
        "driver_name": "Jean Livreur"
    }
}
```

**Statuts possibles**:
- `pending`: En attente
- `confirmed`: Confirmée
- `processing`: En cours de livraison
- `delivered`: Livrée
- `cancelled`: Annulée

---

## 💻 Exemples d'Implémentation

### JavaScript/React Native
```javascript
const ws = new WebSocket('wss://mondomaine.com:8080/app/petro-key-12345?protocol=7&client=js&version=8.3.0&flash=false');

ws.onopen = () => {
    console.log('✅ WebSocket connecté');
    
    // Souscrire au canal de la commande
    ws.send(JSON.stringify({
        event: 'pusher:subscribe',
        data: { 
            channel: `delivery-${orderNumber}` 
        }
    }));
};

ws.onmessage = (event) => {
    const message = JSON.parse(event.data);
    
    switch (message.event) {
        case 'pusher:connection_established':
            console.log('🔗 Connexion établie');
            break;
            
        case 'pusher_internal:subscription_succeeded':
            console.log(`📡 Souscrit au canal: ${message.channel}`);
            break;
            
        case 'delivery-position-updated':
            handlePositionUpdate(message.data);
            break;
            
        case 'delivery-status-updated':
            handleStatusUpdate(message.data);
            break;
            
        case 'pusher:ping':
            // Répondre au ping automatiquement
            ws.send(JSON.stringify({
                event: 'pusher:pong',
                data: {}
            }));
            break;
    }
};

function handlePositionUpdate(data) {
    // Mettre à jour la position sur la carte
    updateMapPosition(data.driver_lat, data.driver_lng);
    
    // Mettre à jour les métriques
    updateProgress(data.progress_percentage);
    updateETA(data.estimated_duration);
    updateDistance(data.distance_remaining);
    updateSpeed(data.current_speed);
}

function handleStatusUpdate(data) {
    // Mettre à jour le statut de la commande
    updateOrderStatus(data.status, data.status_label);
    
    // Notifications spécifiques
    if (data.status === 'delivered') {
        showNotification('🎉 Votre commande a été livrée !');
    }
}
```

### Flutter/Dart
```dart
import 'package:web_socket_channel/web_socket_channel.dart';
import 'dart:convert';

class DeliveryWebSocket {
  WebSocketChannel? _channel;
  
  void connect(String orderNumber) {
    _channel = WebSocketChannel.connect(
      Uri.parse('wss://mondomaine.com:8080/app/petro-key-12345?protocol=7&client=js&version=8.3.0&flash=false')
    );
    
    // Souscrire au canal
    _channel!.sink.add(jsonEncode({
      'event': 'pusher:subscribe',
      'data': {'channel': 'delivery-$orderNumber'}
    }));
    
    // Écouter les messages
    _channel!.stream.listen(
      (message) {
        final data = jsonDecode(message);
        
        switch (data['event']) {
          case 'delivery-position-updated':
            _handlePositionUpdate(data['data']);
            break;
          case 'delivery-status-updated':
            _handleStatusUpdate(data['data']);
            break;
          case 'pusher:ping':
            _channel!.sink.add(jsonEncode({
              'event': 'pusher:pong',
              'data': {}
            }));
            break;
        }
      },
      onError: (error) {
        print('❌ WebSocket error: $error');
      },
    );
  }
  
  void _handlePositionUpdate(Map<String, dynamic> data) {
    // Mise à jour de la position
    final lat = data['driver_lat'];
    final lng = data['driver_lng'];
    final progress = data['progress_percentage'];
    
    // Notifier les widgets
    // ...
  }
}
```

---

## 🔄 Gestion de la Reconnexion

### Reconnexion Automatique
```javascript
let reconnectAttempts = 0;
const maxReconnectAttempts = 5;
const reconnectDelay = 2000;

ws.onclose = (event) => {
    console.log(`🔌 WebSocket fermé: ${event.code}`);
    
    if (reconnectAttempts < maxReconnectAttempts) {
        reconnectAttempts++;
        const delay = reconnectDelay * reconnectAttempts;
        
        console.log(`🔄 Reconnexion dans ${delay}ms (tentative ${reconnectAttempts})`);
        
        setTimeout(() => {
            connectWebSocket(orderNumber);
        }, delay);
    } else {
        console.error('❌ Échec de reconnexion après 5 tentatives');
        showError('Impossible de maintenir la connexion temps réel');
    }
};

ws.onerror = (error) => {
    console.error('❌ Erreur WebSocket:', error);
};
```

### Gestion des Codes de Fermeture
| Code | Signification | Action |
|------|---------------|--------|
| 1000 | Fermeture normale | Ne pas reconnecter |
| 1001 | Endpoint parti | Reconnecter |
| 1006 | Fermeture anormale | Reconnecter |
| 4000+ | Erreur application | Vérifier auth/params |

---

## 🧪 Test et Débogage

### Test de Connexion Simple
```bash
# Test avec websocat (outil CLI)
websocat wss://mondomaine.com:8080/app/petro-key-12345

# Ou avec curl
curl -i -N -H "Connection: Upgrade" \
     -H "Upgrade: websocket" \
     -H "Sec-WebSocket-Key: dGhlIHNhbXBsZSBub25jZQ==" \
     -H "Sec-WebSocket-Version: 13" \
     wss://mondomaine.com:8080/app/petro-key-12345
```

### Messages de Test
```json
// Souscription
{
    "event": "pusher:subscribe",
    "data": {
        "channel": "delivery-TEST-C1-1759146129-001"
    }
}

// Ping manuel
{
    "event": "pusher:ping",
    "data": {}
}
```

### URLs de Test Web
- **Récepteur**: `https://mondomaine.com/test-websocket-receiver.html`
- **Émetteur**: `https://mondomaine.com/test-websocket-sender.html`

---

## 🚨 Bonnes Pratiques

### Côté Client
1. **Toujours gérer les pings/pongs** pour maintenir la connexion
2. **Implémenter la reconnexion automatique** avec backoff exponentiel
3. **Valider les données reçues** avant de les utiliser
4. **Gérer les états déconnecté/connecté** dans l'UI
5. **Ne pas souscrire plusieurs fois au même canal**

### Côté Serveur
1. **Valider l'authentification** avant de diffuser
2. **Limiter la fréquence** des mises à jour (throttling)
3. **Inclure tous les champs obligatoires** dans les événements
4. **Logger les erreurs** de diffusion
5. **Nettoyer les canaux inactifs**

### Sécurité
1. **Utiliser HTTPS/WSS** en production
2. **Valider les tokens d'authentification**
3. **Ne pas exposer d'informations sensibles**
4. **Implémenter rate limiting** sur les souscriptions

---

## 📋 Checklist d'Intégration

### Client Mobile
- [ ] Connexion WebSocket établie
- [ ] Souscription aux canaux réussie
- [ ] Gestion des messages position
- [ ] Gestion des messages statut
- [ ] Reconnexion automatique
- [ ] Gestion des erreurs
- [ ] Interface utilisateur réactive
- [ ] Tests de déconnexion/reconnexion

### Backend
- [ ] Laravel Reverb configuré
- [ ] Événements diffusés correctement
- [ ] Authentification vérifiée
- [ ] Logs d'activité activés
- [ ] Performance optimisée
- [ ] Tests de charge effectués

---

**🚀 Votre système WebSocket est maintenant prêt pour une communication temps réel robuste !**