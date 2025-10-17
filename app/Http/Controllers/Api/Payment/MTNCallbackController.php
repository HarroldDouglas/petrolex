<?php

namespace App\Http\Controllers\Api\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class MTNCallbackController extends Controller
{
    /**
     * Handle MTN Mobile Money callback
     */
    public function handleCallback(Request $request): Response
    {
        Log::info('MTN Mobile Money callback received', $request->all());

        try {
            // TODO: Implement MTN Mobile Money callback logic
            // This should handle the payment verification and update order status

            return response('OK', 200);
        } catch (\Exception $e) {
            Log::error('MTN Mobile Money callback error: '.$e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e,
            ]);

            return response('Error', 500);
        }
    }
}
