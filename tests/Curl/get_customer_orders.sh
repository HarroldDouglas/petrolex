#!/bin/bash

# Chemin vers la racine du projet
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")"/../.. && pwd)"

# Charger les variables depuis .env
if [ -f "$PROJECT_ROOT/.env" ]; then
    export $(cat "$PROJECT_ROOT/.env" | grep -v '#' | awk '/^[A-Z]/ {print}')
else
    echo "❌ Fichier .env non trouvé dans $PROJECT_ROOT!"
    exit 1
fi

BASE_URL="${APP_URL}/api"
TOKEN_FILE="$(dirname "${BASH_SOURCE[0]}")"/token.txt"

# Vérifier si le fichier token.txt existe
if [ ! -f "$TOKEN_FILE" ]; then
    echo "❌ Fichier token.txt non trouvé. Veuillez d'abord exécuter auth.sh pour vous connecter."
    exit 1
fi

TOKEN=$(cat "$TOKEN_FILE")

if [ -z "$TOKEN" ]; then
    echo "❌ Token vide dans token.txt. Veuillez vous reconnecter via auth.sh."
    exit 1
fi

# Récupérer le premier client pour le test
CUSTOMER_ID=$(curl -s -X GET "$BASE_URL/customers" -H "Authorization: Bearer $TOKEN" | jq -r '.data[0].id')

if [ -z "$CUSTOMER_ID" ] || [ "$CUSTOMER_ID" == "null" ]; then
    echo "❌ Aucun client trouvé pour le test."
    exit 1
fi

echo "🚀 Récupération des commandes pour le client ID: $CUSTOMER_ID..."
ORDERS_RESPONSE=$(curl -s -X GET "$BASE_URL/customers/$CUSTOMER_ID/orders" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN")

echo "Commandes du client:"
echo "$ORDERS_RESPONSE" | jq .

if [ $(echo "$ORDERS_RESPONSE" | jq -r '._metadata.success // "false"') == "true" ]; then
    echo "✅ Récupération des commandes du client réussie."
else
    echo "❌ Échec de la récupération des commandes du client. Réponse: $ORDERS_RESPONSE"
fi
