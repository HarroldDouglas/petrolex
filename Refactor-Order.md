# Order Process Refactoring Plan

## Current State Analysis

### Existing Architecture Issues
The current order creation process in `CreateOrderController` violates the Single Responsibility Principle by combining:
- Order validation and creation
- Payment initialization 
- Transaction management

This tightly coupled approach makes testing, maintenance, and future modifications difficult.

### Current Flow Problems
1. **Monolithic endpoint**: Single endpoint handles complete order-to-payment flow
2. **Insufficient validation**: Limited price, stock, and delivery fee validation
3. **Poor error handling**: Generic error responses without localized user feedback
4. **Async payment simulation**: Missing proper payment callback simulation

## Proposed Architecture

### 1. Separation of Concerns

#### 1.1 Order Creation Endpoint
**Endpoint**: `POST /api/orders`
**Responsibility**: Create and validate orders without payment processing

**Enhanced Validations**:
- Unit price verification against `ProductCategoryService::getProductPrice()`
- Stock availability validation via `ProductCategoryService::getProductQuantity()`
- Total amount calculation verification
- Delivery fee validation against `DeliveryType::fee()`

**New Request Structure**:
```php
CreateOrderRequest {
    delivery_address_id: int
    distribution_center_id: int  
    delivery_type: string
    items: OrderItemRequest[]
    comments?: string
    // Remove payment_method - handled separately
}

OrderItemRequest {
    product_category_id: int
    quantity: int
    option?: string
    unit_price: float  // Required for validation
}
```

**Enhanced Error Responses**:
- Localized error messages based on user language
- Specific validation failures (insufficient stock, price mismatch, etc.)
- Clear field-level error mapping

#### 1.2 Payment Initialization Endpoint  
**Endpoint**: `POST /api/orders/{orderId}/payment`
**Responsibility**: Initialize payment for existing orders

**Request Structure**:
```php
InitiatePaymentRequest {
    payment_method: string
    payment_details: PaymentDetailsRequest
}

// Polymorphic based on payment_method
PaymentDetailsRequest {
    // For orange_money/mtn_money
    phone?: string
    name?: string
    
    // For credit_card  
    card_number?: string
    cvv?: string
    expiry_date?: string
    cardholder_name?: string
}
```

**Enhanced Validation**:
- Payment method-specific validation rules
- Phone number format validation for mobile money
- Credit card validation (Luhn algorithm, CVV format, expiry date)
- Localized validation messages

#### 1.3 Order Details Endpoint
**Endpoint**: `GET /api/orders/{orderId}`
**Status**: Already implemented - no changes needed

### 2. Enhanced Request Validation

#### 2.1 Enhanced CreateOrderRequest
**Responsibility**: Complete order validation using Laravel FormRequest

```php
final class CreateOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'delivery_address_id' => ['required', 'integer', 'exists:customer_delivery_addresses,id'],
            'distribution_center_id' => ['required', 'integer', 'exists:distribution_centers,id'],
            'delivery_type' => ['required', 'string', Rule::in(DeliveryType::values())],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.product_category_id' => ['required', 'integer', 'exists:product_categories,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.option' => ['nullable', 'string', Rule::in(BottleOrderType::values())],
            'delivery_fee' => ['required', 'numeric', 'min:0'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'comments' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validatePricing($validator);
            $this->validateStock($validator);
            $this->validateDeliveryFee($validator);
            $this->validateTotalAmount($validator);
        });
    }

    private function validatePricing($validator): void
    {
        foreach ($this->input('items', []) as $index => $item) {
            $expectedPrice = app(ProductCategoryService::class)->getProductPrice(
                $item['product_category_id'], 
                isset($item['option']) ? BottleOrderType::from($item['option']) : null
            );
            
            if (abs($item['unit_price'] - $expectedPrice) > 0.01) {
                $validator->errors()->add("items.{$index}.unit_price", 
                    __('validation.order.price_mismatch', [
                        'expected' => $expectedPrice, 
                        'provided' => $item['unit_price']
                    ])
                );
            }
        }
    }

    private function validateStock($validator): void
    {
        $distributionCenterId = $this->input('distribution_center_id');
        
        foreach ($this->input('items', []) as $index => $item) {
            $availableStock = app(ProductCategoryService::class)->getProductQuantity(
                $item['product_category_id'], 
                $distributionCenterId
            );
            
            if ($item['quantity'] > $availableStock) {
                $validator->errors()->add("items.{$index}.quantity", 
                    __('validation.order.insufficient_stock', [
                        'requested' => $item['quantity'], 
                        'available' => $availableStock
                    ])
                );
            }
        }
    }

    private function validateDeliveryFee($validator): void
    {
        $deliveryType = DeliveryType::from($this->input('delivery_type'));
        $expectedFee = $deliveryType->fee();
        $providedFee = $this->input('delivery_fee');
        
        if (abs($providedFee - $expectedFee) > 0.01) {
            $validator->errors()->add('delivery_fee', 
                __('validation.order.delivery_fee_mismatch', [
                    'expected' => $expectedFee, 
                    'provided' => $providedFee
                ])
            );
        }
    }

    private function validateTotalAmount($validator): void
    {
        $itemsTotal = collect($this->input('items', []))->sum(fn($item) => $item['unit_price'] * $item['quantity']);
        $deliveryFee = $this->input('delivery_fee', 0);
        $expectedTotal = $itemsTotal + $deliveryFee;
        $providedTotal = $this->input('total_amount');
        
        if (abs($providedTotal - $expectedTotal) > 0.01) {
            $validator->errors()->add('total_amount', 
                __('validation.order.total_amount_mismatch', [
                    'expected' => $expectedTotal, 
                    'provided' => $providedTotal
                ])
            );
        }
    }
}
```

#### 2.2 New InitiatePaymentRequest
**Responsibility**: Payment validation using Laravel FormRequest

```php
final class InitiatePaymentRequest extends FormRequest
{
    public function rules(): array
    {
        $paymentMethod = $this->input('payment_method');
        
        $rules = [
            'payment_method' => ['required', 'string', Rule::in(PaymentMethod::values())],
        ];

        if ($paymentMethod === PaymentMethod::ORANGE_MONEY()->value || $paymentMethod === PaymentMethod::MTN_MONEY()->value) {
            $rules['payment_details.phone'] = ['required', 'string', 'regex:/^[0-9]{8,15}$/'];
            $rules['payment_details.name'] = ['required', 'string', 'max:255'];
        } elseif ($paymentMethod === PaymentMethod::CREDIT_CARD()->value) {
            $rules['payment_details.card_number'] = ['required', 'string', 'regex:/^[0-9]{13,19}$/'];
            $rules['payment_details.cvv'] = ['required', 'string', 'regex:/^[0-9]{3,4}$/'];
            $rules['payment_details.expiry_date'] = ['required', 'date_format:m/y', 'after:today'];
            $rules['payment_details.cardholder_name'] = ['required', 'string', 'max:255'];
        }

        return $rules;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('payment_method') === PaymentMethod::CREDIT_CARD()->value) {
                $this->validateCreditCard($validator);
            }
        });
    }

    private function validateCreditCard($validator): void
    {
        $cardNumber = str_replace(' ', '', $this->input('payment_details.card_number', ''));
        
        if (!$this->luhnCheck($cardNumber)) {
            $validator->errors()->add('payment_details.card_number', 
                __('validation.payment.invalid_card_number')
            );
        }
    }

    private function luhnCheck(string $number): bool
    {
        $sum = 0;
        $length = strlen($number);
        
        for ($i = $length - 1; $i >= 0; $i--) {
            $digit = (int) $number[$i];
            if (($length - $i) % 2 === 0) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $sum += $digit;
        }
        
        return $sum % 10 === 0;
    }
}
```

#### 2.3 Enhanced PaymentService
**Current**: Basic payment initialization
**Enhanced**: Clean business logic without validation

```php
class PaymentService
{
    public function initiatePayment(Order $order, PaymentMethod $method, array $paymentDetails): OrderPayment
    {
        $payment = $this->createOrderPayment($order, $method);
        $gateway = $this->gatewayFactory->create($method->value);
        $response = $gateway->initiatePayment($payment, $paymentDetails);
        $this->updatePaymentFromResponse($payment, $response);

        // TODO: Remove this simulation when real payment callbacks are implemented
        $this->schedulePaymentCallback($payment);

        return $payment->refresh();
    }

    private function schedulePaymentCallback(OrderPayment $payment): void
    {
        dispatch(new UpdatePaymentStatusJob($payment->id))->delay(now()->addMinute());
    }
}
```

### 3. Controllers Implementation

#### 3.1 Refactored CreateOrderController

```php
final class CreateOrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function __invoke(CreateOrderRequest $request): CreateOrderResponse
    {
        $data = $request->validated();
        $data['customer_id'] = $request->user()->customer->id;

        $orderDTO = CreateOrderDTO::from($data);

        $order = $this->orderService->createWithoutPayment($orderDTO);

        $order->load([
            'items.productCategory',
            'deliveryAddress.neighborhood.municipality.city.country',
            'customer.user.country',
            'distributionCenter.neighborhood.municipality.city.country',
        ]);

        return CreateOrderResponse::withOrder($order);
    }
}
```

#### 3.2 New InitiatePaymentController

```php
final class InitiatePaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    public function __invoke(InitiatePaymentRequest $request, Order $order): InitiatePaymentResponse
    {
        $this->authorize('initiate-payment', $order);

        if (!$order->canAcceptPayment()) {
            throw new BadRequestException(__('order.cannot_accept_payment'));
        }

        $data = $request->validated();
        
        $payment = $this->paymentService->initiatePayment(
            $order,
            PaymentMethod::from($data['payment_method']),
            $data['payment_details']
        );

        return InitiatePaymentResponse::withPayment($payment);
    }
}
```

#### 3.3 Enhanced OrderService

```php
class OrderService extends BaseServiceForEntity
{
    public function createWithoutPayment(CreateOrderDTO $orderDTO): Order
    {
        return $this->executeInTransaction(function () use ($orderDTO) {
            $orderItemsData = array_map(function (OrderItemDTO $itemDTO): OrderItemDTO {
                // No need to recalculate prices - already validated in request
                $itemDTO->option = $itemDTO->option ?? null;
                return $itemDTO;
            }, $orderDTO->items);

            $orderData = $orderDTO->toArray();
            if (isset($orderData['items'])) {
                unset($orderData['items']);
            }

            $orderData['status'] = OrderStatus::PENDING()->value;

            /** @var Order $order */
            $order = $this->repository->create($orderData);

            Event::dispatch(new OrderCreatedEvent($order, $orderItemsData));

            $order->load('items.productCategory');

            return $order;
        });
    }

    // Keep existing create() method for backward compatibility
    public function create(array $data): Order
    {
        // Legacy method - calls createWithoutPayment internally
        $orderDTO = CreateOrderDTO::from($data);
        return $this->createWithoutPayment($orderDTO);
    }
}
```

#### 3.4 Enhanced Order Model

```php
class Order extends Model
{
    public function canAcceptPayment(): bool
    {
        return $this->status === OrderStatus::PENDING() && 
               !$this->payments()->whereIn('payment_status', [
                   PaymentStatus::PAID()->value,
                   PaymentStatus::PENDING()->value
               ])->exists();
    }
}
```

### 4. Implementation Plan

#### Phase 1: Request Layer Enhancement
1. Update `CreateOrderRequest` with comprehensive validation including pricing, stock, and delivery fee
2. Create `InitiatePaymentRequest` with payment method-specific validation rules
3. Add custom validation messages in language files
4. Implement Luhn algorithm for credit card validation

#### Phase 2: Controller Refactoring  
1. Refactor `CreateOrderController` to remove payment logic
2. Create `InitiatePaymentController` for separate payment processing
3. Add proper authorization checks for payment initiation
4. Implement business rule validation (order state checks)

#### Phase 3: Service Layer Enhancement
1. Add `createWithoutPayment()` method to `OrderService`
2. Enhance `PaymentService` with async callback scheduling
3. Create `UpdatePaymentStatusJob` for payment simulation
4. Add order state validation methods

#### Phase 4: Response Enhancement
1. Update `CreateOrderResponse` to remove payment data
2. Create `InitiatePaymentResponse` for payment initialization
3. Add structured error responses with localization
4. Update API documentation

#### Phase 5: Route & Middleware Updates
1. Add new payment route: `POST /api/orders/{order}/payment`
2. Add proper middleware for payment authorization
3. Update existing order routes if needed
4. Add route model binding for Order

#### Phase 6: Testing & Validation
1. Update existing order creation tests
2. Create comprehensive payment initialization tests
3. Add validation tests for all business rules
4. Create integration tests for separated flow

### 5. Database Considerations

No schema changes required - existing tables support the new architecture:
- `orders` table remains unchanged
- `order_payments` table structure supports enhanced payment details
- Existing relationships preserved

### 6. Async Payment Simulation

**Job Implementation**:
```php
class UpdatePaymentStatusJob implements ShouldQueue
{
    public function __construct(
        private int $paymentId
    ) {}

    public function handle(): void
    {
        $payment = OrderPayment::findOrFail($this->paymentId);
        
        // Simulate successful payment after 1 minute
        $payment->update([
            'payment_status' => PaymentStatus::PAID()->value,
            'payment_date' => now(),
            'amount_paid' => $payment->amount_due,
            'amount_due' => 0,
        ]);

        // Update order status to PAID
        $payment->order->update([
            'status' => OrderStatus::PAID()->value,
            'confirmed_at' => now(),
        ]);

        // TODO: Remove this simulation when real payment callbacks are implemented
    }
}
```

### 7. Language Files Structure

**resources/lang/fr/validation/order.php**:
```php
return [
    'price_mismatch' => 'Prix incorrect: attendu :expected, fourni :provided',
    'insufficient_stock' => 'Stock insuffisant: demandé :requested, disponible :available',
    'delivery_fee_mismatch' => 'Frais de livraison incorrect: attendu :expected, fourni :provided',
    'total_amount_mismatch' => 'Montant total incorrect: attendu :expected, fourni :provided',
];
```

**resources/lang/fr/validation/payment.php**:
```php
return [
    'invalid_card_number' => 'Numéro de carte invalide',
    'invalid_expiry_date' => 'Date d\'expiration invalide',
    'invalid_phone_number' => 'Numéro de téléphone invalide',
];
```

### 8. Error Handling Strategy

**Error Response Structure**:
```php
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "items.0.unit_price": ["Prix incorrect: attendu 5000, fourni 4500"],
        "items.1.quantity": ["Stock insuffisant: demandé 10, disponible 5"]
    },
    "error_code": "VALIDATION_ERROR"
}
```

### 9. Benefits of This Architecture

1. **Laravel Best Practices**: Uses FormRequest for validation instead of service layer validation
2. **Single Responsibility**: Each endpoint and service has one clear purpose
3. **Enhanced Validation**: Comprehensive business rule validation in the right place
4. **Better Error Handling**: Localized, specific error messages
5. **Testability**: Clean separation enables focused unit testing
6. **Maintainability**: Clear separation enables easier modifications
7. **Extensibility**: Easy to add new payment methods or validation rules
8. **SOLID Compliance**: Follows SOLID principles throughout

### 10. Migration Strategy

1. **Backward Compatibility**: Keep existing endpoint during transition
2. **Gradual Migration**: Migrate clients to new endpoints progressively  
3. **Feature Flags**: Use feature flags to control endpoint availability
4. **Deprecation**: Mark old endpoint as deprecated with sunset date

This refactoring transforms the order process from a monolithic approach to a clean, maintainable, and extensible architecture that follows Laravel best practices and SOLID principles.