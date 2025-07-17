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

if [ -z "$1" ] || [ -z "$2" ] || [ -z "$3" ]; then
    echo "Usage: $0 <CUSTOMER_ID> <LABEL> <ADDRESS> [NEIGHBORHOOD] [CITY] [COUNTRY] [PHONE] [CONTACT_FIRSTNAME] [CONTACT_LASTNAME] [EMAIL] [ADDRESS_PRECISION] [IS_DEFAULT]"
    exit 1
fi

CUSTOMER_ID=$1
LABEL=$2
ADDRESS=$3
NEIGHBORHOOD=${4:-null}
CITY=${5:-null}
COUNTRY=${6:-null}
PHONE=${7:-null}
CONTACT_FIRSTNAME=${8:-null}
CONTACT_LASTNAME=${9:-null}
EMAIL=${10:-null}
ADDRESS_PRECISION=${11:-null}
IS_DEFAULT=${12:-false}

# Construire le corps de la requête JSON
JSON_PAYLOAD=$(jq -n \
  --arg label "$LABEL" \
  --arg address "$ADDRESS" \
  --arg neighborhood "$NEIGHBORHOOD" \
  --arg city "$CITY" \
  --arg country "$COUNTRY" \
  --arg phone "$PHONE" \
  --arg contact_firstname "$CONTACT_FIRSTNAME" \
  --arg contact_lastname "$CONTACT_LASTNAME" \
  --arg email "$EMAIL" \
  --arg address_precision "$ADDRESS_PRECISION" \
  --argjson is_default "$IS_DEFAULT" \
  '{label: $label, address: $address, neighborhood: $neighborhood, city: $city, country: $country, phone: $phone, contact_firstname: $contact_firstname, contact_lastname: $contact_lastname, email: $email, address_precision: $address_precision, is_default: $is_default}')

echo "🚀 Création d'une adresse pour le client ID: $CUSTOMER_ID avec les données:"
echo "$JSON_PAYLOAD" | jq .

CREATE_ADDRESS_RESPONSE=$(curl -s -X POST "$BASE_URL/customers/$CUSTOMER_ID/delivery-addresses" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d "$JSON_PAYLOAD")

echo "Réponse de création d'adresse:"
echo "$CREATE_ADDRESS_RESPONSE" | jq .

if [ $(echo "$CREATE_ADDRESS_RESPONSE" | jq -r '._metadata.success') == "true" ]; then
    echo "✅ Création d'adresse réussie."
else
    echo "❌ Échec de la création d'adresse. Réponse: $CREATE_ADDRESS_RESPONSE"
fi
