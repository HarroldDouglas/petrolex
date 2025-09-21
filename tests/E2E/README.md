# E2E Tests - Petrolex Order System

## Overview
This directory contains End-to-End (E2E) tests for the Petrolex order system. These tests validate the complete order flow from creation to payment completion against a real database and running server.

## Architecture Tested
The tests validate the new separated architecture:
- **Order Creation**: `POST /api/orders` (without payment)
- **Payment Initiation**: `POST /api/orders/{id}/payment` (separate endpoint)
- **Async Payment Processing**: Queue jobs with 1-minute delay simulation

## Test Suite Structure

### Core Configuration
- **`config.sh`**: Central configuration file with constants, helper functions, and test data

### Test Scripts

#### 1. Price Validation (`test_price_validation.sh`)
Tests the price validation system:
- ✅ Detects incorrect unit prices for individual items
- ✅ Validates multiple incorrect prices simultaneously  
- ✅ Ensures correct prices are accepted
- ✅ Verifies localized error messages in French

#### 2. Total Amount Validation (`test_total_validation.sh`)
Tests total amount calculation validation:
- ✅ Detects total amounts that are too low
- ✅ Detects total amounts that are too high
- ✅ Validates delivery fee consistency
- ✅ Ensures correct totals are accepted

#### 3. Payment Methods Validation (`test_payment_methods.sh`)
Tests all payment method validations:
- ✅ Orange Money phone number format validation
- ✅ MTN Money successful payment processing
- ✅ Credit Card Luhn algorithm validation
- ✅ CVV format validation
- ✅ Expiry date validation (future dates only)
- ✅ Invalid payment method rejection

#### 4. Complete Order Flow (`test_complete_flow.sh`)
Tests the entire end-to-end workflow:
- ✅ Order creation with proper validation
- ✅ Order details retrieval
- ✅ Payment initiation (Orange Money)
- ✅ Async payment processing (1-minute wait)
- ✅ Order status update to "paid"
- ✅ Duplicate payment prevention

## Prerequisites

### Server Requirements
1. **Laravel Server**: Must be running on `http://localhost:8001`
   ```bash
   php artisan serve --host=0.0.0.0 --port=8001
   ```

2. **Queue Workers**: Must be running for async payment processing
   ```bash
   php artisan queue:work
   ```

3. **Database**: Must have test data seeded
   - Customer user: `customer1@test.com` / `password`
   - Product categories with proper pricing
   - Distribution centers and delivery addresses

### System Requirements
- `curl` command-line tool
- `jq` for JSON parsing
- `bash` shell environment

## Running Tests

### Run All Tests
```bash
cd tests/E2E/order
./run_all_tests.sh
```

### Run Individual Tests
```bash
# Price validation only
./test_price_validation.sh

# Total validation only  
./test_total_validation.sh

# Payment methods only
./test_payment_methods.sh

# Complete flow only
./test_complete_flow.sh
```

## Test Configuration

### Test Data Constants
All test data is centralized in `config.sh`:

```bash
# User Credentials
TEST_EMAIL="customer1@test.com"
TEST_PASSWORD="password"

# Product Pricing
PRODUCT_CATEGORY_1_FULL_PRICE=5000
PRODUCT_CATEGORY_2_FULL_PRICE=6500

# Delivery Fees
DELIVERY_FEE_NORMAL=500
DELIVERY_FEE_FAST=1000

# Payment Test Data
TEST_PHONE="677889900"
TEST_CARD_NUMBER="4000000000000002"  # Valid Visa test card
```

### Customization
To adapt tests for different environments:
1. Update `BASE_URL` in `config.sh`
2. Modify test credentials and IDs
3. Adjust expected prices and fees
4. Update payment test data

## Expected Test Output

### Successful Run
```
🚀 Petrolex E2E Test Suite - Order Module
✅ Server is running
⚠️  Make sure queue workers are running

🧪 Starting Test Execution
✅ Price Validation Tests completed successfully
✅ Total Amount Validation Tests completed successfully  
✅ Payment Methods Validation Tests completed successfully
✅ Complete Order Flow Test completed successfully

📊 Test Execution Summary
📈 Total Tests: 4
✅ Passed: 4
❌ Failed: 0
🎉 Success Rate: 100%

🎉 ALL TESTS PASSED! 🎉
```

### Test Validation Coverage
- **Input Validation**: Price, total, delivery fee consistency
- **Business Rules**: Stock availability, payment method restrictions
- **Security**: User authorization, order ownership
- **Async Processing**: Queue job execution, status updates
- **Error Handling**: Localized messages, proper HTTP status codes

## Integration with CI/CD

These tests can be integrated into CI/CD pipelines:

```yaml
# Example GitHub Actions step
- name: Run E2E Tests
  run: |
    php artisan serve --host=0.0.0.0 --port=8001 &
    php artisan queue:work &
    sleep 5
    cd tests/E2E/order
    ./run_all_tests.sh
```

## Troubleshooting

### Common Issues
1. **Server not running**: Start with `php artisan serve --port=8001`
2. **Queue not working**: Start with `php artisan queue:work`
3. **Authentication fails**: Check test user exists in database
4. **Price mismatches**: Verify product category pricing in database

### Debug Mode
Add `set -x` to any test script for detailed execution traces:
```bash
#!/bin/bash
set -x  # Enable debug mode
set -e
```

## Maintenance

### Updating Tests
When the API changes:
1. Update endpoint URLs in test scripts
2. Modify expected response structures
3. Adjust validation rules as needed
4. Update test data constants

### Adding New Tests
1. Create new test script following naming convention
2. Source `config.sh` for consistency
3. Use helper functions for common operations
4. Add to `run_all_tests.sh` execution sequence

This E2E test suite ensures the order system maintains high quality and reliability across deployments.