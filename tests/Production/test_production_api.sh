#!/bin/bash
#
# Production API Test Suite
# Tests all major endpoints with real authentication
#
# Usage: bash tests/Production/test_production_api.sh
#

set -e

BASE_URL="https://isogaz.afrik-solutions.com"
GREEN='\033[0;32m'
RED='\033[0;31m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${BLUE}================================================${NC}"
echo -e "${BLUE}  PRODUCTION API TEST - ISOGAZ${NC}"
echo -e "${BLUE}  Testing: $BASE_URL${NC}"
echo -e "${BLUE}================================================${NC}\n"

# Test credentials (modify these if needed)
EMAIL="customer1@test.com"
PASSWORD="password"

PASSED=0
FAILED=0

# Helper function
test_step() {
    local step="$1"
    local description="$2"
    echo -e "${BLUE}[$step] $description${NC}"
}

success() {
    echo -e "${GREEN}✅ $1${NC}\n"
    PASSED=$((PASSED + 1))
}

fail() {
    echo -e "${RED}❌ $1${NC}\n"
    FAILED=$((FAILED + 1))
}

# ==================== STEP 1: LOGIN ====================
test_step "1/10" "Testing Login..."

LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/api/login" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d "{\"login\":\"$EMAIL\",\"password\":\"$PASSWORD\"}")

echo "$LOGIN_RESPONSE" | jq '.'

SUCCESS=$(echo "$LOGIN_RESPONSE" | jq -r '._metadata.success // false')
if [ "$SUCCESS" = "true" ]; then
    TOKEN=$(echo "$LOGIN_RESPONSE" | jq -r '.data.access_token')
    success "Login successful! Token obtained."
else
    echo -e "${YELLOW}⚠️  Login failed. Attempting to register user...${NC}\n"

    # Try to register
    REGISTER_RESPONSE=$(curl -s -X POST "$BASE_URL/api/register/customer" \
        -H "Content-Type: application/json" \
        -H "Accept: application/json" \
        -d "{
            \"name\":\"Test Customer\",
            \"email\":\"$EMAIL\",
            \"phone\":\"+237677889900\",
            \"password\":\"$PASSWORD\",
            \"password_confirmation\":\"$PASSWORD\"
        }")

    echo "$REGISTER_RESPONSE" | jq '.'

    # Retry login
    LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/api/login" \
        -H "Content-Type: application/json" \
        -H "Accept: application/json" \
        -d "{\"login\":\"$EMAIL\",\"password\":\"$PASSWORD\"}")

    TOKEN=$(echo "$LOGIN_RESPONSE" | jq -r '.data.access_token')
    if [ "$TOKEN" = "null" ] || [ -z "$TOKEN" ]; then
        fail "Cannot authenticate. Stopping tests."
        exit 1
    fi
    success "Login successful after registration!"
fi

echo -e "${BLUE}Token (first 50 chars): ${TOKEN:0:50}...${NC}\n"

# ==================== STEP 2: GET USER PROFILE ====================
test_step "2/10" "Getting user profile..."

USER_RESPONSE=$(curl -s -X GET "$BASE_URL/api/user" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Accept: application/json")

echo "$USER_RESPONSE" | jq '.'

if echo "$USER_RESPONSE" | jq -e '._metadata.success' > /dev/null 2>&1; then
    success "User profile retrieved"
else
    fail "Failed to get user profile"
fi

# ==================== STEP 3: CHECK AUTH ====================
test_step "3/10" "Checking authentication status..."

AUTH_RESPONSE=$(curl -s -X GET "$BASE_URL/api/auth/check" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Accept: application/json")

echo "$AUTH_RESPONSE" | jq '.'

if echo "$AUTH_RESPONSE" | jq -e '._metadata.success' > /dev/null 2>&1; then
    success "Authentication verified"
else
    fail "Authentication check failed"
fi

# ==================== STEP 4: GET COUNTRIES ====================
test_step "4/10" "Getting countries list..."

COUNTRIES_RESPONSE=$(curl -s -X GET "$BASE_URL/api/geography/countries" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Accept: application/json")

echo "$COUNTRIES_RESPONSE" | jq '._metadata, (.data[0] // "No data")'

if echo "$COUNTRIES_RESPONSE" | jq -e '.data' > /dev/null 2>&1; then
    success "Countries retrieved"
else
    fail "Failed to get countries"
fi

# ==================== STEP 5: GET DELIVERY TYPES ====================
test_step "5/10" "Getting delivery types..."

DELIVERY_RESPONSE=$(curl -s -X GET "$BASE_URL/api/delivery-types" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Accept: application/json")

echo "$DELIVERY_RESPONSE" | jq '.'

if echo "$DELIVERY_RESPONSE" | jq -e '.data // ._metadata' > /dev/null 2>&1; then
    success "Delivery types retrieved"
else
    fail "Failed to get delivery types"
fi

# ==================== STEP 6: GET PAYMENT METHODS ====================
test_step "6/10" "Getting payment methods..."

PAYMENT_RESPONSE=$(curl -s -X GET "$BASE_URL/api/payment-methods" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Accept: application/json")

echo "$PAYMENT_RESPONSE" | jq '.'

if echo "$PAYMENT_RESPONSE" | jq -e '.data // ._metadata' > /dev/null 2>&1; then
    success "Payment methods retrieved"
else
    fail "Failed to get payment methods"
fi

# ==================== STEP 7: GET DISTRIBUTION CENTERS ====================
test_step "7/10" "Getting distribution centers..."

CENTERS_RESPONSE=$(curl -s -X GET "$BASE_URL/api/distribution-centers" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Accept: application/json")

echo "$CENTERS_RESPONSE" | jq '._metadata, (.data[0] // "No data")'

if echo "$CENTERS_RESPONSE" | jq -e '.data' > /dev/null 2>&1; then
    success "Distribution centers retrieved"
else
    fail "Failed to get distribution centers"
fi

# ==================== STEP 8: GET MY ORDERS ====================
test_step "8/10" "Getting my orders..."

ORDERS_RESPONSE=$(curl -s -X GET "$BASE_URL/api/my/orders" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Accept: application/json")

echo "$ORDERS_RESPONSE" | jq '._metadata'

if echo "$ORDERS_RESPONSE" | jq -e '._metadata' > /dev/null 2>&1; then
    success "Orders retrieved"
else
    fail "Failed to get orders"
fi

# ==================== STEP 9: CREATE ORDER ====================
test_step "9/12" "Testing order creation..."

ORDER_RESPONSE=$(curl -s -X POST "$BASE_URL/api/orders" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{
        "distribution_center_id": 1,
        "delivery_address_id": 1,
        "items": [
            {
                "product_category_id": 1,
                "quantity": 1,
                "option": "bottle_with_content",
                "unit_price": 6500
            }
        ],
        "delivery_type": "normal",
        "delivery_fee": 500,
        "total_amount": 7000
    }')

echo "$ORDER_RESPONSE" | jq '._metadata, (.data.order | {id, order_number, status, total_amount})'

if echo "$ORDER_RESPONSE" | jq -e '._metadata.success' > /dev/null 2>&1; then
    ORDER_ID=$(echo "$ORDER_RESPONSE" | jq -r '.data.order.id')
    success "Order created successfully (ID: $ORDER_ID)"
else
    fail "Failed to create order"
fi

# ==================== STEP 10: GET ORDER DETAILS ====================
test_step "10/12" "Getting order details..."

if [ -n "$ORDER_ID" ] && [ "$ORDER_ID" != "null" ]; then
    ORDER_DETAIL=$(curl -s -X GET "$BASE_URL/api/my/orders/$ORDER_ID" \
        -H "Authorization: Bearer $TOKEN" \
        -H "Accept: application/json")

    echo "$ORDER_DETAIL" | jq '._metadata, (.data | {order_number, status, total_amount})'

    if echo "$ORDER_DETAIL" | jq -e '._metadata.success' > /dev/null 2>&1; then
        success "Order details retrieved"
    else
        fail "Failed to get order details"
    fi
else
    fail "Skipped - no order ID available"
fi

# ==================== STEP 11: TEST APP VERSION ====================
test_step "11/12" "Testing app version endpoint..."

VERSION_RESPONSE=$(curl -s -X GET "$BASE_URL/api/app/version?app_type=customer" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Accept: application/json")

echo "$VERSION_RESPONSE" | jq '.'

if echo "$VERSION_RESPONSE" | jq -e '._metadata // .data' > /dev/null 2>&1; then
    success "App version endpoint works"
else
    fail "App version endpoint failed"
fi

# ==================== STEP 12: LOGOUT ====================
test_step "12/12" "Logging out..."

LOGOUT_RESPONSE=$(curl -s -X POST "$BASE_URL/api/logout" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Accept: application/json")

echo "$LOGOUT_RESPONSE" | jq '.'

if echo "$LOGOUT_RESPONSE" | jq -e '._metadata.success' > /dev/null 2>&1; then
    success "Logout successful"
else
    fail "Logout failed"
fi

# ==================== FINAL SUMMARY ====================
echo -e "\n${BLUE}================================================${NC}"
echo -e "${BLUE}  TEST SUMMARY${NC}"
echo -e "${BLUE}================================================${NC}"
echo -e "${GREEN}✅ Passed: $PASSED${NC}"
echo -e "${RED}❌ Failed: $FAILED${NC}"
echo -e "${BLUE}Total Tests: $((PASSED + FAILED))${NC}"

if [ $FAILED -eq 0 ]; then
    echo -e "\n${GREEN}🎉 ALL TESTS PASSED! Production API is working correctly.${NC}"
    exit 0
else
    echo -e "\n${YELLOW}⚠️  Some tests failed. Please review the errors above.${NC}"
    exit 1
fi
