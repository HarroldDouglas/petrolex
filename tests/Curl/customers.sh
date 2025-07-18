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

echo "🚀 Récupération de la liste des clients..."
CUSTOMERS_RESPONSE=$(curl -s -X GET "$BASE_URL/customers" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN")

echo "Liste des clients:"
echo "$CUSTOMERS_RESPONSE" | jq .

if [ $(echo "$CUSTOMERS_RESPONSE" | jq -r '._metadata.success // "false"') == "true" ]; then
    echo "✅ Récupération des clients réussie."
else
    echo "❌ Échec de la récupération des clients. Réponse: $CUSTOMERS_RESPONSE"
fi