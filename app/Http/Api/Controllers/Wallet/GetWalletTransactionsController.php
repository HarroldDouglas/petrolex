<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\Wallet;

use App\Http\Api\Resources\WalletTransactionResource;
use App\Http\Controllers\Controller;
use App\Services\Wallet\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class GetWalletTransactionsController extends Controller
{
    public function __construct(
        private WalletService $walletService
    ) {}

    /**
     * Get the wallet transaction history for the authenticated customer.
     *
     * Route: GET /api/my/wallet/transactions
     * Name: api.my.wallet.transactions
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

        $perPage = $request->input('per_page', 15);
        $transactions = $this->walletService->getTransactionHistory($customer, $perPage);

        return response()->json([
            '_metadata' => [
                'success' => true,
                'message' => __('api.success'),
            ],
            'data' => WalletTransactionResource::collection($transactions),
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }
}
