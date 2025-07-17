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
TOKEN_FILE="$(dirname "${BASH_SOURCE[0]}")/token.txt"

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

if [ -z "$1" ]; then
    echo "Usage: $0 <CUSTOMER_ID>"
    exit 1
fi

CUSTOMER_ID=$1

echo "🚀 Récupération des adresses pour le client ID: $CUSTOMER_ID..."
ADDRESSES_RESPONSE=$(curl -s -X GET "$BASE_URL/customers/$CUSTOMER_ID" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN")

echo "Adresses du client $CUSTOMER_ID:"
echo "$ADDRESSES_RESPONSE" | jq .

if [ $(echo "$ADDRESSES_RESPONSE" | jq -r '._metadata.success // "false"') == "true" ]; then
    echo "✅ Récupération des adresses réussie."
else
    echo "❌ Échec de la récupération des adresses. Réponse: $ADDRESSES_RESPONSE"
fi