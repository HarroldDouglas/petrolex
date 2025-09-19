<?php

namespace App\Services\Customer;

use App\DTOs\Customer\CustomerDeliveryAddressDTO;
use App\DTOs\Order\GetOrdersFilterDTO;
use App\Enums\UserRole;
use App\Events\CustomerCreatedEvent;
use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\User;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Services\Auth\OtpService;
use App\Services\BaseServiceWithMedia;
use App\Services\Shared\Media\MediaServiceInterface;
use App\Services\User\UserService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class CustomerService extends BaseServiceWithMedia
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected BaseRepositoryInterface $repository;

    public function __construct(
        CustomerRepositoryInterface $repository,
        protected UserService $userService,
        protected MediaServiceInterface $mediaService,
        protected OtpService $otpService
    ) {
        parent::__construct($repository, $mediaService);
    }

    protected function getMediaFields(): array
    {
        return ['image'];
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
                'is_active' => false,
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

    public function getOrders(Customer $customer, GetOrdersFilterDTO $filters, int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->getOrdersForCustomer($customer, $filters, $perPage);
    }

    public function createDeliveryAddress(Customer $customer, CustomerDeliveryAddressDTO $dto): CustomerDeliveryAddress
    {
        return $this->repository->createDeliveryAddress(array_merge($dto->toArray(), [
            'customer_id' => $customer->id,
        ]));
    }
}
