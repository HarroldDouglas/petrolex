<?php

// Example usage of PaymentDetailsData DTO

use App\DTOs\PaymentDetailsData;

// Example 1: Mobile payment (MTN Money)
$mobilePaymentData = [
    'phone' => '676826360',
];

$mobilePaymentDto = PaymentDetailsData::from($mobilePaymentData);

echo "Mobile Payment:\n";
echo '- Is mobile payment: '.($mobilePaymentDto->isMobilePayment() ? 'Yes' : 'No')."\n";
echo '- Is card payment: '.($mobilePaymentDto->isCardPayment() ? 'Yes' : 'No')."\n";
echo '- Phone: '.($mobilePaymentDto->getPhone() ?? 'Not provided')."\n\n";

// Example 2: Card payment
$cardPaymentData = [
    'card_number' => '4111111111111111',
    'cvv' => '123',
    'expiry_date' => '12/25',
    'cardholder_name' => 'Jean Dupont',
];

$cardPaymentDto = PaymentDetailsData::from($cardPaymentData);

echo "Card Payment:\n";
echo '- Is mobile payment: '.($cardPaymentDto->isMobilePayment() ? 'Yes' : 'No')."\n";
echo '- Is card payment: '.($cardPaymentDto->isCardPayment() ? 'Yes' : 'No')."\n";
echo '- Card details: '.json_encode($cardPaymentDto->getCardDetails())."\n\n";

// Example 3: Mixed data (both phone and card - could happen in complex scenarios)
$mixedPaymentData = [
    'phone' => '676826360',
    'card_number' => '4111111111111111',
    'cvv' => '123',
    'expiry_date' => '12/25',
    'cardholder_name' => 'Jean Dupont',
];

$mixedPaymentDto = PaymentDetailsData::from($mixedPaymentData);

echo "Mixed Payment Data:\n";
echo '- Is mobile payment: '.($mixedPaymentDto->isMobilePayment() ? 'Yes' : 'No')."\n";
echo '- Is card payment: '.($mixedPaymentDto->isCardPayment() ? 'Yes' : 'No')."\n";
echo '- Full array: '.json_encode($mixedPaymentDto->toArray())."\n";
