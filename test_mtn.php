#!/usr/bin/env php
<?php

/**
 * MTN MoMo API Test Script
 *
 * Usage: php test_mtn.php
 */

// Colors for terminal output
define('GREEN', "\033[32m");
define('RED', "\033[31m");
define('YELLOW', "\033[33m");
define('BLUE', "\033[34m");
define('CYAN', "\033[36m");
define('RESET', "\033[0m");
define('BOLD', "\033[1m");

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

// Configuration
$config = [
    'base_url' => $env['MTN_MOMO_BASE_URL'] ?? 'https://proxy.momoapi.mtn.com',
    'subscription_key' => $env['MTN_MOMO_SUBSCRIPTION_KEY'] ?? '',
    'api_user' => $env['MTN_MOMO_API_USER'] ?? '',
    'api_key' => $env['MTN_MOMO_API_KEY'] ?? '',
    'target_environment' => $env['MTN_MOMO_TARGET_ENVIRONMENT'] ?? 'mtncameroon',
    'currency' => $env['MTN_MOMO_CURRENCY'] ?? 'XAF',
];

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
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);

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

function formatPhoneNumber($phoneNumber) {
    $cleaned = preg_replace('/\D/', '', $phoneNumber);
    if (str_starts_with($cleaned, '237')) {
        return $cleaned;
    }
    if (str_starts_with($cleaned, '6')) {
        return '237' . $cleaned;
    }
    return '237' . $cleaned;
}

// ============================================
// MTN MoMo API FUNCTIONS
// ============================================

function getAccessToken($config) {
    printStep("1", "Getting access token...");

    $url = $config['base_url'] . '/collection/token/';
    $credentials = base64_encode($config['api_user'] . ':' . $config['api_key']);

    $headers = [
        'Authorization: Basic ' . $credentials,
        'Ocp-Apim-Subscription-Key: ' . $config['subscription_key'],
        'X-Target-Environment: ' . $config['target_environment'],
    ];

    printInfo("URL: $url");
    printInfo("Environment: " . $config['target_environment']);

    $response = httpRequest('POST', $url, $headers);

    if ($response['error']) {
        printError("cURL Error: " . $response['error']);
        return null;
    }

    if ($response['success'] && isset($response['data']['access_token'])) {
        printSuccess("Access token obtained!");
        printInfo("Token: " . substr($response['data']['access_token'], 0, 30) . "...");
        printInfo("Expires in: " . ($response['data']['expires_in'] ?? 'N/A') . " seconds");
        return $response['data']['access_token'];
    }

    printError("Failed to get access token (HTTP " . $response['http_code'] . ")");
    printJson($response['data'] ?? $response['body']);
    return null;
}

function requestToPay($config, $accessToken, $phoneNumber, $amount, $externalId) {
    printStep("2", "Sending payment request (Request to Pay)...");

    $url = $config['base_url'] . '/collection/v1_0/requesttopay';
    $referenceId = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );

    $formattedPhone = formatPhoneNumber($phoneNumber);

    $headers = [
        'Authorization: Bearer ' . $accessToken,
        'X-Reference-Id: ' . $referenceId,
        'X-Target-Environment: ' . $config['target_environment'],
        'Ocp-Apim-Subscription-Key: ' . $config['subscription_key'],
        'Content-Type: application/json',
    ];

    $body = json_encode([
        'amount' => (string) $amount,
        'currency' => $config['currency'],
        'externalId' => $externalId,
        'payer' => [
            'partyIdType' => 'MSISDN',
            'partyId' => $formattedPhone,
        ],
        'payerMessage' => 'Test Paiement Petrolex',
        'payeeNote' => 'Test MTN MoMo',
    ]);

    printInfo("URL: $url");
    printInfo("Reference ID: $referenceId");
    printInfo("Phone: $formattedPhone");
    printInfo("Amount: $amount " . $config['currency']);

    $response = httpRequest('POST', $url, $headers, $body);

    if ($response['error']) {
        printError("cURL Error: " . $response['error']);
        return null;
    }

    // MTN returns 202 Accepted for successful request
    if ($response['http_code'] === 202) {
        printSuccess("Payment request sent!");
        printInfo("Reference ID: $referenceId");
        echo "\n" . BOLD . YELLOW . "  📱 CHECK YOUR PHONE! Enter your MTN MoMo PIN to confirm." . RESET . "\n\n";
        return $referenceId;
    }

    printError("Failed to send payment request (HTTP " . $response['http_code'] . ")");
    printJson($response['data'] ?? $response['body']);
    return null;
}

function checkPaymentStatus($config, $accessToken, $referenceId) {
    $url = $config['base_url'] . '/collection/v1_0/requesttopay/' . $referenceId;

    $headers = [
        'Authorization: Bearer ' . $accessToken,
        'X-Target-Environment: ' . $config['target_environment'],
        'Ocp-Apim-Subscription-Key: ' . $config['subscription_key'],
    ];

    $response = httpRequest('GET', $url, $headers);

    if ($response['success'] && $response['data']) {
        return $response['data'];
    }

    return null;
}

function pollPaymentStatus($config, $accessToken, $referenceId, $maxAttempts = 30, $interval = 5) {
    printStep("3", "Polling payment status (max {$maxAttempts} attempts, every {$interval}s)...");
    echo "\n";

    for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
        $status = checkPaymentStatus($config, $accessToken, $referenceId);

        if ($status) {
            $currentStatus = $status['status'] ?? 'UNKNOWN';
            $timestamp = date('H:i:s');

            $statusColor = match(strtoupper($currentStatus)) {
                'SUCCESSFUL' => GREEN,
                'FAILED', 'REJECTED', 'TIMEOUT', 'EXPIRED' => RED,
                default => YELLOW,
            };

            echo "  [$timestamp] Attempt $attempt/$maxAttempts: " . $statusColor . $currentStatus . RESET;

            if (strtoupper($currentStatus) === 'SUCCESSFUL') {
                echo "\n\n";
                printSuccess("PAYMENT SUCCESSFUL!");
                printInfo("Financial Transaction ID: " . ($status['financialTransactionId'] ?? 'N/A'));
                printInfo("Amount: " . ($status['amount'] ?? 'N/A') . " " . ($status['currency'] ?? ''));
                return $status;
            }

            if (in_array(strtoupper($currentStatus), ['FAILED', 'REJECTED', 'TIMEOUT', 'EXPIRED'])) {
                echo "\n\n";
                printError("PAYMENT FAILED!");
                printInfo("Status: $currentStatus");
                printInfo("Reason: " . ($status['reason'] ?? 'N/A'));
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

printHeader("MTN MoMo API TEST");

echo "Configuration:\n";
printInfo("Base URL: " . $config['base_url']);
printInfo("Environment: " . $config['target_environment']);
printInfo("API User: " . substr($config['api_user'], 0, 10) . "...");
printInfo("Subscription Key: " . substr($config['subscription_key'], 0, 10) . "...");
echo "\n";

// Get phone number
$phoneNumber = prompt("Enter MTN phone number (e.g., 676636794)");

if (empty($phoneNumber)) {
    printError("Phone number is required!");
    exit(1);
}

// Get amount
$amount = prompt("Enter amount in FCFA (default: 10)");
$amount = !empty($amount) ? (int) $amount : 10;

if ($amount < 1) {
    printError("Amount must be at least 1 FCFA!");
    exit(1);
}

// Generate external ID
$externalId = 'TEST' . date('ymdHis') . rand(100, 999);

echo "\n";
printInfo("Phone: " . formatPhoneNumber($phoneNumber));
printInfo("Amount: $amount FCFA");
printInfo("External ID: $externalId");
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

// Step 2: Request to Pay
$referenceId = requestToPay($config, $accessToken, $phoneNumber, $amount, $externalId);
if (!$referenceId) {
    exit(1);
}

// Step 3: Poll for status
$finalStatus = pollPaymentStatus($config, $accessToken, $referenceId);

// Summary
printHeader("TEST SUMMARY");

echo "External ID:   $externalId\n";
echo "Reference ID:  $referenceId\n";
echo "Phone:         " . formatPhoneNumber($phoneNumber) . "\n";
echo "Amount:        $amount FCFA\n";
echo "Final Status:  " . ($finalStatus['status'] ?? 'UNKNOWN') . "\n";

if ($finalStatus && strtoupper($finalStatus['status']) === 'SUCCESSFUL') {
    echo "\n" . GREEN . BOLD . "✓ TEST PASSED - Payment successful!" . RESET . "\n";
} else {
    echo "\n" . YELLOW . BOLD . "⚠ TEST INCOMPLETE - Check status manually" . RESET . "\n";
}

echo "\n";
