<?php

namespace App\Services\Customer;

use App\Enums\UserRole;
use App\Events\CustomerCreatedEvent;
use App\Models\Customer;
use App\Models\User;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Services\Auth\OtpService;
use App\Services\BaseServiceForEntity;
use App\Services\User\UserService;
use Illuminate\Database\Eloquent\Model;

class CustomerService extends BaseServiceForEntity
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected BaseRepositoryInterface $repository;

    public function __construct(
        CustomerRepositoryInterface $repository,
        protected UserService $userService,
        protected OtpService $otpService
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
            $attributes = array_merge($attributes, [
                'role' => UserRole::CUSTOMER(),
            ]);

            /** @var User $user */
            $user = $this->userService->createWithMedia($attributes);

            /** @var Customer $customer */
            $customer = $this->repository->create([
                'user_id' => $user->id,
                'current_balance' => $attributes['current_balance'] ?? null,
            ]);

            $user->customer()->save($customer);

            CustomerCreatedEvent::dispatch($customer);

            $this->otpService->sendOtp($user->email ?? $user->phone_number);

            return $customer;
        });
    }
}
