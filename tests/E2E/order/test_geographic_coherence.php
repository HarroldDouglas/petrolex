#!/usr/bin/env php
<?php

/**
 * =============================================================================
 * E2E Test: Geographic Coherence Validation
 * Tests that orders are only accepted when delivery address and distribution
 * center are in the same municipality
 * =============================================================================
 */

require __DIR__.'/../../../vendor/autoload.php';

use App\Models\CustomerDeliveryAddress;
use App\Models\DistributionCenter;
use App\Models\Geography\Municipality;
use App\Models\Geography\Neighborhood;
use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once __DIR__.'/../../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Configuration
$baseUrl = 'http://localhost:8001';
$testEmail = 'customer1@test.com';
$testPassword = 'password';

// Colors for output
$colors = [
    'RED' => "\033[0;31m",
    'GREEN' => "\033[0;32m",
    'YELLOW' => "\033[1;33m",
    'BLUE' => "\033[0;34m",
    'NC' => "\033[0m",
];

// Helper functions
function printHeader($message)
{
    global $colors;
    echo "\n{$colors['BLUE']}================================================{$colors['NC']}\n";
    echo "{$colors['BLUE']} $message{$colors['NC']}\n";
    echo "{$colors['BLUE']}================================================{$colors['NC']}\n\n";
}

function printSuccess($message)
{
    global $colors;
    echo "{$colors['GREEN']}✅ $message{$colors['NC']}\n";
}

function printError($message)
{
    global $colors;
    echo "{$colors['RED']}❌ $message{$colors['NC']}\n";
}

function printInfo($message)
{
    global $colors;
    echo "{$colors['BLUE']}ℹ️  $message{$colors['NC']}\n";
}

function printWarning($message)
{
    global $colors;
    echo "{$colors['YELLOW']}⚠️  $message{$colors['NC']}\n";
}

// Get authentication token
function getAuthToken($baseUrl, $email, $password)
{
    printInfo('Authenticating user...');

    $response = Http::post("$baseUrl/api/login", [
        'login' => $email,
        'password' => $password,
    ]);

    if ($response->successful() && isset($response->json()['data']['access_token'])) {
        printSuccess('Authentication successful');

        return $response->json()['data']['access_token'];
    }

    printError('Failed to authenticate');
    echo json_encode($response->json(), JSON_PRETTY_PRINT)."\n";
    exit(1);
}

// Test counters
$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

// Run a single test case
function runTestCase($testName, $centerId, $deliveryNeighborhoodId, $shouldSucceed, $token, $baseUrl, &$totalTests, &$passedTests, &$failedTests, $customerId)
{
    $totalTests++;

    printInfo("Test Case $totalTests: $testName");

    // Get neighborhood and municipality info for logging
    $neighborhood = Neighborhood::with(['municipality.city'])->find($deliveryNeighborhoodId);
    $center = DistributionCenter::with(['neighborhood.municipality.city'])->find($centerId);

    if (! $neighborhood || ! $center) {
        printError("Invalid test data: Neighborhood ID $deliveryNeighborhoodId or Center ID $centerId not found");
        $failedTests++;

        return;
    }

    printInfo("  Center: {$center->name} ({$center->neighborhood->municipality->city->name} - {$center->neighborhood->municipality->name})");
    printInfo("  Delivery: {$neighborhood->name} ({$neighborhood->municipality->city->name} - {$neighborhood->municipality->name})");

    // Create a delivery address directly in DB (no API endpoint available)
    try {
        $deliveryAddress = CustomerDeliveryAddress::create([
            'customer_id' => $customerId,
            'neighborhood_id' => $deliveryNeighborhoodId,
            'address' => 'Test Address for E2E',
            'additional_info' => 'E2E Test - Geographic Coherence',
            'label' => 'E2E Test',
            'contact_firstname' => 'Test',
            'contact_lastname' => 'User',
            'phone' => '+237677889900',
            'phone_country_code' => '+237',
            'email' => 'test@example.com',
            'is_default' => false,
        ]);

        $deliveryAddressId = $deliveryAddress->id;
    } catch (\Exception $e) {
        printError('Failed to create delivery address: '.$e->getMessage());
        $failedTests++;

        return;
    }

    // Try to create an order with this address and center
    $orderResponse = Http::withToken($token)
        ->withHeaders(['Accept' => 'application/json'])
        ->post("$baseUrl/api/orders", [
            'delivery_address_id' => $deliveryAddressId,
            'distribution_center_id' => $centerId,
            'delivery_type' => 'normal',
            'items' => [
                [
                    'product_category_id' => 1,
                    'quantity' => 1,
                    'unit_price' => 5000,
                    'option' => 'bottle_with_content',
                ],
            ],
            'delivery_fee' => 500,
            'total_amount' => 5500,
            'comments' => 'E2E Test - Geographic Coherence',
        ]);

    $orderCreated = $orderResponse->successful() && isset($orderResponse->json()['data']['order']['id']);

    // Clean up: Delete the test address from DB
    $deliveryAddress->delete();

    // Validate result
    if ($shouldSucceed) {
        if ($orderCreated) {
            printSuccess('✅ Order correctly ACCEPTED (same municipality)');
            $passedTests++;

            // Clean up: Delete the test order
            $orderId = $orderResponse->json()['data']['order']['id'];
            Http::withToken($token)->delete("$baseUrl/api/orders/$orderId");
        } else {
            printError('❌ Order should have been ACCEPTED but was REJECTED');
            echo json_encode($orderResponse->json(), JSON_PRETTY_PRINT)."\n";
            $failedTests++;
        }
    } else {
        if (! $orderCreated) {
            // Check for proper error message
            $responseJson = $orderResponse->json();
            $errorMsg = $responseJson['message'] ?? 'unknown';

            // If no message, try to get from errors array
            if ($errorMsg === 'unknown' && isset($responseJson['errors'])) {
                $errorMsg = is_array($responseJson['errors'])
                    ? json_encode($responseJson['errors'])
                    : $responseJson['errors'];
            }

            $errorString = is_array($errorMsg) ? json_encode($errorMsg) : $errorMsg;

            if (stripos($errorString, 'municipality') !== false || stripos($errorString, 'municipalité') !== false) {
                printSuccess('✅ Order correctly REJECTED with proper error (different municipality)');
                printInfo('  Error: '.(is_string($errorMsg) ? $errorMsg : json_encode($errorMsg)));
            } else {
                printWarning('⚠️  Order correctly REJECTED but error message unclear');
                printInfo("  Error: $errorString");
            }
            $passedTests++;
        } else {
            printError('❌ Order should have been REJECTED but was ACCEPTED');
            echo json_encode($orderResponse->json(), JSON_PRETTY_PRINT)."\n";
            $failedTests++;

            // Clean up the incorrectly created order
            $orderId = $orderResponse->json()['data']['order']['id'];
            Http::withToken($token)->delete("$baseUrl/api/orders/$orderId");
        }
    }
}

// =============================================================================
// Main Test Execution
// =============================================================================

printHeader('E2E Test: Geographic Coherence Validation');

// Get authentication token
$token = getAuthToken($baseUrl, $testEmail, $testPassword);

// Get customer ID
$customerId = 1; // From seeded data

// =============================================================================
// Test Suite 1: Same Municipality (Should SUCCEED)
// =============================================================================

printHeader('Test Suite 1: Same Municipality (Should SUCCEED)');

// Get test data from database
$yaoundeICenters = DistributionCenter::whereHas('neighborhood.municipality', function ($q) {
    $q->where('name', 'Yaoundé I');
})->get();

$yaoundeIINeighborhoods = Neighborhood::whereHas('municipality', function ($q) {
    $q->where('name', 'Yaoundé II');
})->get();

$doualaICenters = DistributionCenter::whereHas('neighborhood.municipality', function ($q) {
    $q->where('name', 'Douala I');
})->get();

// Test 1: Yaoundé I center with Yaoundé I neighborhoods
if ($yaoundeICenters->isNotEmpty()) {
    $center = $yaoundeICenters->first();
    $neighborhoods = Neighborhood::whereHas('municipality', function ($q) {
        $q->where('name', 'Yaoundé I');
    })->limit(2)->get();

    foreach ($neighborhoods as $neighborhood) {
        runTestCase(
            "Yaoundé I center → Yaoundé I delivery ({$neighborhood->name})",
            $center->id,
            $neighborhood->id,
            true,
            $token,
            $baseUrl,
            $totalTests,
            $passedTests,
            $failedTests,
            $customerId
        );
    }
}

// Test 2: Douala I center with Douala I neighborhood
if ($doualaICenters->isNotEmpty()) {
    $center = $doualaICenters->first();
    $neighborhoods = Neighborhood::whereHas('municipality', function ($q) {
        $q->where('name', 'Douala I');
    })->limit(1)->get();

    foreach ($neighborhoods as $neighborhood) {
        runTestCase(
            "Douala I center → Douala I delivery ({$neighborhood->name})",
            $center->id,
            $neighborhood->id,
            true,
            $token,
            $baseUrl,
            $totalTests,
            $passedTests,
            $failedTests,
            $customerId
        );
    }
}

// =============================================================================
// Test Suite 2: Different Municipality - Same City (Should FAIL)
// =============================================================================

printHeader('Test Suite 2: Different Municipality - Same City (Should FAIL)');

// Test 3: Yaoundé I center with Yaoundé II neighborhood
if ($yaoundeICenters->isNotEmpty() && $yaoundeIINeighborhoods->isNotEmpty()) {
    $center = $yaoundeICenters->first();
    $neighborhood = $yaoundeIINeighborhoods->first();

    runTestCase(
        "Yaoundé I center → Yaoundé II delivery ({$neighborhood->name})",
        $center->id,
        $neighborhood->id,
        false,
        $token,
        $baseUrl,
        $totalTests,
        $passedTests,
        $failedTests,
        $customerId
    );
}

// Test 4: Yaoundé III center with Yaoundé IV neighborhood
$yaoundeIIICenters = DistributionCenter::whereHas('neighborhood.municipality', function ($q) {
    $q->where('name', 'Yaoundé III');
})->get();

$yaoundeIVNeighborhoods = Neighborhood::whereHas('municipality', function ($q) {
    $q->where('name', 'Yaoundé IV');
})->get();

if ($yaoundeIIICenters->isNotEmpty() && $yaoundeIVNeighborhoods->isNotEmpty()) {
    $center = $yaoundeIIICenters->first();
    $neighborhood = $yaoundeIVNeighborhoods->first();

    runTestCase(
        "Yaoundé III center → Yaoundé IV delivery ({$neighborhood->name})",
        $center->id,
        $neighborhood->id,
        false,
        $token,
        $baseUrl,
        $totalTests,
        $passedTests,
        $failedTests,
        $customerId
    );
}

// Test 5: Douala I center with Douala II neighborhood
$doualaIINeighborhoods = Neighborhood::whereHas('municipality', function ($q) {
    $q->where('name', 'Douala II');
})->get();

if ($doualaICenters->isNotEmpty() && $doualaIINeighborhoods->isNotEmpty()) {
    $center = $doualaICenters->first();
    $neighborhood = $doualaIINeighborhoods->first();

    runTestCase(
        "Douala I center → Douala II delivery ({$neighborhood->name})",
        $center->id,
        $neighborhood->id,
        false,
        $token,
        $baseUrl,
        $totalTests,
        $passedTests,
        $failedTests,
        $customerId
    );
}

// =============================================================================
// Test Suite 3: Different City (Should FAIL)
// =============================================================================

printHeader('Test Suite 3: Different City (Should FAIL)');

// Test 6: Yaoundé I center with Douala I neighborhood
$doualaINeighborhoods = Neighborhood::whereHas('municipality', function ($q) {
    $q->where('name', 'Douala I');
})->get();

if ($yaoundeICenters->isNotEmpty() && $doualaINeighborhoods->isNotEmpty()) {
    $center = $yaoundeICenters->first();
    $neighborhood = $doualaINeighborhoods->first();

    runTestCase(
        "Yaoundé I center → Douala I delivery ({$neighborhood->name})",
        $center->id,
        $neighborhood->id,
        false,
        $token,
        $baseUrl,
        $totalTests,
        $passedTests,
        $failedTests,
        $customerId
    );
}

// Test 7: Douala I center with Yaoundé I neighborhood
$yaoundeINeighborhoods = Neighborhood::whereHas('municipality', function ($q) {
    $q->where('name', 'Yaoundé I');
})->get();

if ($doualaICenters->isNotEmpty() && $yaoundeINeighborhoods->isNotEmpty()) {
    $center = $doualaICenters->first();
    $neighborhood = $yaoundeINeighborhoods->first();

    runTestCase(
        "Douala I center → Yaoundé I delivery ({$neighborhood->name})",
        $center->id,
        $neighborhood->id,
        false,
        $token,
        $baseUrl,
        $totalTests,
        $passedTests,
        $failedTests,
        $customerId
    );
}

// Test 8: Bamenda I center with Bafoussam I neighborhood
$bamendaICenters = DistributionCenter::whereHas('neighborhood.municipality', function ($q) {
    $q->where('name', 'Bamenda I');
})->get();

$bafoussam_INeighborhoods = Neighborhood::whereHas('municipality', function ($q) {
    $q->where('name', 'Bafoussam I');
})->get();

if ($bamendaICenters->isNotEmpty() && $bafoussam_INeighborhoods->isNotEmpty()) {
    $center = $bamendaICenters->first();
    $neighborhood = $bafoussam_INeighborhoods->first();

    runTestCase(
        "Bamenda I center → Bafoussam I delivery ({$neighborhood->name})",
        $center->id,
        $neighborhood->id,
        false,
        $token,
        $baseUrl,
        $totalTests,
        $passedTests,
        $failedTests,
        $customerId
    );
}

// =============================================================================
// Test Results Summary
// =============================================================================

printHeader('Test Results Summary');

echo "\n{$colors['BLUE']}Geographic Coherence Test Statistics:{$colors['NC']}\n";
echo "  📊 Total Tests: $totalTests\n";
echo "  {$colors['GREEN']}✅ Passed: $passedTests{$colors['NC']}\n";
echo "  {$colors['RED']}❌ Failed: $failedTests{$colors['NC']}\n";

if ($failedTests === 0) {
    printSuccess('All geographic coherence tests passed! 🎉');
    echo "\n";
    echo "{$colors['GREEN']}✅ System correctly validates:{$colors['NC']}\n";
    echo "  • Orders are ACCEPTED when delivery address and center are in the same municipality\n";
    echo "  • Orders are REJECTED when delivery address and center are in different municipalities\n";
    echo "  • Validation works across different cities (Yaoundé, Douala, Bamenda, Bafoussam)\n";
    exit(0);
} else {
    printError('Some tests failed. Please review the errors above.');
    exit(1);
}
