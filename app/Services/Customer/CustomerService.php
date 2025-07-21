<?php

namespace App\Services\Customer;

use App\DTOs\Customer\CreateCustomerDTO;
use App\Events\CustomerCreatedEvent;
use App\Http\Api\Resources\CustomerResource;
use App\Models\Customer;
use App\Models\User;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Services\BaseServiceForEntity;
use App\Services\BaseServiceWithMedia;
use App\Services\User\UserService;
use Illuminate\Database\Eloquent\Model;

class CustomerService extends BaseServiceForEntity
{
    
    /**
     * @var CustomerRepositoryInterface
     */
    public function __construct(
        CustomerRepositoryInterface $repository,
        protected UserService $userService,
    ) {
        parent::__construct($repository);
    }


    protected function getModel(): string
    {
        return Customer::class;
    }

    public function create(array $attributes): Model
    {
        return $this->executeInTransaction(function () use ($attributes) {
            /** @var User $user */
            $user = $this->userService->createWithMedia($attributes);

            /** @var Customer $customer */
            $customer = $this->repository->create([
                'user_id' => $user->id,
                'current_balance' => $attributes['current_balance'] ?? null,
            ]);

            $user->customer()->save($customer);

            CustomerCreatedEvent::dispatch($customer);

            return $customer;
        });
    }
}
