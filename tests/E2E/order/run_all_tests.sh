#!/bin/bash

# =============================================================================
# E2E Test Suite Runner
# Runs all order-related E2E tests in sequence
# =============================================================================

set -e

# Load configuration
source "$(dirname "$0")/config.sh"

print_header "🚀 Petrolex E2E Test Suite - Order Module"

# Test execution summary
TOTAL_TESTS=0
PASSED_TESTS=0
FAILED_TESTS=0
TEST_RESULTS=()

# Function to run a single test
run_test() {
    local test_file="$1"
    local test_name="$2"
    
    print_info "Running: $test_name"
    echo "────────────────────────────────────────────────────────────"
    
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    
    if bash "$test_file"; then
        PASSED_TESTS=$((PASSED_TESTS + 1))
        TEST_RESULTS+=("✅ $test_name")
        print_success "$test_name completed successfully"
    else
        FAILED_TESTS=$((FAILED_TESTS + 1))
        TEST_RESULTS+=("❌ $test_name")
        print_error "$test_name failed"
    fi
    
    echo ""
}

# Get script directory
SCRIPT_DIR="$(dirname "$0")"

# Check if server is running
print_info "Checking server status..."
if ! curl -s "$BASE_URL/api/health" > /dev/null; then
    print_error "Server is not running at $BASE_URL"
    print_info "Please start the server with: php artisan serve --host=0.0.0.0 --port=8001"
    exit 1
fi
print_success "Server is running"

# Check if queues are running
print_info "Checking queue status..."
print_warning "Make sure queue workers are running: php artisan queue:work"

echo ""
print_header "🧪 Starting Test Execution"

# Run all tests
run_test "$SCRIPT_DIR/test_price_validation.sh" "Price Validation Tests"
run_test "$SCRIPT_DIR/test_total_validation.sh" "Total Amount Validation Tests"
run_test "$SCRIPT_DIR/test_payment_methods.sh" "Payment Methods Validation Tests"
run_test "$SCRIPT_DIR/test_complete_flow.sh" "Complete Order Flow Test"
run_test "$SCRIPT_DIR/test_order_cancellation.sh" "Order Cancellation Test"

# Print final summary
print_header "📊 Test Execution Summary"

echo -e "${BLUE}Test Results:${NC}"
for result in "${TEST_RESULTS[@]}"; do
    echo "  $result"
done

echo ""
echo -e "${BLUE}Statistics:${NC}"
echo -e "  📈 Total Tests: ${BLUE}$TOTAL_TESTS${NC}"
echo -e "  ✅ Passed: ${GREEN}$PASSED_TESTS${NC}"
echo -e "  ❌ Failed: ${RED}$FAILED_TESTS${NC}"

if [ $FAILED_TESTS -eq 0 ]; then
    echo -e "  🎉 Success Rate: ${GREEN}100%${NC}"
    print_header "🎉 ALL TESTS PASSED! 🎉"
    echo -e "${GREEN}The order system is working perfectly!${NC}"
    echo ""
    echo -e "${GREEN}✅ Architecture Validation:${NC}"
    echo "  • Separated order creation and payment initiation"
    echo "  • Comprehensive validation rules"
    echo "  • Proper error handling and localization"
    echo "  • Async payment processing"
    echo "  • Business logic enforcement"
    echo ""
    echo -e "${GREEN}✅ Payment Methods:${NC}"
    echo "  • Orange Money validation"
    echo "  • MTN Money validation"
    echo "  • Credit Card Luhn algorithm"
    echo "  • CVV and expiry date validation"
    echo ""
    echo -e "${GREEN}✅ Business Rules:${NC}"
    echo "  • Price consistency validation"
    echo "  • Total amount verification"
    echo "  • Delivery fee validation"
    echo "  • Duplicate payment prevention"
    exit 0
else
    success_rate=$(( (PASSED_TESTS * 100) / TOTAL_TESTS ))
    echo -e "  📊 Success Rate: ${YELLOW}${success_rate}%${NC}"
    print_header "⚠️  SOME TESTS FAILED"
    echo -e "${RED}Please review the failed tests and fix the issues.${NC}"
    exit 1
fi