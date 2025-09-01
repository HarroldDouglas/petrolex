<?php

namespace Tests\Feature\Services\Shared\Notification;

use App\Enums\NotificationType;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\DistributionCenter;
use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderNotification;
use App\Services\Shared\Notification\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    private NotificationService $notificationService;
    private User $user;
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        // Create all necessary roles
        foreach (UserRole::cases() as $role) {
            Role::create(['name' => $role->value]);
        }

        $this->notificationService = $this->app->make(NotificationService::class);
        $this->user = User::factory()->create();

        // Create order with required dependencies
        $customer = Customer::factory()->create();
        $distributionCenter = DistributionCenter::factory()->create();
        $this->order = Order::factory()->create([
            'customer_id' => $customer->id,
            'distribution_center_id' => $distributionCenter->id,
        ]);
    }

    public function test_it_can_create_order_notification_for_user(): void
    {
        Notification::fake();

        $this->notificationService->createOrderNotification(
            $this->order,
            NotificationType::ORDER_CREATED(),
            $this->user
        );

        Notification::assertSentTo(
            $this->user,
            OrderNotification::class,
            function ($notification) {
                return $notification->order->id === $this->order->id &&
                       $notification->type->equals(NotificationType::ORDER_CREATED());
            }
        );
    }

    public function test_it_can_create_order_notification_for_multiple_users(): void
    {
        Notification::fake();

        $users = [
            User::factory()->create(),
            User::factory()->create(),
            User::factory()->create(),
        ];

        $this->notificationService->createOrderNotificationForUsers(
            $this->order,
            NotificationType::ORDER_CONFIRMED(),
            $users
        );

        foreach ($users as $user) {
            Notification::assertSentTo(
                $user,
                OrderNotification::class,
                function ($notification) {
                    return $notification->order->id === $this->order->id &&
                           $notification->type->equals(NotificationType::ORDER_CONFIRMED());
                }
            );
        }
    }

    public function test_it_can_create_order_notification_for_manager(): void
    {
        Notification::fake();

        $manager = User::factory()->create();
        $manager->assignRole(UserRole::CENTER_MANAGER()->value);

        $distributionCenter = DistributionCenter::factory()->create();
        // Associate manager with distribution center through pivot table
        $manager->accessibleDistributionCenters()->attach($distributionCenter->id, [
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $customer = Customer::factory()->create();
        $order = Order::factory()->create([
            'distribution_center_id' => $distributionCenter->id,
            'customer_id' => $customer->id,
        ]);

        $this->notificationService->createOrderNotificationForManager(
            $order,
            NotificationType::ORDER_DELIVERED()
        );

        Notification::assertSentTo(
            $manager,
            OrderNotification::class,
            function ($notification) use ($order) {
                return $notification->order->id === $order->id &&
                       $notification->type->equals(NotificationType::ORDER_DELIVERED());
            }
        );
    }

    public function test_it_handles_missing_manager_gracefully(): void
    {
        Notification::fake();

        $distributionCenter = DistributionCenter::factory()->create();
        $customer = Customer::factory()->create();
        $order = Order::factory()->create([
            'distribution_center_id' => $distributionCenter->id,
            'customer_id' => $customer->id,
        ]);

        $this->notificationService->createOrderNotificationForManager(
            $order,
            NotificationType::ORDER_DELIVERED()
        );

        Notification::assertNothingSent();
    }

    public function test_it_can_get_unread_notifications(): void
    {
        // Create some notifications
        $this->user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => OrderNotification::class,
            'data' => ['order_id' => $this->order->id, 'message' => 'Test notification 1'],
            'read_at' => null,
        ]);

        $this->user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => OrderNotification::class,
            'data' => ['order_id' => $this->order->id, 'message' => 'Test notification 2'],
            'read_at' => null,
        ]);

        $this->user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => OrderNotification::class,
            'data' => ['order_id' => $this->order->id, 'message' => 'Test notification 3'],
            'read_at' => now(), // This one is read
        ]);

        $unreadNotifications = $this->notificationService->getUnreadNotifications($this->user);

        $this->assertCount(2, $unreadNotifications);
    }

    public function test_it_can_limit_unread_notifications(): void
    {
        // Create 15 notifications
        for ($i = 0; $i < 15; $i++) {
            $this->user->notifications()->create([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => OrderNotification::class,
                'data' => ['order_id' => $this->order->id, 'message' => "Test notification {$i}"],
                'read_at' => null,
            ]);
        }

        $unreadNotifications = $this->notificationService->getUnreadNotifications($this->user, 5);

        $this->assertCount(5, $unreadNotifications);
    }

    public function test_it_orders_unread_notifications_by_creation_date(): void
    {
        $olderTime = now()->subHours(2);
        $newerTime = now()->subHour();

        // Create older notification first
        $notification1 = $this->user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => OrderNotification::class,
            'data' => ['order_id' => $this->order->id, 'message' => 'Older notification'],
            'read_at' => null,
            'created_at' => $olderTime,
            'updated_at' => $olderTime,
        ]);

        // Create newer notification second
        $notification2 = $this->user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => OrderNotification::class,
            'data' => ['order_id' => $this->order->id, 'message' => 'Newer notification'],
            'read_at' => null,
            'created_at' => $newerTime,
            'updated_at' => $newerTime,
        ]);

        $unreadNotifications = $this->notificationService->getUnreadNotifications($this->user);

        $this->assertCount(2, $unreadNotifications);
        // Newer notification should be first (ordered by created_at desc)
        $this->assertEquals((string) $notification2->id, (string) $unreadNotifications->first()->id);
        $this->assertEquals((string) $notification1->id, (string) $unreadNotifications->last()->id);
    }

    public function test_it_can_get_unread_count(): void
    {
        // Create notifications
        for ($i = 0; $i < 7; $i++) {
            $this->user->notifications()->create([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => OrderNotification::class,
                'data' => ['order_id' => $this->order->id, 'message' => "Test notification {$i}"],
                'read_at' => null,
            ]);
        }

        // Create some read notifications
        for ($i = 0; $i < 3; $i++) {
            $this->user->notifications()->create([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => OrderNotification::class,
                'data' => ['order_id' => $this->order->id, 'message' => "Read notification {$i}"],
                'read_at' => now(),
            ]);
        }

        $unreadCount = $this->notificationService->getUnreadCount($this->user);

        $this->assertEquals(7, $unreadCount);
    }

    public function test_it_can_mark_notification_as_read(): void
    {
        $notification = $this->user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => OrderNotification::class,
            'data' => ['order_id' => $this->order->id, 'message' => 'Test notification'],
            'read_at' => null,
        ]);

        $this->notificationService->markAsRead($this->user, $notification->id);

        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }

    public function test_it_handles_marking_non_existent_notification_as_read(): void
    {
        // This should not throw an error
        $this->notificationService->markAsRead($this->user, 'non-existent-id');

        // The test passes if no exception is thrown
        $this->assertTrue(true);
    }

    public function test_it_can_mark_all_notifications_as_read(): void
    {
        // Create unread notifications
        for ($i = 0; $i < 5; $i++) {
            $this->user->notifications()->create([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => OrderNotification::class,
                'data' => ['order_id' => $this->order->id, 'message' => "Test notification {$i}"],
                'read_at' => null,
            ]);
        }

        $this->assertEquals(5, $this->user->unreadNotifications()->count());

        $this->notificationService->markAllAsRead($this->user);

        $this->assertEquals(0, $this->user->unreadNotifications()->count());
        $this->assertEquals(5, $this->user->readNotifications()->count());
    }

    public function test_it_can_delete_notification(): void
    {
        $notification = $this->user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => OrderNotification::class,
            'data' => ['order_id' => $this->order->id, 'message' => 'Test notification'],
            'read_at' => null,
        ]);

        $result = $this->notificationService->deleteNotification($this->user, $notification->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

    public function test_it_returns_false_when_deleting_non_existent_notification(): void
    {
        $result = $this->notificationService->deleteNotification($this->user, 'non-existent-id');

        $this->assertFalse($result);
    }

    public function test_it_only_deletes_user_own_notification(): void
    {
        $anotherUser = User::factory()->create();

        $notification = $anotherUser->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => OrderNotification::class,
            'data' => ['order_id' => $this->order->id, 'message' => 'Test notification'],
            'read_at' => null,
        ]);

        $result = $this->notificationService->deleteNotification($this->user, $notification->id);

        $this->assertFalse($result);
        $this->assertDatabaseHas('notifications', ['id' => $notification->id]);
    }

    public function test_it_can_handle_empty_users_array(): void
    {
        Notification::fake();

        $this->notificationService->createOrderNotificationForUsers(
            $this->order,
            NotificationType::ORDER_CONFIRMED(),
            []
        );

        Notification::assertNothingSent();
    }
}
