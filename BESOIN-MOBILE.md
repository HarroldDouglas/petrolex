# 📱 Configuration WebSocket pour Flutter

## 🌍 Configuration Serveur

- **URL API**: `https://mondomaine.com/api`
- **WebSocket URL**: `wss://mondomaine.com:8080/app/petro-key-12345`
- **Port WebSocket**: `8080`
- **App Key**: `petro-key-12345`

## 🔗 Canal WebSocket

- **Format canal**: `delivery-{ORDER_NUMBER}`
- **Exemple**: `delivery-TEST-C1-1759146129-001`
- **Événement**: `delivery-position-updated`

## 📍 Format Position Reçue

```json
{
    "order_number": "TEST-C1-1759146129-001",
    "driver_lat": 3.8485,
    "driver_lng": 11.5025,
    "progress_percentage": 45.2,
    "distance_remaining": 2.8,
    "estimated_duration": 12,
    "current_speed": 35
}
```

## 💻 Code Flutter

### Dépendance
```yaml
dependencies:
  web_socket_channel: ^2.4.0
```

### Connexion WebSocket
```dart
import 'package:web_socket_channel/web_socket_channel.dart';
import 'dart:convert';

class DeliveryTracker {
    WebSocketChannel? _channel;
    
    void connectToTracking(String orderNumber) {
        _channel = WebSocketChannel.connect(
            Uri.parse('wss://mondomaine.com:8080/app/petro-key-12345?protocol=7&client=js&version=8.3.0&flash=false')
        );
        
        // Souscrire au canal
        _channel!.sink.add(jsonEncode({
            'event': 'pusher:subscribe',
            'data': {'channel': 'delivery-$orderNumber'}
        }));
        
        // Écouter les messages
        _channel!.stream.listen((message) {
            final data = jsonDecode(message);
            
            if (data['event'] == 'delivery-position-updated') {
                final positionData = data['data'];
                
                // Mettre à jour la position
                updateDriverPosition(
                    positionData['driver_lat'],
                    positionData['driver_lng']
                );
                updateProgress(positionData['progress_percentage']);
            }
        });
    }
}
```

## 📋 Flow Complet de Livraison

### 1. Démarrage de la livraison (Livreur)
- **Action**: Cliquer sur "Démarrer la livraison"  
- **Endpoint**: `POST /api/delivery/tracking/{orderId}/start`
- **Réponse**: Status `started`, initialise les données de tracking

### 2. Mise à jour des positions (Livreur en mouvement)
- **Action**: App mobile envoie position toutes les 5 secondes
- **Endpoint**: `POST /api/delivery/update-location`
- **Données**: `{"order_id": 123, "lat": 3.848, "lng": 11.502, "speed": 25}`
- **WebSocket**: Déclenche automatiquement `delivery-position-updated`

### 3. Suivi temps réel (Client)
- **Action**: Client peut consulter à tout moment
- **Endpoint**: `GET /api/tracking/delivery/{orderId}`
- **WebSocket**: Reçoit les mises à jour automatiquement sur canal `delivery-{ORDER_NUMBER}`

### 4. Finalisation (Livreur)
- **Action**: Cliquer sur "Marquer comme livré"
- **Endpoint**: `POST /api/orders/{orderId}/status` → `delivered`
- **Effet**: Progress = 100%, tracking terminé

## 🔄 Endpoints API Mobiles

### Authentification
```http
POST /api/login
Content-Type: application/json

{
    "email": "delivery1@test.com",
    "password": "password"
}
```

### Démarrage tracking
```http
POST /api/delivery/tracking/{orderId}/start
Authorization: Bearer {token}
```

### Mise à jour position
```http
POST /api/delivery/update-location
Authorization: Bearer {token}
Content-Type: application/json

{
    "order_id": 123,
    "lat": 3.848,
    "lng": 11.502,
    "speed": 25
}
```

### Statut de livraison
```http
GET /api/tracking/delivery/{orderId}
Authorization: Bearer {token}

POST /api/orders/{orderId}/status
Authorization: Bearer {token}
Content-Type: application/json

{
    "status": "delivered"
}
```

## 🧪 Test WebSocket

1. **Ouvrir**: `https://mondomaine.com/test-websocket-sender.html`
2. **Cliquer**: "Se connecter"
3. **Dans Flutter**: Se connecter au même WebSocket
4. **Test 1**: Envoyer message depuis web → recevoir dans Flutter
5. **Test 2**: Envoyer depuis Flutter → recevoir sur `test-websocket-receiver.html`

## 📋 Checklist

- [ ] WebSocket se connecte
- [ ] Souscription au canal réussie
- [ ] Réception des messages position
- [ ] Mise à jour carte en temps réel
- [ ] Gestion reconnexion automatique
- [ ] Flow complet livreur testé
- [ ] API endpoints fonctionnels
- [ ] Authentification mobile OK