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
if [ -z "$ADMIN_EMAIL" ] || [ -z "$ADMIN_PHONE" ] || [ -z "$ADMIN_PASSWORD" ]; then
    echo "❌ Variables d'environnement manquantes! Vérifiez ADMIN_EMAIL, ADMIN_PHONE et ADMIN_PASSWORD dans .env"
    exit 1
fi

# Fonction pour tester l'authentification
test_auth() {
    local login_type=$1
    local login_value=$2
    local password=$3

    echo "Test de connexion avec $login_type: $login_value"
    LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/login" \
      -H "Content-Type: application/json" \
      -d "{\"login\":\"$login_value\",\"password\":\"$password\"}")

    echo "Réponse de connexion: $LOGIN_RESPONSE"

    # Extraire le token
    TOKEN=$(echo "$LOGIN_RESPONSE" | jq -r '.data.access_token')

    if [ "$TOKEN" == "null" ]; then
      echo "❌ Échec de la connexion avec $login_type"
      return 1
    fi

    echo "✅ Token obtenu avec $login_type: $TOKEN"

    # Get Profile
    echo "\nRécupération du profil..."
    PROFILE_RESPONSE=$(curl -s -X GET "$BASE_URL/user" \
      -H "Accept: application/json" \
      -H "Authorization: Bearer $TOKEN")

    echo "Profil récupéré: $PROFILE_RESPONSE"

    # Logout
    echo "\nDéconnexion..."
    LOGOUT_RESPONSE=$(curl -s -X POST "$BASE_URL/logout" \
      -H "Accept: application/json" \
      -H "Authorization: Bearer $TOKEN")

    echo "Déconnexion effectuée: $LOGOUT_RESPONSE"

    # Vérification après déconnexion
    echo "\nVérification après déconnexion..."
    CHECK_RESPONSE=$(curl -s -X GET "$BASE_URL/user" \
      -H "Accept: application/json" \
      -H "Authorization: Bearer $TOKEN")

    if [[ $CHECK_RESPONSE == *"Unauthenticated"* ]]; then
        echo "✅ Déconnexion réussie"
    else
        echo "❌ Échec de la déconnexion"
    fi

    echo "\n----------------------------------------\n"
}

echo "🚀 Démarrage des tests d'authentification..."

# Test avec email
test_auth "email" "$ADMIN_EMAIL" "$ADMIN_PASSWORD"

# Test avec numéro de téléphone
test_auth "téléphone" "$ADMIN_PHONE" "$ADMIN_PASSWORD"

echo "✨ Tests d'authentification terminés!"