<?php

namespace App\Http\Api\Controllers\Customer;

use App\DTOs\Customer\CreateCustomerDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Api\Resources\CustomerResource;
use App\Http\Api\Responses\Customer\CustomerResponse;
use Illuminate\Http\JsonResponse;
use App\Services\Customer\CustomerService;

class StoreCustomerController extends Controller
{
   public function __construct(protected CustomerService $customerService) {}

    public function __invoke(StoreCustomerRequest $request): CustomerResponse
    {
        /**
         * @var CreateCustomerDTO $dto
         */
        $dto = CreateCustomerDTO::from($request->validated());

        $customer = $this->customerService->create($dto->toArray());

        return CustomerResponse::single($customer);
    }
}
