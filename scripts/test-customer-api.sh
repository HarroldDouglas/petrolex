#!/bin/bash

# ============================================================================
# Test Customer API - Complete Flow Test Script
# ============================================================================
# This script tests all critical customer API endpoints to ensure they work
# properly for Google Play Store validation
#
# Usage:
#   ./scripts/test-customer-api.sh [API_URL]
#
# Example:
#   ./scripts/test-customer-api.sh https://staging-api.petrolex.com
#   ./scripts/test-customer-api.sh http://localhost:8000
# ============================================================================

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Test customer credentials
TEST_EMAIL="test.customer@petrolex.com"
TEST_PASSWORD="TestPetrolex2026!"
TEST_PHONE="+237600000001"

# API URL (default to localhost)
API_URL="${1:-http://localhost:8000}"
API_BASE="${API_URL}/api"

# Global variables
AUTH_TOKEN=""
TESTS_PASSED=0
TESTS_FAILED=0
TEMP_DIR=$(mktemp -d)

# Cleanup on exit
cleanup() {
    rm -rf "$TEMP_DIR"
}
trap cleanup EXIT

# Helper functions
print_header() {
    echo ""
    echo -e "${CYAN}╔══════════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${CYAN}║  $1${NC}"
    echo -e "${CYAN}╚══════════════════════════════════════════════════════════════════╝${NC}"
}

print_test() {
    echo -e "${YELLOW}⏳ $1${NC}"
}

print_success() {
    ((TESTS_PASSED++))
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    ((TESTS_FAILED++))
    echo -e "${RED}✗ $1${NC}"
}

print_info() {
    echo -e "${CYAN}ℹ  $1${NC}"
}

# API call helper
api_call() {
    local method="$1"
    local endpoint="$2"
    local data="$3"
    local auth="$4"

    local curl_cmd="curl -s -w '\n%{http_code}' -X $method"

    if [ "$auth" = "true" ] && [ -n "$AUTH_TOKEN" ]; then
        curl_cmd="$curl_cmd -H 'Authorization: Bearer $AUTH_TOKEN'"
    fi

    curl_cmd="$curl_cmd -H 'Content-Type: application/json' -H 'Accept: application/json'"

    if [ -n "$data" ]; then
        curl_cmd="$curl_cmd -d '$data'"
    fi

    curl_cmd="$curl_cmd '${API_BASE}${endpoint}'"

    eval $curl_cmd
}

# Test functions

test_health_check() {
    print_test "Testing health check endpoint..."

    response=$(api_call GET /health)
    status_code=$(echo "$response" | tail -n1)
    body=$(echo "$response" | sed '$d')

    if [ "$status_code" = "200" ]; then
        print_success "Health check passed (Status: $status_code)"
    else
        print_error "Health check failed (Status: $status_code)"
        echo "$body" | jq '.' 2>/dev/null || echo "$body"
    fi
}

test_login_with_email() {
    print_test "Testing login with email..."

    local data='{"login":"'"$TEST_EMAIL"'","password":"'"$TEST_PASSWORD"'"}'
    response=$(api_call POST /login/customer "$data")
    status_code=$(echo "$response" | tail -n1)
    body=$(echo "$response" | sed '$d')

    if [ "$status_code" = "200" ]; then
        AUTH_TOKEN=$(echo "$body" | jq -r '.data.access_token' 2>/dev/null)
        if [ -n "$AUTH_TOKEN" ] && [ "$AUTH_TOKEN" != "null" ]; then
            print_success "Login with email successful (Token received)"
            print_info "Token: ${AUTH_TOKEN:0:20}..."
        else
            print_error "Login successful but no token received"
            echo "$body" | jq '.' 2>/dev/null || echo "$body"
        fi
    else
        print_error "Login with email failed (Status: $status_code)"
        echo "$body" | jq '.' 2>/dev/null || echo "$body"
    fi
}

test_login_with_phone() {
    print_test "Testing login with phone..."

    local data='{"login":"'"$TEST_PHONE"'","password":"'"$TEST_PASSWORD"'","country_code":"CM"}'
    response=$(api_call POST /login/customer "$data")
    status_code=$(echo "$response" | tail -n1)
    body=$(echo "$response" | sed '$d')

    if [ "$status_code" = "200" ]; then
        print_success "Login with phone successful"
    else
        print_error "Login with phone failed (Status: $status_code)"
        echo "$body" | jq '.' 2>/dev/null || echo "$body"
    fi
}

test_auth_check() {
    print_test "Testing authentication check..."

    response=$(api_call GET /auth/check "" true)
    status_code=$(echo "$response" | tail -n1)
    body=$(echo "$response" | sed '$d')

    if [ "$status_code" = "200" ]; then
        print_success "Auth check passed"
    else
        print_error "Auth check failed (Status: $status_code)"
        echo "$body" | jq '.' 2>/dev/null || echo "$body"
    fi
}

test_get_profile() {
    print_test "Testing get user profile..."

    response=$(api_call GET /user "" true)
    status_code=$(echo "$response" | tail -n1)
    body=$(echo "$response" | sed '$d')

    if [ "$status_code" = "200" ]; then
        user_email=$(echo "$body" | jq -r '.data.user.email' 2>/dev/null)
        if [ -z "$user_email" ] || [ "$user_email" = "null" ]; then
            # Try alternative path
            user_email=$(echo "$body" | jq -r '.user.email' 2>/dev/null)
        fi

        if [ "$user_email" = "$TEST_EMAIL" ]; then
            print_success "Profile retrieved successfully (Email: $user_email)"
        elif [ -n "$user_email" ] && [ "$user_email" != "null" ]; then
            print_success "Profile retrieved successfully (Email: $user_email)"
        else
            print_success "Profile retrieved successfully"
        fi
    else
        print_error "Get profile failed (Status: $status_code)"
        echo "$body" | jq '.' 2>/dev/null || echo "$body"
    fi
}

test_get_countries() {
    print_test "Testing get countries..."

    response=$(api_call GET /geography/countries "" true)
    status_code=$(echo "$response" | tail -n1)
    body=$(echo "$response" | sed '$d')

    if [ "$status_code" = "200" ]; then
        country_count=$(echo "$body" | jq '.data | length' 2>/dev/null)
        print_success "Countries retrieved successfully (Count: $country_count)"
    else
        print_error "Get countries failed (Status: $status_code)"
        echo "$body" | jq '.' 2>/dev/null || echo "$body"
    fi
}

test_get_distribution_centers() {
    print_test "Testing get distribution centers..."

    response=$(api_call GET /distribution-centers "" true)
    status_code=$(echo "$response" | tail -n1)
    body=$(echo "$response" | sed '$d')

    if [ "$status_code" = "200" ]; then
        center_count=$(echo "$body" | jq '.data | length' 2>/dev/null)
        print_success "Distribution centers retrieved (Count: $center_count)"
    else
        print_error "Get distribution centers failed (Status: $status_code)"
        echo "$body" | jq '.' 2>/dev/null || echo "$body"
    fi
}

test_get_closest_distribution_center() {
    print_test "Testing get closest distribution center..."

    response=$(api_call GET "/distribution-centers/closest?latitude=3.8617882&longitude=11.5835694" "" true)
    status_code=$(echo "$response" | tail -n1)
    body=$(echo "$response" | sed '$d')

    if [ "$status_code" = "200" ]; then
        print_success "Closest distribution center retrieved"
    else
        print_error "Get closest distribution center failed (Status: $status_code)"
        echo "$body" | jq '.' 2>/dev/null || echo "$body"
    fi
}

test_get_my_orders() {
    print_test "Testing get my orders..."

    response=$(api_call GET /my/orders "" true)
    status_code=$(echo "$response" | tail -n1)
    body=$(echo "$response" | sed '$d')

    if [ "$status_code" = "200" ]; then
        order_count=$(echo "$body" | jq '.data | length' 2>/dev/null)
        print_success "My orders retrieved (Count: $order_count)"
    else
        print_error "Get my orders failed (Status: $status_code)"
        echo "$body" | jq '.' 2>/dev/null || echo "$body"
    fi
}

test_get_app_version() {
    print_test "Testing get app version..."

    response=$(api_call GET "/app/version?app_type=customer_app")
    status_code=$(echo "$response" | tail -n1)
    body=$(echo "$response" | sed '$d')

    if [ "$status_code" = "200" ] || [ "$status_code" = "404" ]; then
        if [ "$status_code" = "404" ]; then
            print_success "App version endpoint works (No version configured yet)"
        else
            print_success "App version retrieved"
        fi
    else
        print_error "Get app version failed (Status: $status_code)"
        echo "$body" | jq '.' 2>/dev/null || echo "$body"
    fi
}

test_get_terms_and_conditions() {
    print_test "Testing get terms and conditions..."

    response=$(api_call GET /app/terms-and-conditions)
    status_code=$(echo "$response" | tail -n1)

    if [ "$status_code" = "200" ]; then
        print_success "Terms and conditions retrieved"
    else
        print_error "Get terms and conditions failed (Status: $status_code)"
    fi
}

test_get_privacy_policy() {
    print_test "Testing get privacy policy..."

    response=$(api_call GET /app/privacy-policy)
    status_code=$(echo "$response" | tail -n1)

    if [ "$status_code" = "200" ]; then
        print_success "Privacy policy retrieved"
    else
        print_error "Get privacy policy failed (Status: $status_code)"
    fi
}

test_get_support_contact() {
    print_test "Testing get support contact..."

    response=$(api_call GET /app/support/contact)
    status_code=$(echo "$response" | tail -n1)
    body=$(echo "$response" | sed '$d')

    if [ "$status_code" = "200" ]; then
        print_success "Support contact retrieved"
    else
        print_error "Get support contact failed (Status: $status_code)"
        echo "$body" | jq '.' 2>/dev/null || echo "$body"
    fi
}

test_logout() {
    print_test "Testing logout..."

    response=$(api_call POST /logout "" true)
    status_code=$(echo "$response" | tail -n1)
    body=$(echo "$response" | sed '$d')

    if [ "$status_code" = "200" ]; then
        print_success "Logout successful"
        AUTH_TOKEN=""
    else
        print_error "Logout failed (Status: $status_code)"
        echo "$body" | jq '.' 2>/dev/null || echo "$body"
    fi
}

# Main execution
print_header "Testing API for test customer: $TEST_EMAIL"
print_info "API Base URL: $API_BASE"
print_info ""

# Run tests in order
test_health_check
test_login_with_email
test_login_with_phone
test_auth_check
test_get_profile
test_get_countries
test_get_distribution_centers
test_get_closest_distribution_center
test_get_my_orders
test_get_app_version
test_get_terms_and_conditions
test_get_privacy_policy
test_get_support_contact
test_logout

# Print summary
echo ""
print_header "Test Summary"
echo -e "${GREEN}Passed: $TESTS_PASSED${NC}"
echo -e "${RED}Failed: $TESTS_FAILED${NC}"
echo ""

if [ $TESTS_FAILED -eq 0 ]; then
    echo -e "${GREEN}╔══════════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${GREEN}║  ✓ All tests passed! API is ready for mobile app testing        ║${NC}"
    echo -e "${GREEN}╚══════════════════════════════════════════════════════════════════╝${NC}"
    exit 0
else
    echo -e "${RED}╔══════════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${RED}║  ✗ Some tests failed! Please review the errors above            ║${NC}"
    echo -e "${RED}╚══════════════════════════════════════════════════════════════════╝${NC}"
    exit 1
fi
