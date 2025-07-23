<?php

namespace App\Http\Api\Controllers\Customer;

use App\DTOs\Customer\CreateCustomerDTO;
use App\Http\Api\Responses\OtpResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Services\Customer\CustomerService;

/**
 * Controller for storing a new customer.
 */
class StoreCustomerController extends Controller
{
    public function __construct(protected CustomerService $customerService) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(StoreCustomerRequest $request): OtpResponse
    {
        /**
         * @var CreateCustomerDTO $dto
         */
        $dto = CreateCustomerDTO::from($request->validated());

        /** @var \App\Models\Customer $customer */
        $customer = $this->customerService->create($dto->toArray());

        return OtpResponse::otpSent($customer->user->email);
    }
}
