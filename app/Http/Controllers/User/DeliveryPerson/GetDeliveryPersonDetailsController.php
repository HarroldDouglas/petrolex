<?php

namespace App\Http\Controllers\User\DeliveryPerson;

use App\Http\Controllers\Controller;
use App\Services\User\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GetDeliveryPersonDetailsController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}

    /**
     * Display the details of a specific delivery person.
     *
     * Route: GET /users/{user_id}/delivery/details
     * Name: users.delivery.details
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, int $user_id)
    {
        try {
            $user = $this->userService->find($user_id);
            $user->loadMissing('deliveryPerson');

            $deliveryPerson = $user->deliveryPerson;

            if (! $deliveryPerson) {
                return redirect()->route('users.list')->with('error', "The selected user ({$user->full_name}) does not have an associated delivery person profile.");
            }

            return view('users.delivery-details', [
                'user' => $user,
                'deliveryPerson' => $deliveryPerson,
            ]);

        } catch (\Exception $e) {
            Log::error('Error in GetDeliveryPersonDetailsController for user ID '.$user_id.': '.$e->getMessage(), ['exception' => $e]);

            return back()->with('error', 'An unexpected error occurred while fetching delivery person details. Please try again.');
        }
    }
}
