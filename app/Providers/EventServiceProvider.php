<?php

namespace App\Providers;

use App\Events\CustomerCreatedEvent;
use App\Events\DistributionCenterUpdatedEvent;
use App\Events\EmptyBottleReturnedEvent;
use App\Events\OrderCreatedEvent;
use App\Events\OrderDeliveredEvent;
use App\Events\OrderStatusChanged;
use App\Events\PasswordUpdatedEvent;
use App\Events\Role\RoleDeletingEvent;
use App\Events\Role\RolePermissionUpdatedEvent;
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
use App\Listeners\Order\DecrementStockOnPaymentListener;
use App\Listeners\Order\ReleaseBottlesOnOrderCancellation;
use App\Listeners\Order\RestoreStockOnCancellationListener;
use App\Listeners\Order\SendOrderPaidNotification;
use App\Listeners\Order\SendOrderStatusChangedNotification;
use App\Listeners\Role\AssignPermissionsToRoleListener;
use App\Listeners\Role\DetachPermissionsFromDeletingRoleListener;
use App\Listeners\SendPasswordUpdatedNotification;
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
        \App\Events\UserCreatedEvent::class => [
            \App\Listeners\CreateUserRelatedEntitiesListener::class,
        ],
        EmptyBottleReturnedEvent::class => [
            CreateBottleMovementForReturnedBottleListener::class,
        ],
        PasswordUpdatedEvent::class => [
            SendPasswordUpdatedNotification::class,
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
        OrderCreatedEvent::class => [
            AddOrderItemsToOrderListener::class,
            LogOrderCreatedListener::class,
            AssignDeliveryPersonToOrderListener::class,
        ],
        CustomerCreatedEvent::class => [
            LogCustomerCreatedListener::class,
        ],
        RolePermissionUpdatedEvent::class => [
            AssignPermissionsToRoleListener::class,
        ],
        RoleDeletingEvent::class => [
            DetachPermissionsFromDeletingRoleListener::class,
        ],
        OrderStatusChanged::class => [
            SendOrderPaidNotification::class,
            SendOrderStatusChangedNotification::class,
            DecrementStockOnPaymentListener::class,
            RestoreStockOnCancellationListener::class,
            ReleaseBottlesOnOrderCancellation::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void {}
}
