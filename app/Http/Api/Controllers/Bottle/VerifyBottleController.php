<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\Bottle;

use App\Http\Controllers\Controller;
use App\Http\Api\Resources\Bottle\BottleVerificationResource;
use App\Http\Api\Responses\Bottle\BottleVerificationResponse; // Added
use App\Services\Bottle\BottleService;

final class VerifyBottleController extends Controller
{
    public function __construct(
        private readonly BottleService $bottleService
    ) {}

    public function __invoke(string $barcode): BottleVerificationResponse
    {
        $result = $this->bottleService->checkBottleStatusByBarcode($barcode);

        return BottleVerificationResponse::verified($result);
    }
}
