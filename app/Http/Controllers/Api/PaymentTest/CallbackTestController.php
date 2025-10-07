<?php

namespace App\Http\Controllers\Api\PaymentTest;

use App\Http\Controllers\Controller;
use App\Services\PaymentTest\CallbackNormalizationService;
use App\Services\PaymentTest\CallbackStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

class CallbackTestController extends Controller
{
    public function __construct(
        private readonly CallbackNormalizationService $normalizationService,
        private readonly CallbackStorageService $storageService
    ) {}

    /**
     * Handle payment provider callbacks
     *
     * @param  Request  $request  The HTTP request containing callback data
     * @param  string  $provider  The payment provider (mtn, orange, etc.)
     * @return JsonResponse Success or error response
     */
    public function handleCallback(Request $request, string $provider): JsonResponse
    {
        if (! $this->isValidProvider($provider)) {
            return $this->errorResponse(
                'Invalid provider for callback',
                Response::HTTP_BAD_REQUEST,
                ['provider' => $provider]
            );
        }

        try {
            $this->logIncomingCallback($provider, $request);

            $result = $this->processCallback($provider, $request->all());

            return $this->successResponse(
                ucfirst($provider).' callback processed successfully',
                $result
            );

        } catch (Throwable $e) {
            $this->logCallbackError($provider, $e, $request->all());

            return $this->errorResponse(
                ucfirst($provider).' callback processing failed',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['error' => $e->getMessage()]
            );
        }
    }

    private function isValidProvider(string $provider): bool
    {
        $supportedProviders = array_keys(config('payment.providers', []));

        return in_array($provider, $supportedProviders);
    }

    private function processCallback(string $provider, array $callbackData): array
    {
        $normalizedData = $this->normalizationService->normalize($provider, $callbackData);

        return $this->storageService->store($provider, $callbackData, $normalizedData);
    }

    private function logIncomingCallback(string $provider, Request $request): void
    {
        Log::info("📞 {$provider} callback received", [
            'provider' => strtoupper($provider),
            'payload' => $request->all(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    private function logCallbackError(string $provider, Throwable $exception, array $payload): void
    {
        Log::error("❌ {$provider} callback processing failed", [
            'provider' => $provider,
            'error' => $exception->getMessage(),
            'payload' => $payload,
            'trace' => $exception->getTraceAsString(),
        ]);
    }

    private function successResponse(string $message, array $data): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ]);
    }

    private function errorResponse(string $message, int $status, array $additional = []): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        return response()->json(array_merge($response, $additional), $status);
    }
}
