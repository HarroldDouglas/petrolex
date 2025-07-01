<?php

namespace App\Http\Controllers\User\DeliveryPerson;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GetDeliveryPersonDetailsController extends Controller
{
    public function __invoke(Request $request, User $user)
    {
        try {
            $user->loadMissing([
                'deliveryPerson' => function ($query) {
                    $query->with([
                        'orders' => function ($orderQuery) {
                            $orderQuery->latest()->take(10)->with('customer.user');
                        },
                    ]);
                },
            ]);

            $deliveryPerson = $user->deliveryPerson;

            if (! $deliveryPerson) {
                return redirect()->route('users.list')->with('error', "The selected user ({$user->full_name}) does not have an associated delivery person profile.");
            }

            $deliveryPersonOrders = $deliveryPerson->orders;

            return view('users.delivery.delivery-details', [
                'user' => $user,
                'deliveryPerson' => $deliveryPerson,
                'deliveryPersonOrders' => $deliveryPersonOrders,
            ]);

        } catch (\Exception $e) {
            Log::error('Error in GetDeliveryPersonDetailsController for user ID '.$user->id.': '.$e->getMessage(), ['exception' => $e]);

            return back()->with('error', 'An unexpected error occurred while fetching delivery person details. Please try again.');
        }
    }
}
