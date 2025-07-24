#!/bin/bash

# Chemin vers la racine du projet
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"

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

# Générer des données de test uniques
TIMESTAMP=$(date +%s)
EMAIL="testuser$TIMESTAMP@example.com"
PHONE="123456$TIMESTAMP"

echo "🚀 Création d'un nouveau client..."
CREATE_RESPONSE=$(curl -s -X POST "$BASE_URL/customers" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d "{
    \"first_name\": \"Test\",
    \"last_name\": \"User $TIMESTAMP\",
    \"email\": \"$EMAIL\",
    \"phone_number\": \"$PHONE\",
    \"password\": \"password123\",
    \"current_balance\": 100
  }")

echo "Réponse de la création:"
echo "$CREATE_RESPONSE" | jq .

if [ $(echo "$CREATE_RESPONSE" | jq -r '._metadata.success // "false"') == "true" ]; then
    echo "✅ Création du client réussie."
else
    echo "❌ Échec de la création du client. Réponse: $CREATE_RESPONSE"
fi
