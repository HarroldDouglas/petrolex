# Payment Methods API with USSD Codes

## Overview
The PaymentMethod enum and related API endpoints have been enhanced to include USSD codes for mobile payment validation.

## USSD Codes Added
- **Orange Money**: `#150*50#`
- **MTN Money**: `*126#`
- **Credit Card**: `null` (not applicable)

## API Endpoints Updated

### GET /api/payment-methods
Returns all available payment methods with their USSD codes.

**Response Structure:**
```json
{
    "_metadata": {
        "success": true,
        "message": "Payment methods retrieved successfully."
    },
    "data": [
        {
            "value": "orange_money",
            "label": "Orange Money",
            "ussd_code": "#150*50#"
        },
        {
            "value": "mtn_money",
            "label": "MTN Money",
            "ussd_code": "*126#"
        },
        {
            "value": "credit_card",
            "label": "Carte Bancaire",
            "ussd_code": null
        }
    ]
}
```

## Resources Updated

### OrderPaymentResource
Now includes `method_ussd_code` field:
```json
{
    "id": 123,
    "status": "paid",
    "method": "mtn_money",
    "method_label": "MTN Money",
    "method_ussd_code": "*126#"
}
```

### OrderDetailResource
Payment object now includes `payment_method_ussd_code` field:
```json
{
    "payment": {
        "payment_method": "orange_money",
        "payment_method_label": "Orange Money",
        "payment_method_ussd_code": "#150*50#"
    }
}
```

### CreateOrderResponse
Payment object includes `payment_method_ussd_code` field when creating orders with payment:
```json
{
    "payment": {
        "payment_method": "mtn_money",
        "payment_method_label": "MTN Money",
        "payment_method_ussd_code": "*126#"
    }
}
```

## Code Usage

### PaymentMethod Enum
```php
use App\Enums\PaymentMethod;

$method = PaymentMethod::MTN_MONEY();
echo $method->ussdCode(); // "*126#"

$method = PaymentMethod::ORANGE_MONEY();
echo $method->ussdCode(); // "#150*50#"

$method = PaymentMethod::CREDIT_CARD();
echo $method->ussdCode(); // null
```

## Mobile App Integration
The mobile app can now:
1. Call `/api/payment-methods` to get all available payment methods with USSD codes
2. Display the appropriate USSD code to users for mobile money payments
3. Show instructions like "Dial *126# to validate your MTN Money payment"
4. Hide USSD codes for credit card payments (null values)

## Testing
Run the test scripts to verify implementation:
- `php test_ussd_code_implementation.php` - Tests enum and resource implementation
- `php test_payment_methods_api.php` - Tests API endpoint functionality