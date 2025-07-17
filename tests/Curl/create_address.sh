#!/bin/bash
#./tests/Curl/create_address.sh 1 "Maison principale" "123 Rue des Palmiers" "Bonanjo" "Douala" "Cameroun" "+237612345678" "Jean" "Dupont" "jean.dupont@email.com" "Près de la pharmacie centrale" true
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

if [ -z "$1" ] || [ -z "$2" ] || [ -z "$3" ]; then
    echo "Usage: $0 <CUSTOMER_ID> <LABEL> <ADDRESS> [NEIGHBORHOOD] [CITY] [COUNTRY] [PHONE] [CONTACT_FIRSTNAME] [CONTACT_LASTNAME] [EMAIL] [ADDRESS_PRECISION] [IS_DEFAULT]"
    exit 1
fi

CUSTOMER_ID=$1
LABEL=$2
ADDRESS=$3
NEIGHBORHOOD=${4:-""}
CITY=${5:-""}
COUNTRY=${6:-""}
PHONE=${7:-""}
CONTACT_FIRSTNAME=${8:-""}
CONTACT_LASTNAME=${9:-""}
EMAIL=${10:-""}
ADDRESS_PRECISION=${11:-""}
IS_DEFAULT=${12:-false}

# Construire le JSON manuellement
JSON_DATA="{"
JSON_DATA="$JSON_DATA\"label\": \"$LABEL\","
JSON_DATA="$JSON_DATA\"address\": \"$ADDRESS\""

if [ -n "$NEIGHBORHOOD" ]; then
    JSON_DATA="$JSON_DATA,\"neighborhood\": \"$NEIGHBORHOOD\""
fi

if [ -n "$CITY" ]; then
    JSON_DATA="$JSON_DATA,\"city\": \"$CITY\""
fi

if [ -n "$COUNTRY" ]; then
    JSON_DATA="$JSON_DATA,\"country\": \"$COUNTRY\""
fi

if [ -n "$PHONE" ]; then
    JSON_DATA="$JSON_DATA,\"phone\": \"$PHONE\""
fi

if [ -n "$CONTACT_FIRSTNAME" ]; then
    JSON_DATA="$JSON_DATA,\"contact_firstname\": \"$CONTACT_FIRSTNAME\""
fi

if [ -n "$CONTACT_LASTNAME" ]; then
    JSON_DATA="$JSON_DATA,\"contact_lastname\": \"$CONTACT_LASTNAME\""
fi

if [ -n "$EMAIL" ]; then
    JSON_DATA="$JSON_DATA,\"email\": \"$EMAIL\""
fi

if [ -n "$ADDRESS_PRECISION" ]; then
    JSON_DATA="$JSON_DATA,\"address_precision\": \"$ADDRESS_PRECISION\""
fi

if [ "$IS_DEFAULT" = "true" ]; then
    JSON_DATA="$JSON_DATA,\"is_default\": true"
else
    JSON_DATA="$JSON_DATA,\"is_default\": false"
fi

JSON_DATA="$JSON_DATA}"

echo "📤 Données envoyées :"
echo "$JSON_DATA" | jq .

CREATE_ADDRESS_RESPONSE=$(curl -s -X POST "$BASE_URL/customers/$CUSTOMER_ID/delivery-addresses" \
    -H "Accept: application/json" \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer $TOKEN" \
    -d "$JSON_DATA")

echo "📥 Réponse de création d'adresse:"
echo "$CREATE_ADDRESS_RESPONSE" | jq .

if [ $(echo "$CREATE_ADDRESS_RESPONSE" | jq -r '._metadata.success // "false"') == "true" ]; then
    echo "✅ Création d'adresse réussie."
else
    echo "❌ Échec de la création d'adresse."
fi
