<?php

namespace Tests\Unit\Listeners\Order;

use App\Events\OrderCreatedEvent;
use App\Events\OrderStatusChanged;
use App\Listeners\Order\GenerateInvoicePdfListener;
use App\Models\Customer;
use App\Models\Order;
use App\Services\Invoice\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class GenerateInvoicePdfListenerTest extends TestCase
{
    use RefreshDatabase;

    private GenerateInvoicePdfListener $listener;
    private InvoiceService $mockInvoiceService;
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockInvoiceService = $this->createMock(InvoiceService::class);
        $this->listener = new GenerateInvoicePdfListener($this->mockInvoiceService);

        // Create test data with all required dependencies
        $customer = Customer::factory()->create();
        $distributionCenter = \App\Models\DistributionCenter::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create([
            'customer_id' => $customer->id,
        ]);

        $this->order = Order::factory()->create([
            'customer_id' => $customer->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $deliveryAddress->id,
            'order_number' => 'TEST-12345',
        ]);
    }

    /** @test */
    public function it_generates_invoice_on_order_created_event()
    {
        // Arrange
        $event = new OrderCreatedEvent($this->order, []);

        $this->mockInvoiceService
            ->expects($this->once())
            ->method('generateAndStoreInvoicePdf')
            ->with($this->order);

        // Act
        $this->listener->handle($event);

        // Assert - Mock expectations are automatically verified
    }

    /** @test */
    public function it_regenerates_invoice_on_important_status_change()
    {
        // Arrange - Test each important status
        $importantStatuses = ['confirmed', 'paid', 'processing', 'delivered'];

        foreach ($importantStatuses as $status) {
            $event = new OrderStatusChanged(
                $this->order,
                \App\Enums\OrderStatus::PENDING,
                \App\Enums\OrderStatus::from($status)
            );

            $this->mockInvoiceService
                ->expects($this->once())
                ->method('regenerateInvoicePdf')
                ->with($this->order);

            // Act
            $this->listener->handle($event);
        }
    }

    /** @test */
    public function it_skips_invoice_generation_on_unimportant_status_change()
    {
        // Arrange
        $event = new OrderStatusChanged(
            $this->order,
            \App\Enums\OrderStatus::PENDING,
            \App\Enums\OrderStatus::CANCELLED
        );

        $this->mockInvoiceService
            ->expects($this->never())
            ->method('regenerateInvoicePdf');

        $this->mockInvoiceService
            ->expects($this->never())
            ->method('generateAndStoreInvoicePdf');

        // Act
        $this->listener->handle($event);

        // Assert - Mock expectations are automatically verified
    }

    /** @test */
    public function it_logs_successful_invoice_generation_for_new_order()
    {
        // Arrange
        Log::fake();
        $event = new OrderCreatedEvent($this->order, []);

        $this->mockInvoiceService
            ->method('generateAndStoreInvoicePdf')
            ->willReturn('test/path.pdf');

        // Act
        $this->listener->handle($event);

        // Assert
        Log::assertLogged('info', function ($message, $context) {
            return $message === 'Starting invoice PDF generation' &&
                   $context['order_id'] === $this->order->id &&
                   $context['event_type'] === OrderCreatedEvent::class;
        });

        Log::assertLogged('info', function ($message, $context) {
            return $message === 'Invoice PDF generated for new order' &&
                   $context['order_id'] === $this->order->id;
        });
    }

    /** @test */
    public function it_logs_successful_invoice_regeneration_for_status_change()
    {
        // Arrange
        Log::fake();
        $event = new OrderStatusChanged(
            $this->order,
            \App\Enums\OrderStatus::PENDING,
            \App\Enums\OrderStatus::CONFIRMED
        );

        $this->mockInvoiceService
            ->method('regenerateInvoicePdf')
            ->willReturn('test/path.pdf');

        // Act
        $this->listener->handle($event);

        // Assert
        Log::assertLogged('info', function ($message, $context) {
            return $message === 'Invoice PDF regenerated for status change' &&
                   $context['order_id'] === $this->order->id &&
                   $context['old_status'] === 'pending' &&
                   $context['new_status'] === 'confirmed';
        });
    }

    /** @test */
    public function it_logs_skipped_regeneration_for_unimportant_status()
    {
        // Arrange
        Log::fake();
        $event = new OrderStatusChanged(
            $this->order,
            \App\Enums\OrderStatus::PENDING,
            \App\Enums\OrderStatus::CANCELLED
        );

        // Act
        $this->listener->handle($event);

        // Assert
        Log::assertLogged('debug', function ($message, $context) {
            return $message === 'Skipping invoice regeneration for status change' &&
                   $context['order_id'] === $this->order->id &&
                   $context['new_status'] === 'cancelled';
        });
    }

    /** @test */
    public function it_logs_and_rethrows_exceptions()
    {
        // Arrange
        Log::fake();
        $event = new OrderCreatedEvent($this->order, []);
        $exception = new \Exception('Test exception');

        $this->mockInvoiceService
            ->method('generateAndStoreInvoicePdf')
            ->willThrowException($exception);

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Test exception');

        $this->listener->handle($event);

        // Assert error was logged
        Log::assertLogged('critical', function ($message, $context) {
            return $message === 'Invoice PDF generation failed' &&
                   $context['order_id'] === $this->order->id &&
                   $context['error'] === 'Test exception';
        });
    }

    /** @test */
    public function it_logs_permanent_failure_in_failed_method()
    {
        // Arrange
        Log::fake();
        $event = new OrderCreatedEvent($this->order, []);
        $exception = new \Exception('Permanent failure');

        // Act
        $this->listener->failed($event, $exception);

        // Assert
        Log::assertLogged('critical', function ($message, $context) {
            return $message === 'Invoice PDF generation failed permanently after all retries' &&
                   $context['order_id'] === $this->order->id &&
                   $context['error'] === 'Permanent failure';
        });
    }

    /** @test */
    public function it_handles_both_event_types()
    {
        // Arrange
        $createdEvent = new OrderCreatedEvent($this->order, []);
        $statusEvent = new OrderStatusChanged(
            $this->order,
            \App\Enums\OrderStatus::PENDING,
            \App\Enums\OrderStatus::CONFIRMED
        );

        $this->mockInvoiceService
            ->expects($this->once())
            ->method('generateAndStoreInvoicePdf')
            ->with($this->order);

        $this->mockInvoiceService
            ->expects($this->once())
            ->method('regenerateInvoicePdf')
            ->with($this->order);

        // Act
        $this->listener->handle($createdEvent);
        $this->listener->handle($statusEvent);

        // Assert - Mock expectations are automatically verified
    }
}
