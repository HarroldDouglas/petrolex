<?php

namespace App\Http\Controllers\Api\Payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\OrangeCallbackRequest;
use App\Services\PaymentTest\CallbackStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

class OrangeCallbackController extends Controller
{
    public function __construct(
        private readonly CallbackStorageService $storageService
    ) {}

    /**
     * Handle Orange Money callbacks
     *
     * @param  OrangeCallbackRequest  $request  The HTTP request containing Orange callback data
     * @return JsonResponse Success or error response
     */
    public function handleCallback(OrangeCallbackRequest $request): JsonResponse
    {
        try {
            $this->logIncomingCallback($request);

            $result = $this->processCallback($request->validated());

            return $this->successResponse(
                'Orange callback processed successfully',
                $result
            );

        } catch (Throwable $e) {
            $this->logCallbackError($e, $request->validated());

            return $this->errorResponse(
                'Orange callback processing failed',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['error' => $e->getMessage()]
            );
        }
    }

    private function processCallback(array $callbackData): array
    {
        $normalizedData = $this->normalizeOrangeCallback($callbackData);

        return $this->storageService->store('orange', $callbackData, $normalizedData);
    }

    /**
     * Normalize Orange Money callback data
     */
    private function normalizeOrangeCallback(array $data): array
    {
        return [
            'transaction_id' => $data['txnid'] ?? 'N/A',
            'external_id' => $data['payToken'] ?? 'N/A',
            'reference_id' => $data['payToken'] ?? 'N/A',
            'status' => $data['status'] ?? 'UNKNOWN',
            'amount' => 'N/A',
            'currency' => config('payment.defaults.currency', 'XAF'),
            'payer_phone' => 'N/A',
            'payer_type' => 'MSISDN',
            'reason' => $data['message'] ?? null,
            'payer_message' => null,
            'payee_note' => null,
            'provider_specific' => [
                'payToken' => $data['payToken'] ?? null,
                'txnid' => $data['txnid'] ?? null,
                'message' => $data['message'] ?? null,
            ],
        ];
    }

    private function logIncomingCallback(OrangeCallbackRequest $request): void
    {
        Log::info('📞 Orange callback received', [
            'provider' => 'ORANGE',
            'payload' => $request->validated(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    private function logCallbackError(Throwable $exception, array $payload): void
    {
        Log::error('❌ Orange callback processing failed', [
            'provider' => 'ORANGE',
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
