#!/bin/bash

BASE_URL="${APP_URL}/api"
TOKEN_FILE="$(dirname "${BASH_SOURCE[0]}")/token.txt"

# Chemin vers la racine du projet
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"

# Charger les variables depuis .env
if [ -f "$PROJECT_ROOT/.env" ]; then
    export $(cat "$PROJECT_ROOT/.env" | grep -v '#' | awk '/^[A-Z]/ {print}')
else
    echo "❌ Fichier .env non trouvé dans $PROJECT_ROOT!"
    exit 1
fi

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

echo "🚀 Récupération de la liste des centres de distribution..."
DC_RESPONSE=$(curl -s -X GET "$BASE_URL/distribution-centers" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN")

echo "Liste des centres de distribution:"
echo "$DC_RESPONSE" | jq .

if [ $(echo "$DC_RESPONSE" | jq -r '._metadata.success') == "true" ]; then
    echo "✅ Récupération des centres de distribution réussie."
else
    echo "❌ Échec de la récupération des centres de distribution. Réponse: $DC_RESPONSE"
fi
