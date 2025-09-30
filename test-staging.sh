#!/bin/bash

# 🧪 Script de Test Staging - Petrolex Tracking System

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

print_test() {
    echo -e "${BLUE}[TEST]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[✅]${NC} $1"
}

print_error() {
    echo -e "${RED}[❌]${NC} $1"
}

# Lire la configuration depuis le .env
if [[ -f ".env" ]]; then
    DOMAIN=$(grep "^APP_URL=" .env | cut -d'=' -f2 | sed 's|https://||' | sed 's|http://||')
else
    echo "❌ Fichier .env introuvable"
    exit 1
fi

echo "🧪 Tests de Déploiement WebSocket - $DOMAIN"
echo "============================================"
echo ""

# Test 1: API Health Check
print_test "1. Test API accessibilité..."
if curl -s "https://$DOMAIN/api" > /dev/null 2>&1; then
    print_success "API accessible"
else
    print_error "API non accessible"
fi

# Test 2: WebSocket Port
print_test "2. Test port WebSocket 8080..."
if nc -zv $DOMAIN 8080 2>/dev/null; then
    print_success "Port 8080 ouvert"
else
    print_error "Port 8080 fermé ou inaccessible"
fi

# Test 3: Frontend Client
print_test "3. Test Interface Client..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "https://$DOMAIN/test/delivery-tracking/client/")
if [[ $HTTP_CODE == "200" ]]; then
    print_success "Interface Client accessible"
else
    print_error "Interface Client non accessible (HTTP $HTTP_CODE)"
fi

# Test 4: Frontend Delivery
print_test "4. Test Interface Livreur..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "https://$DOMAIN/test/delivery-tracking/delivery/")
if [[ $HTTP_CODE == "200" ]]; then
    print_success "Interface Livreur accessible"
else
    print_error "Interface Livreur non accessible (HTTP $HTTP_CODE)"
fi

# Test 5: Documentation API
print_test "5. Test Documentation API..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "https://$DOMAIN/api/documentation")
if [[ $HTTP_CODE == "200" ]]; then
    print_success "Documentation API accessible"
else
    print_error "Documentation API non accessible (HTTP $HTTP_CODE)"
fi

# Test 6: Variables .env WebSocket
print_test "6. Test variables WebSocket dans .env..."
WEBSOCKET_VARS=("BROADCAST_CONNECTION" "REVERB_APP_ID" "REVERB_APP_KEY" "REVERB_HOST")
MISSING_COUNT=0

for var in "${WEBSOCKET_VARS[@]}"; do
    if ! grep -q "^$var=" .env; then
        ((MISSING_COUNT++))
    fi
done

if [[ $MISSING_COUNT -eq 0 ]]; then
    print_success "Variables WebSocket configurées"
else
    print_error "$MISSING_COUNT variable(s) WebSocket manquante(s)"
fi

echo ""
echo "🔗 URLs à tester manuellement:"
echo "👥 Interface Client: https://$DOMAIN/test/delivery-tracking/client/"
echo "🚚 Interface Livreur: https://$DOMAIN/test/delivery-tracking/delivery/"
echo "📚 Documentation API: https://$DOMAIN/api/documentation"

echo ""
echo "👤 Comptes de test:"
echo "📧 Client: customer1@test.com / password"
echo "🚛 Livreur: delivery1@test.com / password"

echo ""
echo "✅ Tests terminés!"