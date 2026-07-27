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
            // Intentionally a no-op ACK. Payment confirmation is NOT trusted from
            // this inbound callback (it is unauthenticated and spoofable). The
            // authoritative status is pulled server-side from the MTN API by
            // App\Jobs\VerifyPaymentStatusJob. We only acknowledge receipt here.
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
