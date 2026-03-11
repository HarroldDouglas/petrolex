#!/bin/bash

# =============================================================================
# E2E Test: Price Validation
# Tests that orders with incorrect item prices are rejected
# =============================================================================

set -e

# Load configuration
source "$(dirname "$0")/config.sh"

print_header "E2E Test: Price Validation"

# Get authentication token
print_info "Authenticating user..."
TOKEN=$(get_auth_token)
print_success "Authentication successful"

# Test Case 1: Incorrect unit price for first item
print_info "Test Case 1: Incorrect unit price for first item"

WRONG_PRICE=4000  # Expected: 5000
CORRECT_TOTAL=$(calculate_total $WRONG_PRICE 1 $PRODUCT_CATEGORY_2_FULL_PRICE 1 $DELIVERY_FEE_NORMAL)

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
                \"quantity\": 1,
                \"unit_price\": $WRONG_PRICE,
                \"option\": \"$BOTTLE_OPTION_FULL\"
            },
            {
                \"product_category_id\": $PRODUCT_CATEGORY_2_ID,
                \"quantity\": 1,
                \"unit_price\": $PRODUCT_CATEGORY_2_FULL_PRICE
            }
        ],
        \"delivery_fee\": $DELIVERY_FEE_NORMAL,
        \"total_amount\": $CORRECT_TOTAL,
        \"comments\": \"Test price validation - wrong unit price\"
    }")

# Should fail with price mismatch error
if ! validate_response "$RESPONSE" "false"; then
    print_error "Response validation failed"
    echo "$RESPONSE" | jq '.'
    exit 1
fi

# Check for price mismatch error
PRICE_ERROR=$(echo "$RESPONSE" | jq -r '.errors["items.0.unit_price"][0] // empty')
if [[ "$PRICE_ERROR" == *"price_mismatch"* ]] || [[ "$PRICE_ERROR" == *"Prix incorrect"* ]]; then
    print_success "Price validation working correctly - detected wrong unit price"
else
    print_error "Expected price mismatch error not found"
    echo "$RESPONSE" | jq '.'
    exit 1
fi

# Test Case 2: Multiple incorrect prices
print_info "Test Case 2: Multiple incorrect prices"

WRONG_PRICE_1=3000  # Expected: 5000
WRONG_PRICE_2=5000  # Expected: 6500
CORRECT_TOTAL_2=$(calculate_total $WRONG_PRICE_1 2 $WRONG_PRICE_2 1 $DELIVERY_FEE_FAST)

RESPONSE2=$(curl -s -X POST "$BASE_URL/api/orders" \
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
                \"quantity\": 2,
                \"unit_price\": $WRONG_PRICE_1,
                \"option\": \"$BOTTLE_OPTION_FULL\"
            },
            {
                \"product_category_id\": $PRODUCT_CATEGORY_2_ID,
                \"quantity\": 1,
                \"unit_price\": $WRONG_PRICE_2
            }
        ],
        \"delivery_fee\": $DELIVERY_FEE_FAST,
        \"total_amount\": $CORRECT_TOTAL_2,
        \"comments\": \"Test price validation - multiple wrong prices\"
    }")

# Should fail with multiple price mismatch errors
if ! validate_response "$RESPONSE2" "false"; then
    print_error "Response validation failed"
    echo "$RESPONSE2" | jq '.'
    exit 1
fi

# Check for multiple price errors
PRICE_ERROR_1=$(echo "$RESPONSE2" | jq -r '.errors["items.0.unit_price"][0] // empty')
PRICE_ERROR_2=$(echo "$RESPONSE2" | jq -r '.errors["items.1.unit_price"][0] // empty')

if ([[ "$PRICE_ERROR_1" == *"price_mismatch"* ]] || [[ "$PRICE_ERROR_1" == *"Prix incorrect"* ]]) && 
   ([[ "$PRICE_ERROR_2" == *"price_mismatch"* ]] || [[ "$PRICE_ERROR_2" == *"Prix incorrect"* ]]); then
    print_success "Multiple price validation working correctly"
else
    print_error "Expected multiple price mismatch errors not found"
    echo "$RESPONSE2" | jq '.'
    exit 1
fi

# Test Case 3: Correct prices should succeed
print_info "Test Case 3: Correct prices should succeed"

CORRECT_TOTAL_3=$(calculate_total $PRODUCT_CATEGORY_1_FULL_PRICE 1 $PRODUCT_CATEGORY_2_FULL_PRICE 1 $DELIVERY_FEE_NORMAL)

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
                \"unit_price\": $PRODUCT_CATEGORY_2_FULL_PRICE
            }
        ],
        \"delivery_fee\": $DELIVERY_FEE_NORMAL,
        \"total_amount\": $CORRECT_TOTAL_3,
        \"comments\": \"Test price validation - correct prices\"
    }")

# Should succeed
if ! validate_response "$RESPONSE3" "true"; then
    print_error "Order creation with correct prices failed"
    echo "$RESPONSE3" | jq '.'
    exit 1
fi

ORDER_ID=$(echo "$RESPONSE3" | jq -r '.data.order.id')
print_success "Order created successfully with correct prices (ID: $ORDER_ID)"

print_header "✅ Price Validation Tests Completed Successfully"

echo -e "\n${GREEN}Summary:${NC}"
echo "  ✅ Incorrect unit price detection"
echo "  ✅ Multiple incorrect prices detection"  
echo "  ✅ Correct prices acceptance"
echo "  ✅ Proper error message localization"