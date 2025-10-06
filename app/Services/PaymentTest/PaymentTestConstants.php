<?php

namespace App\Services\PaymentTest;

/**
 * Payment Test Constants
 * 
 * Centralized constants for payment testing system
 */
class PaymentTestConstants
{
    // Payment Providers
    public const PROVIDER_MTN = 'mtn';
    public const PROVIDER_ORANGE = 'orange';
    
    // Test Modes
    public const MODE_SANDBOX = 'sandbox';
    public const MODE_LIVE = 'live';
    
    // Payment Status
    public const STATUS_PENDING = 'PENDING';
    public const STATUS_SUCCESSFUL = 'SUCCESSFUL';
    public const STATUS_FAILED = 'FAILED';
    public const STATUS_CANCELLED = 'CANCELLED';
    public const STATUS_EXPIRED = 'EXPIRED';
    public const STATUS_TIMEOUT = 'TIMEOUT';
    
    // Test Scenarios
    public const SCENARIO_SUCCESS = 'SUCCESS';
    public const SCENARIO_FAILED = 'FAILED';
    public const SCENARIO_PENDING = 'PENDING';
    public const SCENARIO_DEFAULT_SUCCESS = 'DEFAULT_SUCCESS';
    
    // Supported Providers List
    public const SUPPORTED_PROVIDERS = [
        self::PROVIDER_MTN,
        self::PROVIDER_ORANGE
    ];
    
    // Supported Test Modes List
    public const SUPPORTED_MODES = [
        self::MODE_SANDBOX,
        self::MODE_LIVE
    ];
    
    // Payment Status List
    public const ALL_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_SUCCESSFUL,
        self::STATUS_FAILED,
        self::STATUS_CANCELLED,
        self::STATUS_EXPIRED,
        self::STATUS_TIMEOUT
    ];
    
    // MTN Test Numbers
    public const MTN_SUCCESS_NUMBERS = ['677000001', '237677000001'];
    public const MTN_FAILED_NUMBERS = ['677000002', '237677000002'];
    public const MTN_PENDING_NUMBERS = ['677000003', '237677000003'];
    
    // Orange Test Numbers
    public const ORANGE_SUCCESS_NUMBERS = ['655000001', '237655000001'];
    public const ORANGE_FAILED_NUMBERS = ['655000002', '237655000002'];
    public const ORANGE_PENDING_NUMBERS = ['655000003', '237655000003'];
    
    // Validation Rules
    public const MIN_AMOUNT = 10;
    public const MAX_AMOUNT = 1000000;
    public const CURRENCY = 'XAF';
    
    /**
     * Check if provider is supported
     */
    public static function isValidProvider(string $provider): bool
    {
        return in_array($provider, self::SUPPORTED_PROVIDERS);
    }
    
    /**
     * Check if test mode is supported
     */
    public static function isValidMode(string $mode): bool
    {
        return in_array($mode, self::SUPPORTED_MODES);
    }
    
    /**
     * Get provider display name
     */
    public static function getProviderDisplayName(string $provider): string
    {
        return match($provider) {
            self::PROVIDER_MTN => 'MTN Money',
            self::PROVIDER_ORANGE => 'Orange Money',
            default => strtoupper($provider)
        };
    }
}