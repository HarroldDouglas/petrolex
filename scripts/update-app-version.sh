#!/bin/bash
# Usage: ./scripts/update-app-version.sh <app_type> <version_code> <version_name> <update_required>
# Exemple: ./scripts/update-app-version.sh customer_app 12 1.0.12 true

APP_TYPE=${1:-customer_app}
VERSION_CODE=${2:-11}
VERSION_NAME=${3:-1.0.11}
UPDATE_REQUIRED=${4:-true}
TOKEN="2caf911181e6cecd2a31f0fd050051095f47ad9423fddf5f343f65002f796cac"
BASE_URL="https://isogaz.net"

curl -s -X PUT "$BASE_URL/api/app/version/$APP_TYPE" \
  -H "Content-Type: application/json" \
  -H "X-Mobile-Dev-Token: $TOKEN" \
  -d "{
    \"android\": {
      \"version_code\": $VERSION_CODE,
      \"version_name\": \"$VERSION_NAME\",
      \"update_required\": $UPDATE_REQUIRED,
      \"release_notes\": \"Commandez et faites vous livrer à domicile\",
      \"app_link\": \"https://play.google.com/store/apps/details?id=cm.petrolex.isogaz_customer_app\"
    },
    \"ios\": {
      \"version_code\": $VERSION_CODE,
      \"version_name\": \"$VERSION_NAME\",
      \"update_required\": $UPDATE_REQUIRED,
      \"release_notes\": \"Commandez et faites vous livrer à domicile\",
      \"app_link\": \"https://apps.apple.com/app/com.isogaz.customer_app\"
    }
  }" | jq .
