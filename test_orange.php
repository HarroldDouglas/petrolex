#!/usr/bin/env php
<?php

/**
 * Orange Money API Test Script
 *
 * Usage: php test_orange.php
 *
 * This script tests the full Orange Money payment flow:
 * 1. Get access token
 * 2. Initialize payment (get payToken)
 * 3. Launch payment (send USSD push)
 * 4. Poll for payment status
 */

// ============================================
// CONFIGURATION (from .env based on argument)
// ============================================

// Load .env file
$envFile = __DIR__ . '/.env';
$env = [];
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            [$key, $value] = explode('=', $line, 2);
            $env[trim($key)] = trim($value, '"\'');
        }
    }
}

// Check argument: sandbox (default) or prod
$mode = $argv[1] ?? 'sandbox';
$mode = strtolower($mode);

if (!in_array($mode, ['sandbox', 'prod'])) {
    echo RED . "Usage: php test_orange.php [sandbox|prod]\n" . RESET;
    exit(1);
}

// Configuration based on mode
if ($mode === 'prod') {
    $config = [
        'base_url' => $env['OM_BASE_URL'] ?? 'https://apiis.orange.cm',
        'client_id' => $env['OM_CLIENT_ID'] ?? '',
        'client_secret' => $env['OM_CLIENT_SECRET'] ?? '',
        'api_username' => $env['OM_API_USERNAME'] ?? '',
        'api_password' => $env['OM_API_PASSWORD'] ?? '',
        'channel_user_msisdn' => $env['OM_CHANNEL_USER_MSISDN'] ?? '',
        'pin' => $env['OM_PIN'] ?? '',
    ];
    $modeName = 'PRODUCTION';
} else {
    // Sandbox hardcoded values (fallback if .env has prod config)
    $config = [
        'base_url' => 'https://api-s1.orange.cm',
        'client_id' => '5xpOluguHcEp6XGLZue3JQII2tsa',
        'client_secret' => 'O_6smU_H1AOrRAnQdG72hX5m8I8a',
        'api_username' => 'OMSANDBOXAPI',
        'api_password' => 'OMS@NDBOX@PI',
        'channel_user_msisdn' => '691301143',
        'pin' => '2222',
    ];
    $modeName = 'SANDBOX';
}

// Colors for terminal output
define('GREEN', "\033[32m");
define('RED', "\033[31m");
define('YELLOW', "\033[33m");
define('BLUE', "\033[34m");
define('CYAN', "\033[36m");
define('RESET', "\033[0m");
define('BOLD', "\033[1m");

// ============================================
// HELPER FUNCTIONS
// ============================================

function printHeader($text) {
    echo "\n" . BOLD . CYAN . "═══════════════════════════════════════════════════════════" . RESET . "\n";
    echo BOLD . CYAN . "  $text" . RESET . "\n";
    echo BOLD . CYAN . "═══════════════════════════════════════════════════════════" . RESET . "\n\n";
}

function printStep($step, $text) {
    echo BOLD . BLUE . "[$step] " . RESET . "$text\n";
}

function printSuccess($text) {
    echo GREEN . "  ✓ $text" . RESET . "\n";
}

function printError($text) {
    echo RED . "  ✗ $text" . RESET . "\n";
}

function printInfo($text) {
    echo YELLOW . "  → $text" . RESET . "\n";
}

function printJson($data) {
    echo CYAN . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . RESET . "\n";
}

function prompt($question) {
    echo BOLD . "$question: " . RESET;
    return trim(fgets(STDIN));
}

function httpRequest($method, $url, $headers = [], $body = null) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($body) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'http_code' => $httpCode,
        'body' => $response,
        'data' => json_decode($response, true),
        'error' => $error,
    ];
}

// ============================================
// ORANGE MONEY API FUNCTIONS
// ============================================

function getAccessToken($config) {
    printStep("1", "Getting access token...");

    $url = $config['base_url'] . '/token';
    $credentials = base64_encode($config['client_id'] . ':' . $config['client_secret']);

    $headers = [
        'Authorization: Basic ' . $credentials,
        'Content-Type: application/x-www-form-urlencoded',
    ];

    $response = httpRequest('POST', $url, $headers, 'grant_type=client_credentials');

    if ($response['success'] && isset($response['data']['access_token'])) {
        printSuccess("Access token obtained!");
        printInfo("Token: " . substr($response['data']['access_token'], 0, 20) . "...");
        printInfo("Expires in: " . $response['data']['expires_in'] . " seconds");
        return $response['data']['access_token'];
    }

    printError("Failed to get access token");
    printJson($response);
    return null;
}

function initializePayment($config, $accessToken, $orderId, $amount) {
    printStep("2", "Initializing payment...");

    $url = $config['base_url'] . '/omcoreapis/1.0.2/mp/init';
    $xAuthToken = base64_encode($config['api_username'] . ':' . $config['api_password']);

    $headers = [
        'Authorization: Bearer ' . $accessToken,
        'X-AUTH-TOKEN: ' . $xAuthToken,
        'Content-Type: application/json',
        'Accept: application/json',
    ];

    $body = json_encode([
        'amount' => (string) $amount,
        'currency' => 'XAF',
        'orderId' => $orderId,
        'description' => 'Test Paiement Petrolex',
    ]);

    $response = httpRequest('POST', $url, $headers, $body);

    if ($response['success'] && isset($response['data']['data']['payToken'])) {
        $payToken = $response['data']['data']['payToken'];
        printSuccess("Payment initialized!");
        printInfo("payToken: $payToken");
        return $payToken;
    }

    printError("Failed to initialize payment");
    printJson($response['data'] ?? $response);
    return null;
}

function launchPayment($config, $accessToken, $payToken, $phoneNumber, $orderId, $amount) {
    printStep("3", "Launching payment (sending USSD push)...");

    $url = $config['base_url'] . '/omcoreapis/1.0.2/mp/pay';
    $xAuthToken = base64_encode($config['api_username'] . ':' . $config['api_password']);

    $headers = [
        'Authorization: Bearer ' . $accessToken,
        'X-AUTH-TOKEN: ' . $xAuthToken,
        'Content-Type: application/json',
        'Accept: application/json',
    ];

    $body = json_encode([
        'notifUrl' => 'https://petrolex.test/api/callback/orange',
        'channelUserMsisdn' => $config['channel_user_msisdn'],
        'amount' => (string) $amount,
        'subscriberMsisdn' => $phoneNumber,
        'pin' => $config['pin'],
        'orderId' => $orderId,
        'description' => 'Test Paiement Petrolex',
        'payToken' => $payToken,
    ]);

    $response = httpRequest('POST', $url, $headers, $body);

    if ($response['success'] && isset($response['data']['data'])) {
        $data = $response['data']['data'];
        printSuccess("Payment launched!");
        printInfo("Status: " . ($data['status'] ?? 'N/A'));
        printInfo("Transaction ID: " . ($data['txnid'] ?? 'N/A'));
        printInfo("Message: " . ($response['data']['message'] ?? 'N/A'));
        echo "\n" . BOLD . YELLOW . "  📱 CHECK YOUR PHONE! Enter your Orange Money PIN to confirm." . RESET . "\n\n";
        return $data;
    }

    printError("Failed to launch payment");
    printJson($response['data'] ?? $response);
    return null;
}

function checkPaymentStatus($config, $accessToken, $payToken) {
    $url = $config['base_url'] . '/omcoreapis/1.0.2/mp/paymentstatus/' . $payToken;
    $xAuthToken = base64_encode($config['api_username'] . ':' . $config['api_password']);

    $headers = [
        'Authorization: Bearer ' . $accessToken,
        'X-AUTH-TOKEN: ' . $xAuthToken,
        'Content-Type: application/json',
        'Accept: application/json',
    ];

    $response = httpRequest('GET', $url, $headers);

    if ($response['success'] && isset($response['data']['data'])) {
        return $response['data']['data'];
    }

    return null;
}

function pollPaymentStatus($config, $accessToken, $payToken, $maxAttempts = 30, $interval = 5) {
    printStep("4", "Polling payment status (max {$maxAttempts} attempts, every {$interval}s)...");
    echo "\n";

    for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
        $status = checkPaymentStatus($config, $accessToken, $payToken);

        if ($status) {
            $currentStatus = $status['status'] ?? 'UNKNOWN';
            $timestamp = date('H:i:s');

            // Display status with color
            $statusColor = match(strtoupper($currentStatus)) {
                'SUCCESSFULL', 'SUCCESS' => GREEN,
                'FAILED', 'EXPIRED' => RED,
                default => YELLOW,
            };

            echo "  [$timestamp] Attempt $attempt/$maxAttempts: " . $statusColor . $currentStatus . RESET;

            if (strtoupper($currentStatus) === 'SUCCESSFULL' || strtoupper($currentStatus) === 'SUCCESS') {
                echo "\n\n";
                printSuccess("PAYMENT SUCCESSFUL!");
                printInfo("Transaction ID: " . ($status['txnid'] ?? 'N/A'));
                printInfo("Amount: " . ($status['amount'] ?? 'N/A') . " FCFA");
                printInfo("Confirmation: " . ($status['confirmtxnmessage'] ?? 'N/A'));
                return $status;
            }

            if (strtoupper($currentStatus) === 'FAILED' || strtoupper($currentStatus) === 'EXPIRED') {
                echo "\n\n";
                printError("PAYMENT FAILED!");
                printInfo("Status: $currentStatus");
                return $status;
            }

            echo " (waiting...)\n";
        } else {
            echo "  [" . date('H:i:s') . "] Attempt $attempt/$maxAttempts: " . RED . "Error checking status" . RESET . "\n";
        }

        if ($attempt < $maxAttempts) {
            sleep($interval);
        }
    }

    echo "\n";
    printError("Timeout - Max attempts reached. Payment may still be pending.");
    return null;
}

// ============================================
// MAIN SCRIPT
// ============================================

printHeader("ORANGE MONEY API TEST [$modeName]");

echo "This script will test the full Orange Money payment flow.\n";
echo "A USSD push will be sent to your phone.\n\n";

// Get phone number
$phoneNumber = prompt("Enter customer phone number (e.g., 655332183)");

if (empty($phoneNumber)) {
    printError("Phone number is required!");
    exit(1);
}

// Clean phone number
$phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
if (strlen($phoneNumber) === 12 && str_starts_with($phoneNumber, '237')) {
    $phoneNumber = substr($phoneNumber, 3);
}

// Get amount
$amount = prompt("Enter amount in FCFA (default: 100)");
$amount = !empty($amount) ? (int) $amount : 100;

if ($amount < 1) {
    printError("Amount must be at least 1 FCFA!");
    exit(1);
}

// Generate order ID (max 20 characters)
$orderId = 'T' . date('ymdHis') . rand(100, 999);

echo "\n";
printInfo("Phone: $phoneNumber");
printInfo("Amount: $amount FCFA");
printInfo("Order ID: $orderId");
echo "\n";

$confirm = prompt("Proceed with payment? (y/n)");
if (strtolower($confirm) !== 'y') {
    echo "Cancelled.\n";
    exit(0);
}

echo "\n";

// Step 1: Get access token
$accessToken = getAccessToken($config);
if (!$accessToken) {
    exit(1);
}

echo "\n";

// Step 2: Initialize payment
$payToken = initializePayment($config, $accessToken, $orderId, $amount);
if (!$payToken) {
    exit(1);
}

echo "\n";

// Step 3: Launch payment
$payResult = launchPayment($config, $accessToken, $payToken, $phoneNumber, $orderId, $amount);
if (!$payResult) {
    exit(1);
}

// Step 4: Poll for status
$finalStatus = pollPaymentStatus($config, $accessToken, $payToken);

// Summary
printHeader("TEST SUMMARY");

echo "Order ID:     $orderId\n";
echo "Pay Token:    $payToken\n";
echo "Phone:        $phoneNumber\n";
echo "Amount:       $amount FCFA\n";
echo "Final Status: " . ($finalStatus['status'] ?? 'UNKNOWN') . "\n";

if ($finalStatus && (strtoupper($finalStatus['status']) === 'SUCCESSFULL' || strtoupper($finalStatus['status']) === 'SUCCESS')) {
    echo "\n" . GREEN . BOLD . "✓ TEST PASSED - Payment successful!" . RESET . "\n";
} else {
    echo "\n" . YELLOW . BOLD . "⚠ TEST INCOMPLETE - Check status manually" . RESET . "\n";
}

echo "\n" . CYAN . "To check status manually, run:" . RESET . "\n";
echo "curl -s -X GET \"https://api-s1.orange.cm/omcoreapis/1.0.2/mp/paymentstatus/$payToken\" \\\n";
echo "  -H \"Authorization: Bearer $accessToken\" \\\n";
echo "  -H \"X-AUTH-TOKEN: " . base64_encode($config['api_username'] . ':' . $config['api_password']) . "\" | jq .\n\n";
