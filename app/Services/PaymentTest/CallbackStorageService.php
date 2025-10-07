<?php

namespace App\Services\PaymentTest;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CallbackStorageService
{
    private const CACHE_KEY = 'payment_callbacks';
    private const MAX_CALLBACKS = 100;
    private const CACHE_TTL_HOURS = 2;

    /**
     * Store callback data in cache and logs
     */
    public function store(string $provider, array $rawData, array $normalizedData): array
    {
        $callbackInfo = $this->buildCallbackInfo($provider, $rawData, $normalizedData);

        $this->cacheCallback($callbackInfo);
        $this->logCallback($provider, $callbackInfo);

        return $callbackInfo;
    }

    /**
     * Build callback information structure
     */
    private function buildCallbackInfo(string $provider, array $rawData, array $normalizedData): array
    {
        return [
            'provider' => strtoupper($provider),
            'timestamp' => now()->toISOString(),
            'raw_data' => $rawData,
            'normalized_data' => $normalizedData,
            'level' => $this->getLogLevelFromStatus($normalizedData['status']),
            'message' => $this->buildMessage($provider, $normalizedData['status']),
            'details' => $normalizedData,
            'production_environment' => app()->environment('production'),
        ];
    }

    /**
     * Cache the callback information
     */
    private function cacheCallback(array $callbackInfo): void
    {
        $callbacks = Cache::get(self::CACHE_KEY, []);
        $callbacks[] = $callbackInfo;

        if (count($callbacks) > self::MAX_CALLBACKS) {
            $callbacks = array_slice($callbacks, -self::MAX_CALLBACKS);
        }

        Cache::put(self::CACHE_KEY, $callbacks, now()->addHours(self::CACHE_TTL_HOURS));
    }

    /**
     * Log the callback information
     */
    private function logCallback(string $provider, array $callbackInfo): void
    {
        Log::info("✅ {$provider} callback processed and stored", $callbackInfo);
    }

    /**
     * Build status message
     */
    private function buildMessage(string $provider, string $status): string
    {
        return "{$provider} callback received - Status: {$status}";
    }

    /**
     * Get log level based on payment status
     */
    private function getLogLevelFromStatus(string $status): string
    {
        $upperStatus = strtoupper($status);

        if (in_array($upperStatus, config('payment.status_mappings.success_statuses', []))) {
            return 'success';
        }

        if (in_array($upperStatus, config('payment.status_mappings.failed_statuses', []))) {
            return 'error';
        }

        if (in_array($upperStatus, config('payment.status_mappings.pending_statuses', []))) {
            return 'warning';
        }

        return 'info';
    }

    /**
     * Get recent callbacks from cache
     */
    public function getRecentCallbacks(): array
    {
        return Cache::get(self::CACHE_KEY, []);
    }
}
