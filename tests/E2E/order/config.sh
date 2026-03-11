#!/bin/bash

# =============================================================================
# Configuration for Order E2E Tests
# =============================================================================

# Base URL
export BASE_URL="https://isogaz.net"

# Test User Credentials
export TEST_EMAIL="test.customer@petrolex.com"
export TEST_PASSWORD="TestPetrolex2026!"

# Test Data IDs
export CUSTOMER_ID=1
export DELIVERY_ADDRESS_ID=5  # Test customer default delivery address (prod)
export DISTRIBUTION_CENTER_ID=1

# Product Categories with Expected Prices
# Category 1: Bouteille de 9Kg
export PRODUCT_CATEGORY_1_ID=1
export PRODUCT_CATEGORY_1_FULL_PRICE=21780    # consigne + recharge
export PRODUCT_CATEGORY_1_CONTENT_PRICE=4680  # recharge seule

# Category 2: Brûleur à gaz (accessory)
export PRODUCT_CATEGORY_2_ID=2
export PRODUCT_CATEGORY_2_FULL_PRICE=2000     # accessory price
export PRODUCT_CATEGORY_2_CONTENT_PRICE=2000  # same for accessories

# Delivery Types and Fees
export DELIVERY_TYPE_NORMAL="normal"
export DELIVERY_TYPE_FAST="fast"
export DELIVERY_FEE_NORMAL=500
export DELIVERY_FEE_FAST=1000

# Bottle Order Types
export BOTTLE_OPTION_FULL="bottle_with_content"
export BOTTLE_OPTION_CONTENT="content"

# Payment Methods
export PAYMENT_METHOD_ORANGE="orange_money"
export PAYMENT_METHOD_MTN="mtn_money"
export PAYMENT_METHOD_CARD="credit_card"

# Test Payment Details
export TEST_PHONE="655332183"
export TEST_NAME="Test User"
export TEST_CARD_NUMBER="4000000000000002"  # Valid Visa test card
export TEST_CVV="123"
export TEST_EXPIRY="12/28"
export TEST_CARDHOLDER="John Doe"

# Colors for output
export RED='\033[0;31m'
export GREEN='\033[0;32m'
export YELLOW='\033[1;33m'
export BLUE='\033[0;34m'
export NC='\033[0m' # No Color

# =============================================================================
# Helper Functions
# =============================================================================

# Print colored output
print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

print_header() {
    echo -e "\n${BLUE}================================================${NC}"
    echo -e "${BLUE} $1${NC}"
    echo -e "${BLUE}================================================${NC}\n"
}

# Get authentication token
get_auth_token() {
    local response=$(curl -s -X POST "$BASE_URL/api/login/customer" \
        -H "Content-Type: application/json; charset=utf-8" \
        -H "Accept: application/json" \
        -d "{
            \"login\": \"$TEST_EMAIL\",
            \"password\": \"$TEST_PASSWORD\"
        }")

    local success=$(echo "$response" | jq -r '._metadata.success // false')

    if [ "$success" = "true" ]; then
        echo "$response" | jq -r '.data.access_token'
    else
        print_error "Failed to authenticate"
        echo "$response" | jq '.'
        exit 1
    fi
}

# Validate JSON response
validate_response() {
    local response="$1"
    local expected_success="$2"

    if ! echo "$response" | jq empty 2>/dev/null; then
        print_error "Invalid JSON response"
        echo "$response"
        return 1
    fi

    # Check for success field (new API) or data/errors field (Laravel validation) or exception field
    local success=$(echo "$response" | jq -r '.success // empty')
    local has_errors=$(echo "$response" | jq -r 'has("errors")')
    local has_exception=$(echo "$response" | jq -r 'has("exception")')
    local has_data=$(echo "$response" | jq -r 'has("data")')

    if [ -n "$success" ]; then
        # New API format with explicit success field
        if [ "$success" != "$expected_success" ]; then
            return 1
        fi
    elif [ "$has_exception" = "true" ]; then
        # Laravel exception format - exceptions indicate failure
        if [ "$expected_success" = "true" ]; then
            return 1  # Exception when expecting success = failure
        else
            return 0  # Exception when expecting failure = success
        fi
    else
        # Laravel validation format - errors field indicates failure
        if [ "$expected_success" = "true" ]; then
            # Expecting success - should have data and no errors
            if [ "$has_errors" = "true" ]; then
                return 1
            fi
        else
            # Expecting failure - should have errors
            if [ "$has_errors" = "false" ]; then
                return 1
            fi
        fi
    fi

    return 0
}

# Wait for async job completion
wait_for_payment_completion() {
    local order_id="$1"
    local token="$2"
    local max_attempts=10
    local attempt=1

    print_info "Waiting for payment completion (max ${max_attempts} attempts)..."

    while [ $attempt -le $max_attempts ]; do
        local response=$(curl -s -X GET "$BASE_URL/api/orders/$order_id" \
            -H "Authorization: Bearer $token" \
            -H "Accept: application/json")

        local order_status=$(echo "$response" | jq -r '.data.status // "unknown"')

        print_info "Attempt $attempt: Order status = $order_status"

        if [ "$order_status" = "paid" ]; then
            print_success "Payment completed successfully!"
            return 0
        fi

        sleep 10
        attempt=$((attempt + 1))
    done

    print_error "Payment did not complete within expected time"
    return 1
}

# Calculate expected total
calculate_total() {
    local item1_price="$1"
    local item1_qty="$2"
    local item2_price="$3"
    local item2_qty="$4"
    local delivery_fee="$5"

    echo $(( (item1_price * item1_qty) + (item2_price * item2_qty) + delivery_fee ))
}
