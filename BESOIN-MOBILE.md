# 📱 Configuration WebSocket pour Flutter

## 🌍 Configuration Serveur

- **URL API**: `https://isogaz.afrik-solutions.com/api`
- **WebSocket URL**: `wss://isogaz.afrik-solutions.com:443/app/petro-key-12345`
- **Port WebSocket**: `443`
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
            Uri.parse('wss://isogaz.afrik-solutions.com:443/app/petro-key-12345?protocol=7&client=js&version=8.3.0&flash=false')
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

    void disconnect() {
        _channel?.sink.close();
        _channel = null;
    }
    
    // Gestion de la reconnexion automatique
    void _setupReconnection() {
        _channel!.stream.listen(
            (message) {
                // Traitement des messages
            },
            onError: (error) {
                print('WebSocket error: $error');
                // Reconnexion après 3 secondes
                Future.delayed(Duration(seconds: 3), () {
                    if (_channel == null) connectToTracking(orderNumber);
                });
            },
            onDone: () {
                print('WebSocket closed');
                // Reconnexion automatique
                Future.delayed(Duration(seconds: 2), () {
                    if (_channel == null) connectToTracking(orderNumber);
                });
            }
        );
    }
}
```

## 🔄 Code Flutter Complet avec Reconnexion

```dart
class DeliveryTracker {
    WebSocketChannel? _channel;
    String? _currentOrderNumber;
    Timer? _reconnectTimer;
    bool _isConnecting = false;
    
    void connectToTracking(String orderNumber) {
        if (_isConnecting) return;
        
        _isConnecting = true;
        _currentOrderNumber = orderNumber;
        
        try {
            _channel = WebSocketChannel.connect(
                Uri.parse('wss://isogaz.afrik-solutions.com:443/app/petro-key-12345?protocol=7&client=js&version=8.3.0&flash=false')
            );
            
            _setupStreamListener();
            _subscribeToChannel(orderNumber);
            
        } catch (e) {
            print('Connection failed: $e');
            _scheduleReconnect();
        } finally {
            _isConnecting = false;
        }
    }
    
    void _setupStreamListener() {
        _channel!.stream.listen(
            (message) {
                final data = jsonDecode(message);
                
                if (data['event'] == 'pusher:connection_established') {
                    print('WebSocket connected successfully');
                    _subscribeToChannel(_currentOrderNumber!);
                }
                
                if (data['event'] == 'delivery-position-updated') {
                    final positionData = jsonDecode(data['data']);
                    updateDriverPosition(
                        positionData['driver_lat'],
                        positionData['driver_lng']
                    );
                    updateProgress(positionData['progress_percentage']);
                }
            },
            onError: (error) {
                print('WebSocket error: $error');
                _scheduleReconnect();
            },
            onDone: () {
                print('WebSocket disconnected');
                _scheduleReconnect();
            }
        );
    }
    
    void _subscribeToChannel(String orderNumber) {
        _channel?.sink.add(jsonEncode({
            'event': 'pusher:subscribe',
            'data': {'channel': 'delivery-$orderNumber'}
        }));
    }
    
    void _scheduleReconnect() {
        _channel = null;
        _reconnectTimer?.cancel();
        
        _reconnectTimer = Timer(Duration(seconds: 3), () {
            if (_currentOrderNumber != null) {
                connectToTracking(_currentOrderNumber!);
            }
        });
    }
    
    void disconnect() {
        _reconnectTimer?.cancel();
        _channel?.sink.close();
        _channel = null;
        _currentOrderNumber = null;
    }
}
```

## 📋 Flow Complet de Livraison

### 1. Démarrage de la livraison (Livreur)
- **Action**: Cliquer sur "Démarrer la livraison"  
- **Endpoint**: `POST /api/tracking/delivery/{orderId}/start`
- **Réponse**: Status `started`, initialise les données de tracking

### 2. Mise à jour des positions (Livreur en mouvement)
- **Action**: App mobile envoie position toutes les 5 secondes
- **Endpoint**: `PATCH /api/tracking/delivery/{orderId}/position`
- **Données**: `{"lat": 3.848, "lng": 11.502, "speed": 25}`
- **WebSocket**: Déclenche automatiquement `delivery-position-updated`

### 3. Suivi temps réel (Client)
- **Action**: Client peut consulter à tout moment
- **Endpoint**: `GET /api/tracking/delivery/{orderId}`
- **WebSocket**: Reçoit les mises à jour automatiquement sur canal `delivery-{ORDER_NUMBER}`

### 4. Finalisation (Livreur)
- **Action**: Cliquer sur "Marquer comme livré"
- **Endpoint**: `PATCH /api/tracking/delivery/{orderId}/complete`
- **Effet**: Progress = 100%, tracking terminé, statut order → `delivered`

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
POST /api/tracking/delivery/{orderId}/start
Authorization: Bearer {token}
```

### Mise à jour position
```http
PATCH /api/tracking/delivery/{orderId}/position
Authorization: Bearer {token}
Content-Type: application/json

{
    "lat": 3.848,
    "lng": 11.502,
    "speed": 25
}
```

### Consulter statut de livraison
```http
GET /api/tracking/delivery/{orderId}
Authorization: Bearer {token}
```

### Finaliser livraison
```http
PATCH /api/tracking/delivery/{orderId}/complete
Authorization: Bearer {token}
```

## 🧪 Test WebSocket

1. **Ouvrir**: `https://isogaz.afrik-solutions.com/test-websocket-sender.html`
2. **Cliquer**: "Se connecter"
3. **Dans Flutter**: Se connecter au même WebSocket
4. **Test 1**: Envoyer message depuis web → recevoir dans Flutter
5. **Test 2**: Envoyer depuis Flutter → recevoir sur `https://isogaz.afrik-solutions.com/test-websocket-receiver.html`

## 📋 Checklist

- [ ] WebSocket se connecte
- [ ] Souscription au canal réussie
- [ ] Réception des messages position
- [ ] Mise à jour carte en temps réel
- [ ] Gestion reconnexion automatique
- [ ] Flow complet livreur testé
- [ ] API endpoints fonctionnels
- [ ] Authentification mobile OK