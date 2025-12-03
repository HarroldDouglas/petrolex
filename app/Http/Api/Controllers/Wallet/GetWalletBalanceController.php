<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\Wallet;

use App\Http\Controllers\Controller;
use App\Services\Wallet\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class GetWalletBalanceController extends Controller
{
    public function __construct(
        private WalletService $walletService
    ) {}

    /**
     * Get the current wallet balance for the authenticated customer.
     *
     * Route: GET /api/my/wallet
     * Name: api.my.wallet
     */
    public function __invoke(Request $request): JsonResponse
    {
        $customer = $request->user()->customer;

        if (! $customer) {
            return response()->json([
                '_metadata' => [
                    'success' => false,
                    'message' => __('api.customer_not_found'),
                ],
            ], 404);
        }

        $balance = $this->walletService->getBalance($customer);

        return response()->json([
            '_metadata' => [
                'success' => true,
                'message' => __('api.success'),
            ],
            'data' => [
                'current_balance' => $balance,
                'formatted_balance' => number_format($balance, 0, ',', ' ').' FCFA',
            ],
        ]);
    }
}
