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

# Chemin vers la racine du projet
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"

# Charger les variables depuis .env
if [ -f "$PROJECT_ROOT/.env" ]; then
    export $(cat "$PROJECT_ROOT/.env" | grep -v '#' | awk '/^[A-Z]/ {print}')
else
    echo "❌ Fichier .env non trouvé dans $PROJECT_ROOT!"
    exit 1
fi

# Vérifier que les variables requises sont définies
if [ -z "$ADMIN_EMAIL" ] || [ -z "$ADMIN_PASSWORD" ]; then
    echo "❌ Variables d'environnement manquantes! Vérifiez ADMIN_EMAIL et ADMIN_PASSWORD dans .env"
    exit 1
fi

echo "🚀 Tentative de connexion..."
LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/login" \
  -H "Content-Type: application/json" \
  -d "{\"login\":\"$ADMIN_EMAIL\",\"password\":\"$ADMIN_PASSWORD\"}")

TOKEN=$(echo "$LOGIN_RESPONSE" | jq -r '.data.access_token')

if [ "$TOKEN" == "null" ]; then
  echo "❌ Échec de la connexion. Réponse: $LOGIN_RESPONSE"
  exit 1
fi

echo "✅ Connexion réussie. Token obtenu: $TOKEN"
echo "$TOKEN" > "$TOKEN_FILE"
echo "Token enregistré dans $TOKEN_FILE"
