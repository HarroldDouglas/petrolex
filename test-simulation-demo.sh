#!/bin/bash

# 🎭 Script de Test de Simulation - Démonstration Client
# Usage: ./test-simulation-demo.sh [order_id] [bearer_token]

set -e

# Configuration
BASE_URL="https://mondomaine.com/api"
ORDER_ID=${1:-"456"}
BEARER_TOKEN=${2:-"YOUR_BEARER_TOKEN_HERE"}

echo "🎭 Test de Simulation de Livraison - Démonstration Client"
echo "================================================"
echo "📍 Base URL: $BASE_URL"
echo "📦 Order ID: $ORDER_ID"
echo "🔐 Token: ${BEARER_TOKEN:0:20}..."
echo ""

# Fonction utilitaire pour les requêtes
make_request() {
    local method=$1
    local endpoint=$2
    local data=$3
    
    echo "🔗 $method $endpoint"
    
    if [ -n "$data" ]; then
        curl -s -X "$method" \
             -H "Authorization: Bearer $BEARER_TOKEN" \
             -H "Content-Type: application/json" \
             -H "Accept: application/json" \
             -d "$data" \
             "$BASE_URL$endpoint" | jq '.'
    else
        curl -s -X "$method" \
             -H "Authorization: Bearer $BEARER_TOKEN" \
             -H "Accept: application/json" \
             "$BASE_URL$endpoint" | jq '.'
    fi
    
    echo ""
}

# 1. Vérifier que l'API est accessible
echo "1️⃣ Test de connectivité API..."
make_request "GET" "/health"

# 2. Vérifier les détails de la commande
echo "2️⃣ Récupération des détails de la commande..."
ORDER_DETAILS=$(make_request "GET" "/tracking/delivery/$ORDER_ID")

# Extraire le order_number de la réponse
ORDER_NUMBER=$(echo "$ORDER_DETAILS" | jq -r '.data.order_number // "TEST-C1-1759146129-001"')
echo "📦 Order Number détecté: $ORDER_NUMBER"
echo ""

# 3. Démarrer la simulation
echo "3️⃣ Démarrage de la simulation..."
SIMULATION_DATA='{
    "duration_minutes": 3,
    "speed_multiplier": 2.0
}'

SIMULATION_RESPONSE=$(make_request "POST" "/demo/simulate-delivery/$ORDER_ID" "$SIMULATION_DATA")
SIMULATION_ID=$(echo "$SIMULATION_RESPONSE" | jq -r '.data.simulation_id // ""')

if [ -n "$SIMULATION_ID" ] && [ "$SIMULATION_ID" != "null" ]; then
    echo "✅ Simulation démarrée avec succès!"
    echo "🆔 Simulation ID: $SIMULATION_ID"
    echo "📡 WebSocket Channel: delivery-$ORDER_NUMBER"
    echo ""
else
    echo "❌ Échec du démarrage de la simulation"
    echo "$SIMULATION_RESPONSE"
    exit 1
fi

# 4. Attendre et vérifier le statut
echo "4️⃣ Vérification du statut de la simulation..."
sleep 2

STATUS_RESPONSE=$(make_request "GET" "/demo/simulation/$ORDER_ID/status")
echo "📊 Statut actuel:"
echo "$STATUS_RESPONSE" | jq '.data'
echo ""

# 5. Instructions pour le test WebSocket
echo "5️⃣ Instructions pour le test temps réel:"
echo "================================================"
echo "🌐 Ouvrir dans le navigateur:"
echo "   https://mondomaine.com/test-websocket-receiver.html"
echo ""
echo "📡 Le récepteur WebSocket devrait maintenant recevoir:"
echo "   - Canal: delivery-$ORDER_NUMBER"
echo "   - Événements: delivery-position-updated"
echo "   - Fréquence: toutes les 3 secondes environ"
echo ""
echo "📱 Pour tester l'app mobile:"
echo "   - Se connecter au WebSocket: wss://mondomaine.com:8080/app/petro-key-12345"
echo "   - Souscrire au canal: delivery-$ORDER_NUMBER"
echo "   - Écouter l'événement: delivery-position-updated"
echo ""

# 6. Attendre la fin de la simulation
echo "6️⃣ Surveillance de la simulation (3 minutes)..."
echo "⏱️ La simulation va durer environ 3 minutes..."
echo "🔄 Vérification du statut toutes les 30 secondes..."
echo ""

for i in {1..6}; do
    echo "🕐 Check $i/6 - $(date)"
    STATUS=$(make_request "GET" "/demo/simulation/$ORDER_ID/status" | jq -r '.data.status // "unknown"')
    PROGRESS=$(make_request "GET" "/demo/simulation/$ORDER_ID/status" | jq -r '.data.progress_percentage // 0')
    
    echo "   Status: $STATUS | Progress: $PROGRESS%"
    
    if [ "$STATUS" = "completed" ]; then
        echo "✅ Simulation terminée!"
        break
    elif [ "$STATUS" = "stopped" ]; then
        echo "⏹️ Simulation arrêtée"
        break
    fi
    
    sleep 30
done

echo ""

# 7. Statut final
echo "7️⃣ Statut final de la simulation..."
FINAL_STATUS=$(make_request "GET" "/demo/simulation/$ORDER_ID/status")
echo "📊 Statut final:"
echo "$FINAL_STATUS" | jq '.data'
echo ""

# 8. Nettoyage optionnel
echo "8️⃣ Commandes de nettoyage disponibles:"
echo "================================================"
echo "🛑 Pour arrêter la simulation manuellement:"
echo "   curl -X DELETE -H \"Authorization: Bearer $BEARER_TOKEN\" \\"
echo "        \"$BASE_URL/demo/simulation/$ORDER_ID/stop\""
echo ""
echo "📊 Pour vérifier le statut à tout moment:"
echo "   curl -H \"Authorization: Bearer $BEARER_TOKEN\" \\"
echo "        \"$BASE_URL/demo/simulation/$ORDER_ID/status\""
echo ""

echo "🎉 Test de simulation terminé avec succès!"
echo "================================================"
echo "📱 L'équipe mobile peut maintenant:"
echo "   1. Utiliser l'endpoint: POST /demo/simulate-delivery/{orderId}"
echo "   2. Se connecter au WebSocket pour recevoir les mises à jour"
echo "   3. Afficher le suivi temps réel sur la carte"
echo "   4. Tester avec différentes durées et vitesses"
echo ""
echo "📚 Documentation complète disponible dans:"
echo "   - BESOIN-MOBILE.md (guide intégration mobile)"
echo "   - WEBSOCKET-CHANNELS.md (documentation WebSocket)"
echo "   - DEPLOYMENT-GUIDE.md (configuration production)"