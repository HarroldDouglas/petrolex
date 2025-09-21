#!/bin/bash

# =============================================================================
# E2E Test: Total Amount Validation
# Tests that orders with incorrect total amounts are rejected
# =============================================================================

set -e

# Load configuration
source "$(dirname "$0")/config.sh"

print_header "E2E Test: Total Amount Validation"

# Get authentication token
print_info "Authenticating user..."
TOKEN=$(get_auth_token)
print_success "Authentication successful"

# Test Case 1: Incorrect total amount (too low)
print_info "Test Case 1: Incorrect total amount (too low)"

CORRECT_TOTAL=$(calculate_total $PRODUCT_CATEGORY_1_FULL_PRICE 2 $PRODUCT_CATEGORY_2_FULL_PRICE 1 $DELIVERY_FEE_NORMAL)
WRONG_TOTAL=$((CORRECT_TOTAL - 1000))  # 1000 FCFA too low

RESPONSE=$(curl -s -X POST "$BASE_URL/api/orders" \
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
                \"quantity\": 2,
                \"unit_price\": $PRODUCT_CATEGORY_1_FULL_PRICE,
                \"option\": \"$BOTTLE_OPTION_FULL\"
            },
            {
                \"product_category_id\": $PRODUCT_CATEGORY_2_ID,
                \"quantity\": 1,
                \"unit_price\": $PRODUCT_CATEGORY_2_FULL_PRICE,
                \"option\": \"$BOTTLE_OPTION_FULL\"
            }
        ],
        \"delivery_fee\": $DELIVERY_FEE_NORMAL,
        \"total_amount\": $WRONG_TOTAL,
        \"comments\": \"Test total validation - amount too low\"
    }")

# Should fail with total mismatch error
if ! validate_response "$RESPONSE" "false"; then
    print_error "Response validation failed"
    exit 1
fi

# Check for total mismatch error
TOTAL_ERROR=$(echo "$RESPONSE" | jq -r '.errors.total_amount[0] // empty')
if [[ "$TOTAL_ERROR" == *"total_amount_mismatch"* ]] || [[ "$TOTAL_ERROR" == *"Montant total incorrect"* ]]; then
    print_success "Total validation working correctly - detected low amount"
    print_info "Expected: $CORRECT_TOTAL, Provided: $WRONG_TOTAL"
else
    print_error "Expected total mismatch error not found"
    echo "$RESPONSE" | jq '.'
    exit 1
fi

# Test Case 2: Incorrect total amount (too high)
print_info "Test Case 2: Incorrect total amount (too high)"

WRONG_TOTAL_HIGH=$((CORRECT_TOTAL + 2000))  # 2000 FCFA too high

RESPONSE2=$(curl -s -X POST "$BASE_URL/api/orders" \
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
                \"quantity\": 2,
                \"unit_price\": $PRODUCT_CATEGORY_1_FULL_PRICE,
                \"option\": \"$BOTTLE_OPTION_FULL\"
            },
            {
                \"product_category_id\": $PRODUCT_CATEGORY_2_ID,
                \"quantity\": 1,
                \"unit_price\": $PRODUCT_CATEGORY_2_FULL_PRICE,
                \"option\": \"$BOTTLE_OPTION_FULL\"
            }
        ],
        \"delivery_fee\": $DELIVERY_FEE_NORMAL,
        \"total_amount\": $WRONG_TOTAL_HIGH,
        \"comments\": \"Test total validation - amount too high\"
    }")

# Should fail with total mismatch error
if ! validate_response "$RESPONSE2" "false"; then
    print_error "Response validation failed"
    exit 1
fi

TOTAL_ERROR2=$(echo "$RESPONSE2" | jq -r '.errors.total_amount[0] // empty')
if [[ "$TOTAL_ERROR2" == *"total_amount_mismatch"* ]] || [[ "$TOTAL_ERROR2" == *"Montant total incorrect"* ]]; then
    print_success "Total validation working correctly - detected high amount"
    print_info "Expected: $CORRECT_TOTAL, Provided: $WRONG_TOTAL_HIGH"
else
    print_error "Expected total mismatch error not found"
    echo "$RESPONSE2" | jq '.'
    exit 1
fi

# Test Case 3: Wrong delivery fee affecting total
print_info "Test Case 3: Wrong delivery fee affecting total"

WRONG_DELIVERY_FEE=300  # Should be 500 for normal delivery
WRONG_TOTAL_WITH_DELIVERY=$(calculate_total $PRODUCT_CATEGORY_1_FULL_PRICE 1 $PRODUCT_CATEGORY_2_FULL_PRICE 1 $WRONG_DELIVERY_FEE)

RESPONSE3=$(curl -s -X POST "$BASE_URL/api/orders" \
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
            },
            {
                \"product_category_id\": $PRODUCT_CATEGORY_2_ID,
                \"quantity\": 1,
                \"unit_price\": $PRODUCT_CATEGORY_2_FULL_PRICE,
                \"option\": \"$BOTTLE_OPTION_FULL\"
            }
        ],
        \"delivery_fee\": $WRONG_DELIVERY_FEE,
        \"total_amount\": $WRONG_TOTAL_WITH_DELIVERY,
        \"comments\": \"Test total validation - wrong delivery fee\"
    }")

# Should fail with delivery fee mismatch error
if ! validate_response "$RESPONSE3" "false"; then
    print_error "Response validation failed"
    exit 1
fi

DELIVERY_ERROR=$(echo "$RESPONSE3" | jq -r '.errors.delivery_fee[0] // empty')
if [[ "$DELIVERY_ERROR" == *"delivery_fee_mismatch"* ]] || [[ "$DELIVERY_ERROR" == *"Frais de livraison incorrect"* ]]; then
    print_success "Delivery fee validation working correctly"
    print_info "Expected: $DELIVERY_FEE_NORMAL, Provided: $WRONG_DELIVERY_FEE"
else
    print_error "Expected delivery fee mismatch error not found"
    echo "$RESPONSE3" | jq '.'
    exit 1
fi

# Test Case 4: Correct total should succeed
print_info "Test Case 4: Correct total should succeed"

CORRECT_TOTAL_FINAL=$(calculate_total $PRODUCT_CATEGORY_1_FULL_PRICE 1 $PRODUCT_CATEGORY_2_FULL_PRICE 2 $DELIVERY_FEE_FAST)

RESPONSE4=$(curl -s -X POST "$BASE_URL/api/orders" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d "{
        \"delivery_address_id\": $DELIVERY_ADDRESS_ID,
        \"distribution_center_id\": $DISTRIBUTION_CENTER_ID,
        \"delivery_type\": \"$DELIVERY_TYPE_FAST\",
        \"items\": [
            {
                \"product_category_id\": $PRODUCT_CATEGORY_1_ID,
                \"quantity\": 1,
                \"unit_price\": $PRODUCT_CATEGORY_1_FULL_PRICE,
                \"option\": \"$BOTTLE_OPTION_FULL\"
            },
            {
                \"product_category_id\": $PRODUCT_CATEGORY_2_ID,
                \"quantity\": 2,
                \"unit_price\": $PRODUCT_CATEGORY_2_FULL_PRICE,
                \"option\": \"$BOTTLE_OPTION_FULL\"
            }
        ],
        \"delivery_fee\": $DELIVERY_FEE_FAST,
        \"total_amount\": $CORRECT_TOTAL_FINAL,
        \"comments\": \"Test total validation - correct amounts\"
    }")

# Should succeed
if ! validate_response "$RESPONSE4" "true"; then
    print_error "Order creation with correct total failed"
    echo "$RESPONSE4" | jq '.'
    exit 1
fi

ORDER_ID=$(echo "$RESPONSE4" | jq -r '.data.order.id')
ACTUAL_TOTAL=$(echo "$RESPONSE4" | jq -r '.data.order.total_amount')
ACTUAL_SUBTOTAL=$(echo "$RESPONSE4" | jq -r '.data.order.subtotal')

print_success "Order created successfully with correct total (ID: $ORDER_ID)"
print_info "Total: $ACTUAL_TOTAL, Subtotal: $ACTUAL_SUBTOTAL"

print_header "✅ Total Amount Validation Tests Completed Successfully"

echo -e "\n${GREEN}Summary:${NC}"
echo "  ✅ Low total amount detection"
echo "  ✅ High total amount detection"
echo "  ✅ Wrong delivery fee detection"
echo "  ✅ Correct total acceptance"
echo "  ✅ Proper error message localization"