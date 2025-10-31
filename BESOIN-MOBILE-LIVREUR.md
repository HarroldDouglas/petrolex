# 📱 Guide Tracking GPS Livreur - Flutter

## 🌍 Configuration Serveur

- **URL API**: `https://isogaz.afrik-solutions.com/api`
- **WebSocket URL**: `wss://isogaz.afrik-solutions.com:443/app/petro-key-12345`

---

## 🎯 Vue d'ensemble du Tracking

```
┌─────────────────────────────────────────────────────────────┐
│                   APP MOBILE LIVREUR                        │
│                                                             │
│  1. Démarrer tracking → POST /api/tracking/delivery/{id}/start    │
│  2. Envoyer position GPS (toutes les 3-5s) → PATCH /position      │
│  3. Calculer: progression + distance + temps               │
│  4. Terminer livraison → PATCH /complete                   │
│                                                             │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼ (WebSocket automatique)
         ┌─────────────────────────────────────┐
         │      APP MOBILE CLIENT              │
         │  Visualise position en temps réel   │
         └─────────────────────────────────────┘
```

---

## 🚀 1. Démarrer le Tracking

### Endpoint

```http
POST /api/tracking/delivery/{orderId}/start
Authorization: Bearer {token}
Content-Type: application/json

{
    "driver_lat": 3.8485,
    "driver_lng": 11.5025
}
```

### Réponse

```json
{
    "_metadata": {
        "success": true,
        "message": "Delivery started successfully."
    },
    "data": {
        "id": 123,
        "order_id": 456,
        "status": "started",
        "driver_lat": 3.8485,
        "driver_lng": 11.5025,
        "destination_lat": 3.8600,
        "destination_lng": 11.5200,
        "destination_address": "Rue de la Réunification, Yaoundé",
        "progress_percentage": 0,
        "distance_remaining": 8.5,
        "estimated_duration": 1200,
        "total_distance": 8.5
    }
}
```

---

## 📍 2. Envoyer Position GPS en Temps Réel

### Endpoint

```http
PATCH /api/tracking/delivery/{orderId}/position
Authorization: Bearer {token}
Content-Type: application/json

{
    "driver_lat": 3.8520,
    "driver_lng": 11.5080,
    "current_speed": 35,
    "progress_percentage": 25.5,
    "distance_remaining": 6.3,
    "estimated_duration": 900
}
```

**IMPORTANT** : L'app mobile DOIT calculer :
- `progress_percentage` : % de progression (0-100)
- `distance_remaining` : distance restante en km
- `estimated_duration` : temps estimé en secondes

### Code Flutter - Service GPS Complet

```dart
import 'package:geolocator/geolocator.dart';
import 'package:http/http.dart' as http;
import 'dart:async';
import 'dart:convert';
import 'dart:math';

class LocationTrackingService {
    static const String baseUrl = 'https://isogaz.afrik-solutions.com/api';

    Timer? _locationTimer;
    Position? _currentPosition;
    Position? _previousPosition;

    // Destination
    double? _destLat;
    double? _destLng;
    double? _initialDistance; // Distance totale au démarrage

    // Démarrer le tracking GPS réel
    Future<void> startTracking(
        String token,
        int orderId,
        double destLat,
        double destLng,
    ) async {
        _destLat = destLat;
        _destLng = destLng;

        // 1. Obtenir position GPS actuelle
        _currentPosition = await _getCurrentPosition();

        // 2. Calculer distance initiale
        _initialDistance = _calculateDistance(
            _currentPosition!.latitude,
            _currentPosition!.longitude,
            destLat,
            destLng,
        );

        // 3. Démarrer le tracking sur le serveur
        await _startTrackingOnServer(token, orderId);

        // 4. Envoyer position toutes les 3 secondes
        _locationTimer = Timer.periodic(Duration(seconds: 3), (_) async {
            await _updatePosition(token, orderId);
        });
    }

    // Obtenir position GPS
    Future<Position> _getCurrentPosition() async {
        bool serviceEnabled = await Geolocator.isLocationServiceEnabled();
        if (!serviceEnabled) throw Exception('GPS désactivé');

        LocationPermission permission = await Geolocator.checkPermission();
        if (permission == LocationPermission.denied) {
            permission = await Geolocator.requestPermission();
            if (permission == LocationPermission.denied) {
                throw Exception('Permission GPS refusée');
            }
        }

        return await Geolocator.getCurrentPosition(
            desiredAccuracy: LocationAccuracy.high,
        );
    }

    // Démarrer sur serveur
    Future<void> _startTrackingOnServer(String token, int orderId) async {
        await http.post(
            Uri.parse('$baseUrl/tracking/delivery/$orderId/start'),
            headers: {
                'Authorization': 'Bearer $token',
                'Content-Type': 'application/json',
            },
            body: jsonEncode({
                'driver_lat': _currentPosition!.latitude,
                'driver_lng': _currentPosition!.longitude,
            }),
        );
    }

    // Mettre à jour position
    Future<void> _updatePosition(String token, int orderId) async {
        _previousPosition = _currentPosition;
        _currentPosition = await _getCurrentPosition();

        // Calculer vitesse (km/h)
        double speed = _calculateSpeed();

        // Calculer distance restante
        double distanceRemaining = _calculateDistance(
            _currentPosition!.latitude,
            _currentPosition!.longitude,
            _destLat!,
            _destLng!,
        );

        // Calculer progression (0-100%)
        double progress = _initialDistance! > 0
            ? (((_initialDistance! - distanceRemaining) / _initialDistance!) * 100).clamp(0, 100)
            : 0;

        // Calculer temps estimé (en secondes)
        int estimatedDuration = speed > 0
            ? ((distanceRemaining / speed) * 3600).round()
            : 0;

        // Envoyer au serveur
        await http.patch(
            Uri.parse('$baseUrl/tracking/delivery/$orderId/position'),
            headers: {
                'Authorization': 'Bearer $token',
                'Content-Type': 'application/json',
            },
            body: jsonEncode({
                'driver_lat': _currentPosition!.latitude,
                'driver_lng': _currentPosition!.longitude,
                'current_speed': speed.toInt(),
                'progress_percentage': progress,
                'distance_remaining': distanceRemaining,
                'estimated_duration': estimatedDuration,
            }),
        );

        print('📍 Position: ${progress.toStringAsFixed(1)}% - ${distanceRemaining.toStringAsFixed(2)}km restants');
    }

    // Calculer vitesse entre 2 positions
    double _calculateSpeed() {
        if (_previousPosition == null || _currentPosition == null) return 0.0;

        double distance = _calculateDistance(
            _previousPosition!.latitude,
            _previousPosition!.longitude,
            _currentPosition!.latitude,
            _currentPosition!.longitude,
        );

        // Distance en km / temps en heures = km/h
        double timeInHours = 3.0 / 3600.0; // 3 secondes
        return distance / timeInHours;
    }

    // Formule Haversine - Distance entre 2 points GPS (en km)
    double _calculateDistance(double lat1, double lon1, double lat2, double lon2) {
        const R = 6371; // Rayon Terre en km

        double dLat = _toRadians(lat2 - lat1);
        double dLon = _toRadians(lon2 - lon1);

        double a = sin(dLat / 2) * sin(dLat / 2) +
            cos(_toRadians(lat1)) * cos(_toRadians(lat2)) *
            sin(dLon / 2) * sin(dLon / 2);

        double c = 2 * asin(sqrt(a));
        return R * c;
    }

    double _toRadians(double degrees) => degrees * (pi / 180);

    // Arrêter le tracking
    void stopTracking() {
        _locationTimer?.cancel();
        _locationTimer = null;
    }
}
```

---

## 🎭 3. Simulation de Déplacement (Tests)

### Logique de Simulation

Pour tester l'app **sans vraiment se déplacer**, on génère des points GPS intermédiaires entre le départ et la destination, puis on les envoie progressivement au serveur.

**Principe** :
1. Créer 30 points GPS entre départ et arrivée (interpolation linéaire)
2. Calculer un intervalle de temps (ex: 60s ÷ 30 points = 2s par point)
3. Envoyer chaque point au serveur comme si c'était une vraie position GPS
4. Calculer progression, distance restante et temps pour chaque point

### Code Flutter - Service de Simulation

```dart
import 'dart:async';
import 'dart:convert';
import 'dart:math';
import 'package:http/http.dart' as http;

class SimulationService {
    static const String baseUrl = 'https://isogaz.afrik-solutions.com/api';

    Timer? _timer;
    List<LatLng> _routePoints = [];
    int _currentIndex = 0;
    double _totalDistance = 0;

    // Démarrer simulation
    Future<void> startSimulation(
        String token,
        int orderId,
        double startLat,
        double startLng,
        double destLat,
        double destLng, {
        int durationSeconds = 60, // Durée totale simulation
    }) async {
        // 1. Générer points intermédiaires
        _routePoints = _generateRoutePoints(
            startLat, startLng,
            destLat, destLng,
            30, // Nombre de points
        );

        // 2. Calculer distance totale
        _totalDistance = _calculateTotalDistance();

        _currentIndex = 0;

        // 3. Démarrer tracking sur serveur
        await http.post(
            Uri.parse('$baseUrl/tracking/delivery/$orderId/start'),
            headers: {
                'Authorization': 'Bearer $token',
                'Content-Type': 'application/json',
            },
            body: jsonEncode({
                'driver_lat': startLat,
                'driver_lng': startLng,
            }),
        );

        // 4. Envoyer positions à intervalle régulier
        int intervalMs = (durationSeconds * 1000) ~/ _routePoints.length;

        _timer = Timer.periodic(Duration(milliseconds: intervalMs), (timer) async {
            if (_currentIndex >= _routePoints.length) {
                timer.cancel();
                print('🏁 Simulation terminée');
                return;
            }

            await _sendSimulatedPosition(token, orderId);
            _currentIndex++;
        });
    }

    // Générer points intermédiaires
    List<LatLng> _generateRoutePoints(
        double startLat, double startLng,
        double destLat, double destLng,
        int count,
    ) {
        List<LatLng> points = [];

        for (int i = 0; i <= count; i++) {
            double ratio = i / count;

            // Interpolation linéaire
            double lat = startLat + (destLat - startLat) * ratio;
            double lng = startLng + (destLng - startLng) * ratio;

            // Ajouter variation aléatoire (simuler route réelle)
            if (i > 0 && i < count) {
                lat += (Random().nextDouble() - 0.5) * 0.0005;
                lng += (Random().nextDouble() - 0.5) * 0.0005;
            }

            points.add(LatLng(lat, lng));
        }

        return points;
    }

    // Calculer distance totale de la route
    double _calculateTotalDistance() {
        double total = 0;
        for (int i = 1; i < _routePoints.length; i++) {
            total += _calculateDistance(
                _routePoints[i - 1].lat, _routePoints[i - 1].lng,
                _routePoints[i].lat, _routePoints[i].lng,
            );
        }
        return total;
    }

    // Envoyer position simulée
    Future<void> _sendSimulatedPosition(String token, int orderId) async {
        LatLng point = _routePoints[_currentIndex];

        // Progression (%)
        double progress = (_currentIndex / _routePoints.length) * 100;

        // Distance restante (km)
        double distanceRemaining = _calculateRemainingDistance(_currentIndex);

        // Vitesse simulée (km/h)
        double speed = 40.0;

        // Temps estimé (secondes)
        int estimatedDuration = ((distanceRemaining / speed) * 3600).round();

        await http.patch(
            Uri.parse('$baseUrl/tracking/delivery/$orderId/position'),
            headers: {
                'Authorization': 'Bearer $token',
                'Content-Type': 'application/json',
            },
            body: jsonEncode({
                'driver_lat': point.lat,
                'driver_lng': point.lng,
                'current_speed': speed.toInt(),
                'progress_percentage': progress,
                'distance_remaining': distanceRemaining,
                'estimated_duration': estimatedDuration,
            }),
        );

        print('🚗 Simulation: ${progress.toStringAsFixed(0)}% - ${distanceRemaining.toStringAsFixed(2)}km');
    }

    // Calculer distance restante depuis position actuelle
    double _calculateRemainingDistance(int currentIndex) {
        double remaining = 0;
        for (int i = currentIndex + 1; i < _routePoints.length; i++) {
            remaining += _calculateDistance(
                _routePoints[i - 1].lat, _routePoints[i - 1].lng,
                _routePoints[i].lat, _routePoints[i].lng,
            );
        }
        return remaining;
    }

    // Formule Haversine
    double _calculateDistance(double lat1, double lon1, double lat2, double lon2) {
        const R = 6371;
        double dLat = _toRadians(lat2 - lat1);
        double dLon = _toRadians(lon2 - lon1);

        double a = sin(dLat / 2) * sin(dLat / 2) +
            cos(_toRadians(lat1)) * cos(_toRadians(lat2)) *
            sin(dLon / 2) * sin(dLon / 2);

        double c = 2 * asin(sqrt(a));
        return R * c;
    }

    double _toRadians(double d) => d * (pi / 180);

    void stopSimulation() {
        _timer?.cancel();
        _timer = null;
    }
}

class LatLng {
    final double lat;
    final double lng;
    LatLng(this.lat, this.lng);
}
```

### Exemple d'utilisation

```dart
// Simuler déplacement de 60 secondes
await simulationService.startSimulation(
    token,
    orderId,
    3.848, 11.502,  // Départ (centre de distribution)
    3.860, 11.520,  // Arrivée (client)
    durationSeconds: 60,
);
```

**Résultat** : 30 positions envoyées en 60 secondes (1 position toutes les 2 secondes) avec progression, distance et temps calculés automatiquement.

---

## ✅ 4. Terminer la Livraison

### Endpoint

```http
PATCH /api/tracking/delivery/{orderId}/complete
Authorization: Bearer {token}
```

### Code Flutter

```dart
Future<void> completeDelivery(String token, int orderId) async {
    final response = await http.patch(
        Uri.parse('$baseUrl/tracking/delivery/$orderId/complete'),
        headers: {
            'Authorization': 'Bearer $token',
            'Accept': 'application/json',
        },
    );

    if (response.statusCode == 200) {
        print('✅ Livraison terminée');
        // Arrêter tracking GPS
        locationTrackingService.stopTracking();
    }
}
```

---

## 📦 5. Dépendances Flutter

```yaml
dependencies:
  # HTTP pour API
  http: ^1.1.0

  # GPS
  geolocator: ^10.1.0

  # Google Maps (optionnel)
  google_maps_flutter: ^2.5.0
```

---

## 🔧 6. Configuration Android GPS

### `android/app/src/main/AndroidManifest.xml`

```xml
<manifest xmlns:android="http://schemas.android.com/apk/res/android">
    <!-- Permissions GPS -->
    <uses-permission android:name="android.permission.ACCESS_FINE_LOCATION" />
    <uses-permission android:name="android.permission.ACCESS_COARSE_LOCATION" />
    <uses-permission android:name="android.permission.INTERNET" />

    <application ...>
        <!-- Google Maps (si utilisé) -->
        <meta-data
            android:name="com.google.android.geo.API_KEY"
            android:value="AIzaSyB0w8HLsobdoJgK7WUTQxLFUuZOirvmUCI"/>
    </application>
</manifest>
```

---

## ✅ Checklist Tracking

- [ ] Démarrage tracking réussi (`POST /start`)
- [ ] GPS capte position actuelle
- [ ] Envoi position toutes les 3-5 secondes (`PATCH /position`)
- [ ] Calcul progression avec formule Haversine
- [ ] Calcul distance restante
- [ ] Calcul temps estimé
- [ ] Simulation testée (génération points + envoi automatique)
- [ ] Finalisation livraison (`PATCH /complete`)
- [ ] Permissions GPS accordées
- [ ] Client visualise en temps réel via WebSocket

---

## 🚨 Points Clés

### Calculs côté mobile (OBLIGATOIRE)

L'app mobile DOIT calculer et envoyer :

1. **`progress_percentage`** : `((distance_initiale - distance_restante) / distance_initiale) * 100`
2. **`distance_remaining`** : Formule Haversine entre position actuelle et destination
3. **`estimated_duration`** : `(distance_restante / vitesse) * 3600` (en secondes)

### Formule Haversine (Distance GPS)

```dart
double distance(lat1, lon1, lat2, lon2) {
    const R = 6371; // Rayon Terre (km)
    double dLat = toRadians(lat2 - lat1);
    double dLon = toRadians(lon2 - lon1);

    double a = sin(dLat/2) * sin(dLat/2) +
        cos(toRadians(lat1)) * cos(toRadians(lat2)) *
        sin(dLon/2) * sin(dLon/2);

    return R * 2 * asin(sqrt(a));
}
```

### Fréquence d'envoi

- **GPS réel** : Envoyer position toutes les **3-5 secondes**
- **Simulation** : Ajuster selon durée totale (ex: 60s ÷ 30 points = 2s)

### Simulation vs GPS Réel

- **GPS réel** : Pour livraisons réelles en production
- **Simulation** : Pour tests et démonstrations sans déplacement

### WebSocket automatique

Dès que le livreur envoie une position, le serveur diffuse automatiquement via WebSocket au client. **Aucune action requise du livreur**.

---

## 📞 Support & Tests

- **API Documentation** : `https://isogaz.afrik-solutions.com/api/documentation`
- **Test WebSocket Sender** (Livreur) : `https://isogaz.afrik-solutions.com/test-websocket-sender.html`
- **Test WebSocket Receiver** (Client) : `https://isogaz.afrik-solutions.com/test-websocket-receiver.html`

### Comment tester le WebSocket

1. **Ouvrir Sender** : Simuler envoi position livreur
2. **Ouvrir Receiver** : Visualiser réception en temps réel (comme le client)
3. Tester que les positions envoyées sont bien reçues instantanément
