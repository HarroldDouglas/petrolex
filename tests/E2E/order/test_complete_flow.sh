#!/bin/bash

# =============================================================================
# E2E Test: Complete Order Flow
# Tests the complete flow from order creation to payment completion
# =============================================================================

set -e

# Load configuration
source "$(dirname "$0")/config.sh"

print_header "E2E Test: Complete Order Flow"

# Get authentication token
print_info "Authenticating user..."
TOKEN=$(get_auth_token)
print_success "Authentication successful"

# Step 1: Create Order
print_info "Step 1: Creating order..."

EXPECTED_TOTAL=$(calculate_total $PRODUCT_CATEGORY_1_FULL_PRICE 2 $PRODUCT_CATEGORY_2_FULL_PRICE 1 $DELIVERY_FEE_FAST)

ORDER_RESPONSE=$(curl -s -X POST "$BASE_URL/api/orders" \
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
        \"delivery_fee\": $DELIVERY_FEE_FAST,
        \"total_amount\": $EXPECTED_TOTAL,
        \"comments\": \"E2E Test - Complete Flow\"
    }")

# Validate order creation
if ! validate_response "$ORDER_RESPONSE" "true"; then
    print_error "Order creation failed"
    echo "$ORDER_RESPONSE" | jq '.'
    exit 1
fi

ORDER_ID=$(echo "$ORDER_RESPONSE" | jq -r '.data.order.id')
ORDER_STATUS=$(echo "$ORDER_RESPONSE" | jq -r '.data.order.status')
ORDER_TOTAL=$(echo "$ORDER_RESPONSE" | jq -r '.data.order.total_amount')

print_success "Order created successfully"
print_info "Order ID: $ORDER_ID"
print_info "Status: $ORDER_STATUS"
print_info "Total: $ORDER_TOTAL FCFA"

# Verify order status is pending
if [ "$ORDER_STATUS" != "pending" ]; then
    print_error "Expected order status 'pending', got '$ORDER_STATUS'"
    exit 1
fi

# Step 2: Verify order details
print_info "Step 2: Verifying order details..."

ORDER_DETAILS=$(curl -s -X GET "$BASE_URL/api/orders/$ORDER_ID" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Accept: application/json")

if ! validate_response "$ORDER_DETAILS" "true"; then
    print_error "Failed to retrieve order details"
    echo "$ORDER_DETAILS" | jq '.'
    exit 1
fi

ITEMS_COUNT=$(echo "$ORDER_DETAILS" | jq '.data.items | length')
DELIVERY_FEE=$(echo "$ORDER_DETAILS" | jq -r '.data.delivery_fee')

print_success "Order details retrieved successfully"
print_info "Items count: $ITEMS_COUNT"
print_info "Delivery fee: $DELIVERY_FEE FCFA"

# Step 3: Test payment initiation with Orange Money
print_info "Step 3: Initiating payment with Orange Money..."

PAYMENT_RESPONSE=$(curl -s -X POST "$BASE_URL/api/orders/$ORDER_ID/payment" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d "{
        \"payment_method\": \"$PAYMENT_METHOD_ORANGE\",
        \"payment_details\": {
            \"phone\": \"$TEST_PHONE\",
            \"name\": \"$TEST_NAME\"
        }
    }")

if ! validate_response "$PAYMENT_RESPONSE" "true"; then
    print_error "Payment initiation failed"
    echo "$PAYMENT_RESPONSE" | jq '.'
    exit 1
fi

PAYMENT_REFERENCE=$(echo "$PAYMENT_RESPONSE" | jq -r '.data.payment.reference')
PAYMENT_STATUS=$(echo "$PAYMENT_RESPONSE" | jq -r '.data.payment.status')
PAYMENT_METHOD=$(echo "$PAYMENT_RESPONSE" | jq -r '.data.payment.method')

print_success "Payment initiated successfully"
print_info "Payment Reference: $PAYMENT_REFERENCE"
print_info "Payment Status: $PAYMENT_STATUS"
print_info "Payment Method: $PAYMENT_METHOD"

# Step 4: Wait for async payment completion
print_info "Step 4: Waiting for payment completion..."
if ! wait_for_payment_completion "$ORDER_ID" "$TOKEN"; then
    print_error "Payment completion check failed"
    exit 1
fi

# Step 5: Verify final order status
print_info "Step 5: Verifying final order status..."

FINAL_ORDER=$(curl -s -X GET "$BASE_URL/api/orders/$ORDER_ID" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Accept: application/json")

if ! validate_response "$FINAL_ORDER" "true"; then
    print_error "Failed to retrieve final order status"
    echo "$FINAL_ORDER" | jq '.'
    exit 1
fi

FINAL_STATUS=$(echo "$FINAL_ORDER" | jq -r '.data.status')
FINAL_PAYMENT_STATUS=$(echo "$FINAL_ORDER" | jq -r '.data.payment.payment_status // "none"')

print_success "Final order status retrieved"
print_info "Final Order Status: $FINAL_STATUS"
print_info "Final Payment Status: $FINAL_PAYMENT_STATUS"

# Verify final status is paid
if [ "$FINAL_STATUS" != "paid" ]; then
    print_error "Expected final status 'paid', got '$FINAL_STATUS'"
    exit 1
fi

if [ "$FINAL_PAYMENT_STATUS" != "paid" ]; then
    print_error "Expected payment status 'paid', got '$FINAL_PAYMENT_STATUS'"
    exit 1
fi

# Step 6: Test that order cannot accept new payment
print_info "Step 6: Testing that paid order cannot accept new payment..."

DUPLICATE_PAYMENT=$(curl -s -X POST "$BASE_URL/api/orders/$ORDER_ID/payment" \
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

# Should fail
if validate_response "$DUPLICATE_PAYMENT" "true"; then
    print_error "Paid order should not accept new payment"
    echo "$DUPLICATE_PAYMENT" | jq '.'
    exit 1
fi

ERROR_MESSAGE=$(echo "$DUPLICATE_PAYMENT" | jq -r '.message // "no message"')
if [[ "$ERROR_MESSAGE" == *"cannot_accept_payment"* ]] || [[ "$ERROR_MESSAGE" == *"Cannot accept"* ]]; then
    print_success "Correctly rejected duplicate payment attempt"
else
    print_error "Unexpected error message: $ERROR_MESSAGE"
    echo "$DUPLICATE_PAYMENT" | jq '.'
    exit 1
fi

print_header "✅ Complete Order Flow Test Completed Successfully"

echo -e "\n${GREEN}Flow Summary:${NC}"
echo "  ✅ Order Creation (Status: pending)"
echo "  ✅ Order Details Retrieval"
echo "  ✅ Payment Initiation (Orange Money)"
echo "  ✅ Async Payment Processing (1 minute wait)"
echo "  ✅ Order Status Update (Status: paid)"
echo "  ✅ Duplicate Payment Prevention"

echo -e "\n${GREEN}Final Order Details:${NC}"
echo "  📋 Order ID: $ORDER_ID"
echo "  💰 Total Amount: $ORDER_TOTAL FCFA"
echo "  📱 Payment Method: Orange Money"
echo "  ✅ Status: $FINAL_STATUS"
echo "  💳 Payment Status: $FINAL_PAYMENT_STATUS"