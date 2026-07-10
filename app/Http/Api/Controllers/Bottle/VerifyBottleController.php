<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\Bottle;

use App\Http\Api\Responses\Bottle\BottleVerificationResponse;
use App\Http\Controllers\Controller;
use App\Services\Bottle\BottleService;
use Illuminate\Support\Facades\Log;

final class VerifyBottleController extends Controller
{
    public function __construct(
        private readonly BottleService $bottleService
    ) {}

    /**
     * Verify bottle authenticity and status.
     *
     * Route: GET /api/bottles/{barcode}/verify
     * Name: api.bottles.verify
     */
    public function __invoke(string $barcode): BottleVerificationResponse
    {
        Log::info('[MobileScan] verify-bottle reçu', [
            'endpoint' => 'GET /api/bottles/{barcode}/verify',
            'barcode' => $barcode,
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $result = $this->bottleService->checkBottleStatusByBarcode($barcode);

        return BottleVerificationResponse::verified($result);
    }
}
