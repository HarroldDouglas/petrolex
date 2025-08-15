<?php

namespace App\Providers;

use App\Events\CustomerCreatedEvent;
use App\Events\DistributionCenterUpdatedEvent;
use App\Events\EmptyBottleReturned;
use App\Events\OrderCreatedEvent;
use App\Events\OrderDeliveredEvent;
use App\Events\UserDeletedEvent;
use App\Events\UserUpdatedEvent;
use App\Listeners\AddOrderItemsToOrderListener;
use App\Listeners\CreateBottleMovementForReturnedBottleListener;
use App\Listeners\LogCustomerCreatedListener;
use App\Listeners\LogDistributionCenterUpdated;
use App\Listeners\LogOrderCreatedListener;
use App\Listeners\LogOrderDelivered;
use App\Listeners\LogUserDeleted;
use App\Listeners\LogUserUpdated;
use App\Listeners\Order\AssignDeliveryPersonToOrderListener;
use App\Listeners\UpdateBottleStatusAndMovementOnOrderDelivered;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        EmptyBottleReturned::class => [
            CreateBottleMovementForReturnedBottleListener::class,
        ],
        OrderDeliveredEvent::class => [
            UpdateBottleStatusAndMovementOnOrderDelivered::class,
            LogOrderDelivered::class,
        ],
        UserUpdatedEvent::class => [
            LogUserUpdated::class,
        ],
        UserDeletedEvent::class => [
            LogUserDeleted::class,
        ],
        DistributionCenterUpdatedEvent::class => [
            LogDistributionCenterUpdated::class,
        ],
        \App\Events\OrderStatusChanged::class => [
            \App\Listeners\Order\SendOrderStatusChangedNotificationToCustomer::class,
            \App\Listeners\Order\SendOrderStatusChangedNotificationToDeliveryPerson::class,
            \App\Listeners\Order\SendOrderStatusChangedNotificationToManager::class,
        ],
        OrderCreatedEvent::class => [
            AddOrderItemsToOrderListener::class,
            LogOrderCreatedListener::class,
            AssignDeliveryPersonToOrderListener::class,
        ],
        CustomerCreatedEvent::class => [
            LogCustomerCreatedListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void {}
}
