<?php

namespace App\Services\Customer;

use App\Models\Customer;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Services\BaseService; // Ensure BaseService exists in the specified namespace
use App\Services\BaseServiceWithMedia;
use App\Services\Shared\Media\MediaServiceInterface;

class CustomerService extends BaseServiceWithMedia
{
    public function __construct(
        protected CustomerRepositoryInterface $customerRepository,
        protected MediaServiceInterface $mediaService,
    ) {
        parent::__construct($customerRepository, $mediaService);
    }

    /**
     * Get the media fields for the customer service.
     *
     * @return array The list of media fields.
     */
    public function getMediaFields(): array
    {
        return ['picture'];
    }

    protected function getModel(): string
    {
        return Customer::class;
    }
}
