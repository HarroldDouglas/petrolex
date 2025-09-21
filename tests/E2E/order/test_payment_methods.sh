#!/bin/bash

# =============================================================================
# E2E Test: Payment Methods Validation
# Tests all payment methods with their specific validation rules
# =============================================================================

set -e

# Load configuration
source "$(dirname "$0")/config.sh"

print_header "E2E Test: Payment Methods Validation"

# Get authentication token
print_info "Authenticating user..."
TOKEN=$(get_auth_token)
print_success "Authentication successful"

# Helper function to create a test order
create_test_order() {
    local comment="$1"
    local total=$(calculate_total $PRODUCT_CATEGORY_1_FULL_PRICE 1 0 0 $DELIVERY_FEE_NORMAL)
    
    local response=$(curl -s -X POST "$BASE_URL/api/orders" \
        -H "Authorization: Bearer $TOKEN" \
        -H "Content-Type: application/json" \
        -H "Accept: application/json" \
        -d "{
            \"delivery_address_id\": $DELIVERY_ADDRESS_ID,
            \"distribution_center_id\": $DISTRIBUTION_CENTER_ID,
            \"delivery_type\": \"$DELIVERY_TYPE_NORMAL\",
            \"items\": [
                {
                    \"product_category_id\": $PRODUCT_CATEGORY_1_ID,
                    \"quantity\": 1,
                    \"unit_price\": $PRODUCT_CATEGORY_1_FULL_PRICE,
                    \"option\": \"$BOTTLE_OPTION_FULL\"
                }
            ],
            \"delivery_fee\": $DELIVERY_FEE_NORMAL,
            \"total_amount\": $total,
            \"comments\": \"$comment\"
        }")
    
    if validate_response "$response" "true"; then
        echo "$response" | jq -r '.data.order.id'
    else
        print_error "Failed to create test order"
        echo "$response" | jq '.'
        exit 1
    fi
}

# Test Case 1: Orange Money with invalid phone
print_info "Test Case 1: Orange Money with invalid phone number"

ORDER_ID_1=$(create_test_order "Test Orange Money - invalid phone")

RESPONSE1=$(curl -s -X POST "$BASE_URL/api/orders/$ORDER_ID_1/payment" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d "{
        \"payment_method\": \"$PAYMENT_METHOD_ORANGE\",
        \"payment_details\": {
            \"phone\": \"123\",
            \"name\": \"$TEST_NAME\"
        }
    }")

if ! validate_response "$RESPONSE1" "false"; then
    print_error "Should have failed with invalid phone"
    exit 1
fi

PHONE_ERROR=$(echo "$RESPONSE1" | jq -r '.errors["payment_details.phone"][0] // empty')
if [[ "$PHONE_ERROR" == *"regex"* ]] || [[ "$PHONE_ERROR" == *"format"* ]]; then
    print_success "Orange Money phone validation working correctly"
else
    print_error "Expected phone format error not found"
    echo "$RESPONSE1" | jq '.'
    exit 1
fi

# Test Case 2: MTN Money with valid data
print_info "Test Case 2: MTN Money with valid data"

ORDER_ID_2=$(create_test_order "Test MTN Money - valid")

RESPONSE2=$(curl -s -X POST "$BASE_URL/api/orders/$ORDER_ID_2/payment" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d "{
        \"payment_method\": \"$PAYMENT_METHOD_MTN\",
        \"payment_details\": {
            \"phone\": \"$TEST_PHONE\",
            \"name\": \"$TEST_NAME\"
        }
    }")

if ! validate_response "$RESPONSE2" "true"; then
    print_error "MTN Money payment should have succeeded"
    echo "$RESPONSE2" | jq '.'
    exit 1
fi

print_success "MTN Money payment initiated successfully"

# Test Case 3: Credit Card with invalid Luhn number
print_info "Test Case 3: Credit Card with invalid Luhn number"

ORDER_ID_3=$(create_test_order "Test Credit Card - invalid Luhn")

RESPONSE3=$(curl -s -X POST "$BASE_URL/api/orders/$ORDER_ID_3/payment" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d "{
        \"payment_method\": \"$PAYMENT_METHOD_CARD\",
        \"payment_details\": {
            \"card_number\": \"1234567890123456\",
            \"cvv\": \"$TEST_CVV\",
            \"expiry_date\": \"$TEST_EXPIRY\",
            \"cardholder_name\": \"$TEST_CARDHOLDER\"
        }
    }")

if ! validate_response "$RESPONSE3" "false"; then
    print_error "Should have failed with invalid Luhn"
    exit 1
fi

LUHN_ERROR=$(echo "$RESPONSE3" | jq -r '.errors["payment_details.card_number"][0] // empty')
if [[ "$LUHN_ERROR" == *"invalid_card_number"* ]] || [[ "$LUHN_ERROR" == *"invalide"* ]]; then
    print_success "Credit Card Luhn validation working correctly"
else
    print_error "Expected Luhn validation error not found"
    echo "$RESPONSE3" | jq '.'
    exit 1
fi

# Test Case 4: Credit Card with invalid CVV
print_info "Test Case 4: Credit Card with invalid CVV format"

ORDER_ID_4=$(create_test_order "Test Credit Card - invalid CVV")

RESPONSE4=$(curl -s -X POST "$BASE_URL/api/orders/$ORDER_ID_4/payment" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d "{
        \"payment_method\": \"$PAYMENT_METHOD_CARD\",
        \"payment_details\": {
            \"card_number\": \"$TEST_CARD_NUMBER\",
            \"cvv\": \"12\",
            \"expiry_date\": \"$TEST_EXPIRY\",
            \"cardholder_name\": \"$TEST_CARDHOLDER\"
        }
    }")

if ! validate_response "$RESPONSE4" "false"; then
    print_error "Should have failed with invalid CVV"
    exit 1
fi

CVV_ERROR=$(echo "$RESPONSE4" | jq -r '.errors["payment_details.cvv"][0] // empty')
if [[ "$CVV_ERROR" == *"regex"* ]] || [[ "$CVV_ERROR" == *"format"* ]]; then
    print_success "Credit Card CVV validation working correctly"
else
    print_error "Expected CVV format error not found"
    echo "$RESPONSE4" | jq '.'
    exit 1
fi

# Test Case 5: Credit Card with expired date
print_info "Test Case 5: Credit Card with expired date"

ORDER_ID_5=$(create_test_order "Test Credit Card - expired date")

RESPONSE5=$(curl -s -X POST "$BASE_URL/api/orders/$ORDER_ID_5/payment" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d "{
        \"payment_method\": \"$PAYMENT_METHOD_CARD\",
        \"payment_details\": {
            \"card_number\": \"$TEST_CARD_NUMBER\",
            \"cvv\": \"$TEST_CVV\",
            \"expiry_date\": \"01/20\",
            \"cardholder_name\": \"$TEST_CARDHOLDER\"
        }
    }")

if ! validate_response "$RESPONSE5" "false"; then
    print_error "Should have failed with expired date"
    exit 1
fi

EXPIRY_ERROR=$(echo "$RESPONSE5" | jq -r '.errors["payment_details.expiry_date"][0] // empty')
if [[ "$EXPIRY_ERROR" == *"after"* ]] || [[ "$EXPIRY_ERROR" == *"futur"* ]]; then
    print_success "Credit Card expiry date validation working correctly"
else
    print_error "Expected expiry date error not found"
    echo "$RESPONSE5" | jq '.'
    exit 1
fi

# Test Case 6: Credit Card with valid data
print_info "Test Case 6: Credit Card with valid data"

ORDER_ID_6=$(create_test_order "Test Credit Card - valid")

RESPONSE6=$(curl -s -X POST "$BASE_URL/api/orders/$ORDER_ID_6/payment" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d "{
        \"payment_method\": \"$PAYMENT_METHOD_CARD\",
        \"payment_details\": {
            \"card_number\": \"$TEST_CARD_NUMBER\",
            \"cvv\": \"$TEST_CVV\",
            \"expiry_date\": \"$TEST_EXPIRY\",
            \"cardholder_name\": \"$TEST_CARDHOLDER\"
        }
    }")

if ! validate_response "$RESPONSE6" "true"; then
    print_error "Credit Card payment should have succeeded"
    echo "$RESPONSE6" | jq '.'
    exit 1
fi

print_success "Credit Card payment initiated successfully"

# Test Case 7: Invalid payment method
print_info "Test Case 7: Invalid payment method"

ORDER_ID_7=$(create_test_order "Test Invalid Payment Method")

RESPONSE7=$(curl -s -X POST "$BASE_URL/api/orders/$ORDER_ID_7/payment" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d "{
        \"payment_method\": \"bitcoin\",
        \"payment_details\": {
            \"wallet\": \"1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa\"
        }
    }")

if ! validate_response "$RESPONSE7" "false"; then
    print_error "Should have failed with invalid payment method"
    exit 1
fi

METHOD_ERROR=$(echo "$RESPONSE7" | jq -r '.errors.payment_method[0] // empty')
if [[ "$METHOD_ERROR" == *"in"* ]] || [[ "$METHOD_ERROR" == *"invalid"* ]]; then
    print_success "Invalid payment method correctly rejected"
else
    print_error "Expected payment method error not found"
    echo "$RESPONSE7" | jq '.'
    exit 1
fi

print_header "✅ Payment Methods Validation Tests Completed Successfully"

echo -e "\n${GREEN}Summary:${NC}"
echo "  ✅ Orange Money phone validation"
echo "  ✅ MTN Money successful payment"
echo "  ✅ Credit Card Luhn algorithm validation"
echo "  ✅ Credit Card CVV format validation"
echo "  ✅ Credit Card expiry date validation"
echo "  ✅ Credit Card successful payment"
echo "  ✅ Invalid payment method rejection"

echo -e "\n${GREEN}Test Orders Created:${NC}"
echo "  📋 Order $ORDER_ID_1: Orange Money (failed - invalid phone)"
echo "  📋 Order $ORDER_ID_2: MTN Money (successful)"
echo "  📋 Order $ORDER_ID_3: Credit Card (failed - invalid Luhn)"
echo "  📋 Order $ORDER_ID_4: Credit Card (failed - invalid CVV)"
echo "  📋 Order $ORDER_ID_5: Credit Card (failed - expired date)"
echo "  📋 Order $ORDER_ID_6: Credit Card (successful)"
echo "  📋 Order $ORDER_ID_7: Invalid method (failed)"