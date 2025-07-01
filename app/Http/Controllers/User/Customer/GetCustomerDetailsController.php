<?php

namespace App\Http\Controllers\User\Customer;

use App\Http\Controllers\Controller;
use App\Services\User\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GetCustomerDetailsController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}

    /**
     * Display the details of a specific customer.
     *
     * Route: GET /users/{user_id}/customer/details
     * Name: users.customer.details
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, int $user_id)
    {
        try {
            $user = $this->userService->find($user_id);
            $user->loadMissing('customer');

            $customer = $user->customer;

            if (! $customer) {
                return redirect()->route('users.list')->with('error', "The selected user ({$user->full_name}) does not have an associated customer profile.");
            }

            return view('users.customer-details', [
                'user' => $user,
                'customer' => $customer,
            ]);

        } catch (\Exception $e) {
            Log::error('Error in GetCustomerDetailsController for user ID '.$user_id.': '.$e->getMessage(), ['exception' => $e]);

            return back()->with('error', 'An unexpected error occurred while fetching customer details. Please try again.');
        }
    }
}
