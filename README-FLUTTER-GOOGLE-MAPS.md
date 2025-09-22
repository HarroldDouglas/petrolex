# 📱 Flutter Google Maps - Guide d'intégration

Ce guide vous aide à intégrer Google Maps et le système de tracking en temps réel dans votre application Flutter mobile pour le projet Petrolex.

## 🎯 Vue d'ensemble

L'application web utilise Google Maps avec les fonctionnalités suivantes que vous devez reproduire en Flutter :
- **Tracking en temps réel** des livreurs
- **Recherche de lieux** (autocomplete) pour le Cameroun
- **Calcul d'itinéraires** et navigation
- **Simulation de livraison** pour les tests
- **WebSocket** pour les mises à jour temps réel

## 🔧 Configuration initiale

### 1. Google Maps API Key
Utilisez la même clé API que le projet web :
```
AIzaSyB0w8HLsobdoJgK7WUTQxLFUuZOirvmUCI
```

### 2. APIs Google Cloud activées nécessaires
- **Maps SDK for Android**
- **Maps SDK for iOS** 
- **Places API** (pour l'autocomplete)
- **Directions API** (pour les itinéraires)
- **Geocoding API** (pour conversion adresse ↔ coordonnées)

### 3. Configuration Flutter

#### pubspec.yaml
```yaml
dependencies:
  flutter:
    sdk: flutter
  google_maps_flutter: ^2.5.0
  google_polyline_algorithm: ^3.1.0
  geolocator: ^10.1.0
  geocoding: ^2.1.1
  http: ^1.1.0
  web_socket_channel: ^2.4.0
  flutter_dotenv: ^5.1.0

dev_dependencies:
  flutter_test:
    sdk: flutter
```

#### Configuration Android (android/app/src/main/AndroidManifest.xml)
```xml
<application>
    <!-- Autres configurations -->
    
    <meta-data android:name="com.google.android.geo.API_KEY"
               android:value="AIzaSyB0w8HLsobdoJgK7WUTQxLFUuZOirvmUCI"/>
</application>

<uses-permission android:name="android.permission.ACCESS_FINE_LOCATION" />
<uses-permission android:name="android.permission.ACCESS_COARSE_LOCATION" />
<uses-permission android:name="android.permission.INTERNET" />
```

#### Configuration iOS (ios/Runner/AppDelegate.swift)
```swift
import UIKit
import Flutter
import GoogleMaps

@UIApplicationMain
@objc class AppDelegate: FlutterAppDelegate {
  override func application(
    _ application: UIApplication,
    didFinishLaunchingWithOptions launchOptions: [UIApplication.LaunchOptionsKey: Any]?
  ) -> Bool {
    GMSServices.provideAPIKey("AIzaSyB0w8HLsobdoJgK7WUTQxLFUuZOirvmUCI")
    GeneratedPluginRegistrant.register(with: self)
    return super.application(application, didFinishLaunchingWithOptions: launchOptions)
  }
}
```

## 🌍 Configuration spécifique Cameroun

### Coordonnées par défaut
```dart
class CameroonConfig {
  static const LatLng defaultCenter = LatLng(3.848, 11.502); // Yaoundé
  static const double defaultZoom = 12.0;
  
  // Limites géographiques du Cameroun
  static const LatLngBounds cameroonBounds = LatLngBounds(
    southwest: LatLng(1.6, 8.5),
    northeast: LatLng(13.0, 16.2),
  );
  
  // Grandes villes
  static const Map<String, LatLng> majorCities = {
    'Yaoundé': LatLng(3.848, 11.502),
    'Douala': LatLng(4.0435, 9.7009),
    'Bamenda': LatLng(5.9597, 10.1606),
    'Bafoussam': LatLng(5.4781, 10.4206),
  };
}
```

## 🗺️ Intégration Google Maps

### 1. Widget GoogleMap de base
```dart
class DeliveryTrackingMap extends StatefulWidget {
  @override
  _DeliveryTrackingMapState createState() => _DeliveryTrackingMapState();
}

class _DeliveryTrackingMapState extends State<DeliveryTrackingMap> {
  GoogleMapController? _controller;
  Set<Marker> _markers = {};
  Set<Polyline> _polylines = {};
  
  @override
  Widget build(BuildContext context) {
    return GoogleMap(
      onMapCreated: (GoogleMapController controller) {
        _controller = controller;
      },
      initialCameraPosition: CameraPosition(
        target: CameroonConfig.defaultCenter,
        zoom: CameroonConfig.defaultZoom,
      ),
      markers: _markers,
      polylines: _polylines,
      myLocationEnabled: true,
      myLocationButtonEnabled: true,
      mapType: MapType.normal,
      zoomControlsEnabled: true,
      compassEnabled: true,
      trafficEnabled: false,
    );
  }
}
```

### 2. Recherche de lieux (Autocomplete)
```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

class PlacesService {
  static const String _apiKey = 'AIzaSyB0w8HLsobdoJgK7WUTQxLFUuZOirvmUCI';
  static const String _baseUrl = 'https://maps.googleapis.com/maps/api/place';
  
  static Future<List<PlacePrediction>> searchPlaces(String query) async {
    if (query.isEmpty) return [];
    
    final url = Uri.parse(
      '$_baseUrl/autocomplete/json'
      '?input=$query'
      '&key=$_apiKey'
      '&components=country:cm'  // Limite au Cameroun
      '&language=fr'
      '&types=establishment|geocode'
    );
    
    try {
      final response = await http.get(url);
      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        final List predictions = data['predictions'] ?? [];
        
        return predictions
            .map((json) => PlacePrediction.fromJson(json))
            .toList();
      }
    } catch (e) {
      print('Erreur recherche lieux: $e');
    }
    
    return [];
  }
  
  static Future<LatLng?> getPlaceCoordinates(String placeId) async {
    final url = Uri.parse(
      '$_baseUrl/details/json'
      '?place_id=$placeId'
      '&key=$_apiKey'
      '&fields=geometry'
    );
    
    try {
      final response = await http.get(url);
      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        final location = data['result']['geometry']['location'];
        
        return LatLng(location['lat'], location['lng']);
      }
    } catch (e) {
      print('Erreur coordonnées lieu: $e');
    }
    
    return null;
  }
}

class PlacePrediction {
  final String placeId;
  final String description;
  final String mainText;
  final String secondaryText;
  
  PlacePrediction({
    required this.placeId,
    required this.description,
    required this.mainText,
    required this.secondaryText,
  });
  
  factory PlacePrediction.fromJson(Map<String, dynamic> json) {
    return PlacePrediction(
      placeId: json['place_id'],
      description: json['description'],
      mainText: json['structured_formatting']['main_text'] ?? '',
      secondaryText: json['structured_formatting']['secondary_text'] ?? '',
    );
  }
}
```

### 3. Widget de recherche autocomplete
```dart
class PlaceSearchWidget extends StatefulWidget {
  final Function(LatLng) onPlaceSelected;
  
  const PlaceSearchWidget({Key? key, required this.onPlaceSelected}) : super(key: key);
  
  @override
  _PlaceSearchWidgetState createState() => _PlaceSearchWidgetState();
}

class _PlaceSearchWidgetState extends State<PlaceSearchWidget> {
  final TextEditingController _controller = TextEditingController();
  List<PlacePrediction> _predictions = [];
  bool _isLoading = false;
  
  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        TextField(
          controller: _controller,
          decoration: InputDecoration(
            hintText: 'Rechercher un lieu au Cameroun...',
            prefixIcon: Icon(Icons.search),
            suffixIcon: _isLoading 
                ? SizedBox(width: 20, height: 20, child: CircularProgressIndicator())
                : null,
            border: OutlineInputBorder(),
          ),
          onChanged: _onSearchChanged,
        ),
        if (_predictions.isNotEmpty)
          Container(
            height: 200,
            child: ListView.builder(
              itemCount: _predictions.length,
              itemBuilder: (context, index) {
                final prediction = _predictions[index];
                return ListTile(
                  leading: Icon(Icons.location_on),
                  title: Text(prediction.mainText),
                  subtitle: Text(prediction.secondaryText),
                  onTap: () => _selectPlace(prediction),
                );
              },
            ),
          ),
      ],
    );
  }
  
  void _onSearchChanged(String query) async {
    if (query.length < 3) {
      setState(() => _predictions.clear());
      return;
    }
    
    setState(() => _isLoading = true);
    
    final predictions = await PlacesService.searchPlaces(query);
    
    setState(() {
      _predictions = predictions;
      _isLoading = false;
    });
  }
  
  void _selectPlace(PlacePrediction prediction) async {
    final coordinates = await PlacesService.getPlaceCoordinates(prediction.placeId);
    
    if (coordinates != null) {
      widget.onPlaceSelected(coordinates);
      _controller.text = prediction.mainText;
      setState(() => _predictions.clear());
    }
  }
}
```

## 🛣️ Calcul d'itinéraires

```dart
class DirectionsService {
  static const String _apiKey = 'AIzaSyB0w8HLsobdoJgK7WUTQxLFUuZOirvmUCI';
  
  static Future<DirectionResult?> getDirections({
    required LatLng origin,
    required LatLng destination,
    String travelMode = 'driving',
  }) async {
    final url = Uri.parse(
      'https://maps.googleapis.com/maps/api/directions/json'
      '?origin=${origin.latitude},${origin.longitude}'
      '&destination=${destination.latitude},${destination.longitude}'
      '&mode=$travelMode'
      '&key=$_apiKey'
      '&language=fr'
      '&region=cm'
    );
    
    try {
      final response = await http.get(url);
      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        
        if (data['status'] == 'OK' && data['routes'].isNotEmpty) {
          return DirectionResult.fromJson(data['routes'][0]);
        }
      }
    } catch (e) {
      print('Erreur directions: $e');
    }
    
    return null;
  }
}

class DirectionResult {
  final List<LatLng> points;
  final String duration;
  final String distance;
  final String durationValue; // en secondes
  final String distanceValue; // en mètres
  
  DirectionResult({
    required this.points,
    required this.duration,
    required this.distance,
    required this.durationValue,
    required this.distanceValue,
  });
  
  factory DirectionResult.fromJson(Map<String, dynamic> json) {
    // Décoder la polyligne
    final polylinePoints = GooglePolylineAlgorithm.decode(
      json['overview_polyline']['points']
    );
    
    final leg = json['legs'][0];
    
    return DirectionResult(
      points: polylinePoints.map((point) => LatLng(point[0], point[1])).toList(),
      duration: leg['duration']['text'],
      distance: leg['distance']['text'],
      durationValue: leg['duration']['value'].toString(),
      distanceValue: leg['distance']['value'].toString(),
    );
  }
}
```

## 🚗 Tracking en temps réel

### 1. Modèle de données
```dart
class DeliveryTracking {
  final String orderId;
  final String driverName;
  final String driverPhone;
  final LatLng driverPosition;
  final LatLng destinationPosition;
  final String status;
  final double progressPercentage;
  final String estimatedArrival;
  final List<TrackingEvent> history;
  
  DeliveryTracking({
    required this.orderId,
    required this.driverName,
    required this.driverPhone,
    required this.driverPosition,
    required this.destinationPosition,
    required this.status,
    required this.progressPercentage,
    required this.estimatedArrival,
    required this.history,
  });
  
  factory DeliveryTracking.fromJson(Map<String, dynamic> json) {
    return DeliveryTracking(
      orderId: json['order_id'],
      driverName: json['driver_info']['name'],
      driverPhone: json['driver_info']['phone'],
      driverPosition: LatLng(
        json['driver_position']['lat'],
        json['driver_position']['lng'],
      ),
      destinationPosition: LatLng(
        json['customer_position']['lat'],
        json['customer_position']['lng'],
      ),
      status: json['status'],
      progressPercentage: json['progress_percentage'].toDouble(),
      estimatedArrival: json['estimated_arrival'],
      history: (json['history'] as List)
          .map((e) => TrackingEvent.fromJson(e))
          .toList(),
    );
  }
}

class TrackingEvent {
  final String message;
  final String timestamp;
  final String type;
  
  TrackingEvent({
    required this.message,
    required this.timestamp,
    required this.type,
  });
  
  factory TrackingEvent.fromJson(Map<String, dynamic> json) {
    return TrackingEvent(
      message: json['message'],
      timestamp: json['timestamp'],
      type: json['type'],
    );
  }
}
```

### 2. Service WebSocket
```dart
import 'package:web_socket_channel/web_socket_channel.dart';

class DeliveryWebSocketService {
  static const String wsUrl = 'ws://127.0.0.1:8080/app/your-app-key';
  WebSocketChannel? _channel;
  StreamController<DeliveryTracking> _trackingController = StreamController();
  
  Stream<DeliveryTracking> get trackingStream => _trackingController.stream;
  
  void connect() {
    try {
      _channel = WebSocketChannel.connect(Uri.parse(wsUrl));
      
      _channel!.stream.listen(
        (data) {
          final json = jsonDecode(data);
          final tracking = DeliveryTracking.fromJson(json);
          _trackingController.add(tracking);
        },
        onError: (error) {
          print('Erreur WebSocket: $error');
          _reconnect();
        },
        onDone: () {
          print('WebSocket fermé');
          _reconnect();
        },
      );
      
      // S'abonner au canal de tracking
      _channel!.sink.add(jsonEncode({
        'event': 'pusher:subscribe',
        'data': {'channel': 'delivery-tracking'}
      }));
      
    } catch (e) {
      print('Erreur connexion WebSocket: $e');
      _reconnect();
    }
  }
  
  void _reconnect() {
    Future.delayed(Duration(seconds: 5), () {
      connect();
    });
  }
  
  void subscribeToOrder(String orderId) {
    if (_channel != null) {
      _channel!.sink.add(jsonEncode({
        'event': 'start-tracking',
        'data': {'order_id': orderId}
      }));
    }
  }
  
  void disconnect() {
    _channel?.sink.close();
    _trackingController.close();
  }
}
```

## 🔗 Intégration Backend Laravel

### 1. Service API
```dart
class PetrolexApiService {
  static const String baseUrl = 'http://127.0.0.1:8001/api';
  final http.Client _client = http.Client();
  String? _authToken;
  
  // Authentification
  Future<bool> login(String email, String password) async {
    try {
      final response = await _client.post(
        Uri.parse('$baseUrl/login'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'email': email,
          'password': password,
        }),
      );
      
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        _authToken = data['data']['token'];
        return true;
      }
    } catch (e) {
      print('Erreur login: $e');
    }
    return false;
  }
  
  // Récupérer mes commandes
  Future<List<Order>> getMyOrders({
    Map<String, String>? filters,
    int page = 1,
  }) async {
    try {
      String url = '$baseUrl/my/orders?page=$page';
      
      if (filters != null) {
        filters.forEach((key, value) {
          url += '&$key=$value';
        });
      }
      
      final response = await _client.get(
        Uri.parse(url),
        headers: _getAuthHeaders(),
      );
      
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        final orders = (data['data']['data'] as List)
            .map((json) => Order.fromJson(json))
            .toList();
        return orders;
      }
    } catch (e) {
      print('Erreur récupération commandes: $e');
    }
    return [];
  }
  
  // Démarrer le tracking d'une commande
  Future<DeliveryTracking?> startTracking(String orderId) async {
    try {
      final response = await _client.post(
        Uri.parse('$baseUrl/delivery/tracking/$orderId/start'),
        headers: _getAuthHeaders(),
      );
      
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return DeliveryTracking.fromJson(data['data']);
      }
    } catch (e) {
      print('Erreur démarrage tracking: $e');
    }
    return null;
  }
  
  Map<String, String> _getAuthHeaders() {
    return {
      'Content-Type': 'application/json',
      'Authorization': 'Bearer $_authToken',
    };
  }
}

class Order {
  final String id;
  final String orderNumber;
  final String status;
  final double totalAmount;
  final String orderDate;
  final Map<String, dynamic> deliveryAddress;
  
  Order({
    required this.id,
    required this.orderNumber,
    required this.status,
    required this.totalAmount,
    required this.orderDate,
    required this.deliveryAddress,
  });
  
  factory Order.fromJson(Map<String, dynamic> json) {
    return Order(
      id: json['id'].toString(),
      orderNumber: json['order_number'],
      status: json['status'],
      totalAmount: double.parse(json['total_amount'].toString()),
      orderDate: json['order_date'],
      deliveryAddress: json['delivery_address'] ?? {},
    );
  }
  
  bool get isTrackable => status == 'processing';
}
```

## 🎮 Simulation pour les tests

```dart
class DeliverySimulation {
  Timer? _timer;
  final Function(DeliveryTracking) onUpdate;
  final DirectionResult route;
  int currentStep = 0;
  final int totalSteps;
  final Duration updateInterval;
  
  DeliverySimulation({
    required this.onUpdate,
    required this.route,
    this.totalSteps = 50,
    this.updateInterval = const Duration(seconds: 2),
  });
  
  void start() {
    _timer = Timer.periodic(updateInterval, (timer) {
      if (currentStep >= totalSteps) {
        stop();
        return;
      }
      
      final progress = currentStep / totalSteps;
      final pointIndex = (progress * (route.points.length - 1)).floor();
      final currentPosition = route.points[pointIndex];
      
      final tracking = DeliveryTracking(
        orderId: 'simulation',
        driverName: 'Livreur Test',
        driverPhone: '+237699123456',
        driverPosition: currentPosition,
        destinationPosition: route.points.last,
        status: progress >= 1 ? 'delivered' : 'en_route',
        progressPercentage: progress * 100,
        estimatedArrival: DateTime.now()
            .add(Duration(minutes: ((1 - progress) * 30).round()))
            .toIso8601String(),
        history: [],
      );
      
      onUpdate(tracking);
      currentStep++;
    });
  }
  
  void stop() {
    _timer?.cancel();
    _timer = null;
  }
  
  void setSpeed(Duration newInterval) {
    if (_timer != null) {
      stop();
      updateInterval = newInterval;
      start();
    }
  }
}
```

## 📱 Interface utilisateur complète

```dart
class DeliveryTrackingScreen extends StatefulWidget {
  final String orderId;
  
  const DeliveryTrackingScreen({Key? key, required this.orderId}) : super(key: key);
  
  @override
  _DeliveryTrackingScreenState createState() => _DeliveryTrackingScreenState();
}

class _DeliveryTrackingScreenState extends State<DeliveryTrackingScreen> {
  GoogleMapController? _mapController;
  DeliveryTracking? _currentTracking;
  Set<Marker> _markers = {};
  Set<Polyline> _polylines = {};
  
  final DeliveryWebSocketService _wsService = DeliveryWebSocketService();
  final PetrolexApiService _apiService = PetrolexApiService();
  DeliverySimulation? _simulation;
  
  @override
  void initState() {
    super.initState();
    _initializeTracking();
  }
  
  void _initializeTracking() async {
    // Connecter WebSocket
    _wsService.connect();
    _wsService.trackingStream.listen(_updateTracking);
    
    // Démarrer le tracking
    final tracking = await _apiService.startTracking(widget.orderId);
    if (tracking != null) {
      _updateTracking(tracking);
      _wsService.subscribeToOrder(widget.orderId);
    }
  }
  
  void _updateTracking(DeliveryTracking tracking) async {
    setState(() {
      _currentTracking = tracking;
    });
    
    await _updateMapMarkers(tracking);
    await _updateRoute(tracking);
    _centerMapOnTracking(tracking);
  }
  
  Future<void> _updateMapMarkers(DeliveryTracking tracking) async {
    final driverMarker = Marker(
      markerId: MarkerId('driver'),
      position: tracking.driverPosition,
      infoWindow: InfoWindow(
        title: '🚗 ${tracking.driverName}',
        snippet: 'Progression: ${tracking.progressPercentage.round()}%',
      ),
      icon: await _createDriverIcon(),
    );
    
    final destinationMarker = Marker(
      markerId: MarkerId('destination'),
      position: tracking.destinationPosition,
      infoWindow: InfoWindow(
        title: '📍 Destination',
        snippet: 'Adresse de livraison',
      ),
      icon: BitmapDescriptor.defaultMarkerWithHue(BitmapDescriptor.hueRed),
    );
    
    setState(() {
      _markers = {driverMarker, destinationMarker};
    });
  }
  
  Future<void> _updateRoute(DeliveryTracking tracking) async {
    final directions = await DirectionsService.getDirections(
      origin: tracking.driverPosition,
      destination: tracking.destinationPosition,
    );
    
    if (directions != null) {
      final polyline = Polyline(
        polylineId: PolylineId('route'),
        points: directions.points,
        color: Colors.blue,
        width: 5,
        patterns: [],
      );
      
      setState(() {
        _polylines = {polyline};
      });
    }
  }
  
  void _centerMapOnTracking(DeliveryTracking tracking) {
    if (_mapController != null) {
      final bounds = LatLngBounds(
        southwest: LatLng(
          math.min(tracking.driverPosition.latitude, tracking.destinationPosition.latitude) - 0.01,
          math.min(tracking.driverPosition.longitude, tracking.destinationPosition.longitude) - 0.01,
        ),
        northeast: LatLng(
          math.max(tracking.driverPosition.latitude, tracking.destinationPosition.latitude) + 0.01,
          math.max(tracking.driverPosition.longitude, tracking.destinationPosition.longitude) + 0.01,
        ),
      );
      
      _mapController!.animateCamera(
        CameraUpdate.newLatLngBounds(bounds, 100),
      );
    }
  }
  
  Future<BitmapDescriptor> _createDriverIcon() async {
    // Créer une icône personnalisée pour le livreur
    return BitmapDescriptor.defaultMarkerWithHue(BitmapDescriptor.hueBlue);
  }
  
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Suivi de livraison'),
        backgroundColor: Colors.blue,
      ),
      body: Column(
        children: [
          // Carte
          Expanded(
            flex: 2,
            child: GoogleMap(
              onMapCreated: (controller) => _mapController = controller,
              initialCameraPosition: CameraPosition(
                target: CameroonConfig.defaultCenter,
                zoom: 12,
              ),
              markers: _markers,
              polylines: _polylines,
              myLocationEnabled: true,
              myLocationButtonEnabled: true,
            ),
          ),
          
          // Informations de tracking
          Expanded(
            flex: 1,
            child: _buildTrackingInfo(),
          ),
        ],
      ),
      
      // Boutons de contrôle
      floatingActionButton: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          FloatingActionButton(
            heroTag: "simulate",
            onPressed: _startSimulation,
            child: Icon(Icons.play_arrow),
            backgroundColor: Colors.orange,
          ),
          SizedBox(height: 10),
          FloatingActionButton(
            heroTag: "delivered",
            onPressed: _markAsDelivered,
            child: Icon(Icons.check),
            backgroundColor: Colors.green,
          ),
        ],
      ),
    );
  }
  
  Widget _buildTrackingInfo() {
    if (_currentTracking == null) {
      return Center(child: CircularProgressIndicator());
    }
    
    return Container(
      padding: EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Informations du livreur
          Card(
            child: ListTile(
              leading: Icon(Icons.person, color: Colors.blue),
              title: Text(_currentTracking!.driverName),
              subtitle: Text(_currentTracking!.driverPhone),
              trailing: Chip(
                label: Text(_currentTracking!.status),
                backgroundColor: _getStatusColor(_currentTracking!.status),
              ),
            ),
          ),
          
          // Barre de progression
          SizedBox(height: 10),
          Text('Progression: ${_currentTracking!.progressPercentage.round()}%'),
          SizedBox(height: 5),
          LinearProgressIndicator(
            value: _currentTracking!.progressPercentage / 100,
            backgroundColor: Colors.grey[300],
            valueColor: AlwaysStoppedAnimation<Color>(Colors.blue),
          ),
          
          // Historique
          SizedBox(height: 10),
          Text('Historique:', style: TextStyle(fontWeight: FontWeight.bold)),
          Expanded(
            child: ListView.builder(
              itemCount: _currentTracking!.history.length,
              itemBuilder: (context, index) {
                final event = _currentTracking!.history[index];
                return ListTile(
                  dense: true,
                  leading: Icon(Icons.history, size: 16),
                  title: Text(event.message, style: TextStyle(fontSize: 14)),
                  trailing: Text(event.timestamp, style: TextStyle(fontSize: 12)),
                );
              },
            ),
          ),
        ],
      ),
    );
  }
  
  Color _getStatusColor(String status) {
    switch (status) {
      case 'en_route': return Colors.blue;
      case 'delivered': return Colors.green;
      case 'cancelled': return Colors.red;
      default: return Colors.grey;
    }
  }
  
  void _startSimulation() async {
    if (_currentTracking != null) {
      final directions = await DirectionsService.getDirections(
        origin: _currentTracking!.driverPosition,
        destination: _currentTracking!.destinationPosition,
      );
      
      if (directions != null) {
        _simulation = DeliverySimulation(
          onUpdate: _updateTracking,
          route: directions,
        );
        _simulation!.start();
      }
    }
  }
  
  void _markAsDelivered() {
    if (_currentTracking != null) {
      final deliveredTracking = DeliveryTracking(
        orderId: _currentTracking!.orderId,
        driverName: _currentTracking!.driverName,
        driverPhone: _currentTracking!.driverPhone,
        driverPosition: _currentTracking!.destinationPosition,
        destinationPosition: _currentTracking!.destinationPosition,
        status: 'delivered',
        progressPercentage: 100,
        estimatedArrival: DateTime.now().toIso8601String(),
        history: [
          ..._currentTracking!.history,
          TrackingEvent(
            message: 'Livraison terminée',
            timestamp: DateTime.now().toString(),
            type: 'success',
          ),
        ],
      );
      
      _updateTracking(deliveredTracking);
      _simulation?.stop();
    }
  }
  
  @override
  void dispose() {
    _simulation?.stop();
    _wsService.disconnect();
    super.dispose();
  }
}
```

## 🛠️ Conseils de développement

### 1. Gestion des erreurs
```dart
class ErrorHandler {
  static void handleApiError(http.Response response) {
    switch (response.statusCode) {
      case 401:
        // Token expiré, rediriger vers login
        break;
      case 404:
        // Ressource non trouvée
        break;
      case 500:
        // Erreur serveur
        break;
    }
  }
  
  static void handleMapError(String error) {
    print('Erreur Google Maps: $error');
    // Afficher message à l'utilisateur
  }
}
```

### 2. Optimisation des performances
```dart
class PerformanceOptimizer {
  // Limiter les mises à jour de position
  static const Duration minUpdateInterval = Duration(seconds: 2);
  static DateTime? lastUpdate;
  
  static bool shouldUpdatePosition() {
    final now = DateTime.now();
    if (lastUpdate == null || now.difference(lastUpdate!) >= minUpdateInterval) {
      lastUpdate = now;
      return true;
    }
    return false;
  }
  
  // Réduire la précision des coordonnées pour économiser la bande passante
  static LatLng optimizeCoordinates(LatLng coords) {
    return LatLng(
      double.parse(coords.latitude.toStringAsFixed(6)),
      double.parse(coords.longitude.toStringAsFixed(6)),
    );
  }
}
```

### 3. Tests unitaires
```dart
void main() {
  group('PlacesService Tests', () {
    test('should return places for valid query', () async {
      final places = await PlacesService.searchPlaces('Yaoundé');
      expect(places.isNotEmpty, true);
    });
    
    test('should return coordinates for valid place ID', () async {
      const placeId = 'test_place_id';
      final coords = await PlacesService.getPlaceCoordinates(placeId);
      expect(coords, isNotNull);
    });
  });
  
  group('DirectionsService Tests', () {
    test('should return route between two points', () async {
      final origin = LatLng(3.848, 11.502);
      final destination = LatLng(4.0435, 9.7009);
      
      final result = await DirectionsService.getDirections(
        origin: origin,
        destination: destination,
      );
      
      expect(result, isNotNull);
      expect(result!.points.isNotEmpty, true);
    });
  });
}
```

## 🚀 Déploiement

### 1. Configuration de production
```dart
class Config {
  static const bool isProduction = bool.fromEnvironment('dart.vm.product');
  
  static String get apiBaseUrl {
    return isProduction 
        ? 'https://your-production-api.com/api'
        : 'http://127.0.0.1:8001/api';
  }
  
  static String get wsUrl {
    return isProduction
        ? 'wss://your-production-ws.com:8080/app/your-app-key'
        : 'ws://127.0.0.1:8080/app/your-app-key';
  }
}
```

### 2. Obfuscation des clés API
```yaml
# flutter_build.yaml
targets:
  $default:
    builders:
      flutter_native_splash:background_image_android:
        options:
          android_obfuscate_api_keys: true
```

## 📞 Support et documentation

- **Documentation Google Maps Flutter** : https://pub.dev/packages/google_maps_flutter
- **API Google Places** : https://developers.google.com/maps/documentation/places/web-service
- **API Google Directions** : https://developers.google.com/maps/documentation/directions

## 📝 Notes importantes

1. **Limites d'API** : Surveillez l'utilisation des API Google pour éviter les dépassements
2. **Permissions** : Demandez les permissions de géolocalisation au premier lancement
3. **Batterie** : Optimisez la fréquence des mises à jour pour préserver la batterie
4. **Connectivité** : Gérez les cas de perte de connexion réseau
5. **Cache** : Implémentez un cache local pour les données critiques

Cette documentation vous donne tous les éléments nécessaires pour implémenter un système de tracking complet en Flutter avec Google Maps ! 🚀