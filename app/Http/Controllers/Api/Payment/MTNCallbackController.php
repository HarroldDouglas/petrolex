<?php

namespace App\Http\Controllers\Api\Payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\MTNCallbackRequest;
use App\Services\PaymentTest\CallbackStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

class MTNCallbackController extends Controller
{
    public function __construct(
        private readonly CallbackStorageService $storageService
    ) {}

    /**
     * Handle MTN Mobile Money callbacks
     *
     * @param  MTNCallbackRequest  $request  The HTTP request containing MTN callback data
     * @return JsonResponse Success or error response
     */
    public function handleCallback(MTNCallbackRequest $request): JsonResponse
    {
        try {
            $this->logIncomingCallback($request);

            $result = $this->processCallback($request->validated());

            return $this->successResponse(
                'MTN callback processed successfully',
                $result
            );

        } catch (Throwable $e) {
            $this->logCallbackError($e, $request->validated());

            return $this->errorResponse(
                'MTN callback processing failed',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['error' => $e->getMessage()]
            );
        }
    }

    private function processCallback(array $callbackData): array
    {
        $normalizedData = $this->normalizeMTNCallback($callbackData);

        return $this->storageService->store('mtn', $callbackData, $normalizedData);
    }

    /**
     * Normalize MTN Mobile Money callback data
     */
    private function normalizeMTNCallback(array $data): array
    {
        return [
            'transaction_id' => $data['financialTransactionId'] ?? 'N/A',
            'external_id' => $data['externalId'] ?? 'N/A',
            'reference_id' => $data['externalId'] ?? 'N/A',
            'status' => $data['status'] ?? 'UNKNOWN',
            'amount' => $data['amount'] ?? '0',
            'currency' => $data['currency'] ?? config('payment.defaults.currency', 'XAF'),
            'payer_phone' => $data['payer']['partyId'] ?? 'N/A',
            'payer_type' => $data['payer']['partyIdType'] ?? 'N/A',
            'reason' => $data['reason'] ?? null,
            'payer_message' => $data['payerMessage'] ?? null,
            'payee_note' => $data['payeeNote'] ?? null,
            'provider_specific' => [
                'financialTransactionId' => $data['financialTransactionId'] ?? null,
                'payer' => $data['payer'] ?? null,
            ],
        ];
    }

    private function logIncomingCallback(MTNCallbackRequest $request): void
    {
        Log::info('📞 MTN callback received', [
            'provider' => 'MTN',
            'payload' => $request->validated(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    private function logCallbackError(Throwable $exception, array $payload): void
    {
        Log::error('❌ MTN callback processing failed', [
            'provider' => 'MTN',
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
