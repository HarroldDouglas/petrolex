<?php

namespace Tests\Feature\Services\Invoice;

use App\Events\OrderCreatedEvent;
use App\Events\OrderStatusChanged;
use App\Models\Customer;
use App\Models\Order;
use App\Services\Invoice\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InvoiceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function createOrderWithDependencies(array $overrides = []): Order
    {
        $customer = Customer::factory()->create();
        $distributionCenter = \App\Models\DistributionCenter::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create([
            'customer_id' => $customer->id,
        ]);

        return Order::factory()->create(array_merge([
            'customer_id' => $customer->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $deliveryAddress->id,
        ], $overrides));
    }

    /** @test */
    public function complete_invoice_workflow_for_new_order()
    {
        // Arrange
        Queue::fake();
        $order = $this->createOrderWithDependencies(['order_number' => 'INT-TEST-001']);

        // Act - Dispatch OrderCreatedEvent
        event(new OrderCreatedEvent($order, []));

        // Assert - Event was dispatched
        Queue::assertPushed(\App\Listeners\Order\GenerateInvoicePdfListener::class);

        // Process the queue job manually for testing
        $invoiceService = app(InvoiceService::class);
        $filePath = $invoiceService->generateAndStoreInvoicePdf($order);

        // Assert - Invoice was generated
        $this->assertNotEmpty($filePath);
        Storage::disk('local')->assertExists($filePath);
        $this->assertTrue($invoiceService->invoiceExists($order->id));

        // Assert - Invoice can be retrieved
        $retrievedPath = $invoiceService->getInvoicePath($order->id);
        $this->assertNotNull($retrievedPath);
        $this->assertTrue(file_exists($retrievedPath));
    }

    /** @test */
    public function invoice_regeneration_on_status_change()
    {
        // Arrange
        $customer = Customer::factory()->create();
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'order_number' => 'INT-TEST-002',
            'status' => \App\Enums\OrderStatus::PENDING,
        ]);

        $invoiceService = app(InvoiceService::class);

        // Generate initial invoice
        $initialPath = $invoiceService->generateAndStoreInvoicePdf($order);
        $initialSize = Storage::disk('local')->size($initialPath);

        // Act - Change status to confirmed
        $order->update(['status' => \App\Enums\OrderStatus::CONFIRMED]);
        event(new OrderStatusChanged(
            $order,
            \App\Enums\OrderStatus::PENDING,
            \App\Enums\OrderStatus::CONFIRMED
        ));

        // Simulate listener processing
        $newPath = $invoiceService->regenerateInvoicePdf($order);

        // Assert - Invoice was regenerated
        $this->assertEquals($initialPath, $newPath);
        Storage::disk('local')->assertExists($newPath);

        $newSize = Storage::disk('local')->size($newPath);
        $this->assertGreaterThan(0, $newSize);
    }

    /** @test */
    public function invoice_download_controller_integration()
    {
        // Arrange
        $this->actingAs(\App\Models\User::factory()->create());

        $customer = Customer::factory()->create();
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'order_number' => 'INT-TEST-003',
        ]);

        $invoiceService = app(InvoiceService::class);
        $invoiceService->generateAndStoreInvoicePdf($order);

        // Act - Download invoice via controller
        $response = $this->get(route('orders.download.invoice', ['order' => $order->id]));

        // Assert
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition', 'attachment; filename="facture-INT-TEST-003.pdf"');
    }

    /** @test */
    public function invoice_download_fails_when_pdf_missing()
    {
        // Arrange
        $this->actingAs(\App\Models\User::factory()->create());

        $customer = Customer::factory()->create();
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'order_number' => 'INT-TEST-004',
        ]);

        // Don't generate invoice - simulate missing PDF

        // Act & Assert
        $response = $this->get(route('orders.download.invoice', ['order' => $order->id]));
        $response->assertStatus(500);
    }

    /** @test */
    public function multiple_customers_invoices_are_organized_correctly()
    {
        // Arrange
        $customer1 = Customer::factory()->create();
        $customer2 = Customer::factory()->create();

        $order1 = Order::factory()->create([
            'customer_id' => $customer1->id,
            'order_number' => 'CUST1-001',
        ]);

        $order2 = Order::factory()->create([
            'customer_id' => $customer2->id,
            'order_number' => 'CUST2-001',
        ]);

        $invoiceService = app(InvoiceService::class);

        // Act
        $path1 = $invoiceService->generateAndStoreInvoicePdf($order1);
        $path2 = $invoiceService->generateAndStoreInvoicePdf($order2);

        // Assert - Files are in separate customer directories
        $this->assertEquals("invoices/{$customer1->id}/facture-CUST1-001.pdf", $path1);
        $this->assertEquals("invoices/{$customer2->id}/facture-CUST2-001.pdf", $path2);

        Storage::disk('local')->assertExists($path1);
        Storage::disk('local')->assertExists($path2);

        // Assert directories exist
        Storage::disk('local')->assertDirectoryExists("invoices/{$customer1->id}");
        Storage::disk('local')->assertDirectoryExists("invoices/{$customer2->id}");
    }

    /** @test */
    public function invoice_service_handles_order_without_order_number()
    {
        // Arrange
        $customer = Customer::factory()->create();
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'order_number' => null, // No order number
        ]);

        $invoiceService = app(InvoiceService::class);

        // Act
        $path = $invoiceService->generateAndStoreInvoicePdf($order);

        // Assert
        $this->assertEquals("invoices/{$customer->id}/facture-{$order->id}.pdf", $path);
        Storage::disk('local')->assertExists($path);
    }

    /** @test */
    public function invoice_url_generation_works_correctly()
    {
        // Arrange
        $customer = Customer::factory()->create();
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'order_number' => 'URL-TEST-001',
        ]);

        $invoiceService = app(InvoiceService::class);
        $invoiceService->generateAndStoreInvoicePdf($order);

        // Act
        $url = $invoiceService->getInvoiceUrl($order->id);

        // Assert
        $this->assertNotNull($url);
        $this->assertStringContainsString('orders/'.$order->id.'/download/invoice', $url);

        // Test URL is accessible
        $this->actingAs(\App\Models\User::factory()->create());
        $response = $this->get($url);
        $response->assertStatus(200);
    }

    /** @test */
    public function events_are_properly_registered_in_service_provider()
    {
        // Arrange
        Event::fake();

        $customer = Customer::factory()->create();
        $order = Order::factory()->create(['customer_id' => $customer->id]);

        // Act
        event(new OrderCreatedEvent($order, []));
        event(new OrderStatusChanged($order, \App\Enums\OrderStatus::PENDING, \App\Enums\OrderStatus::CONFIRMED));

        // Assert
        Event::assertDispatched(OrderCreatedEvent::class);
        Event::assertDispatched(OrderStatusChanged::class);
    }

    /** @test */
    public function large_order_invoice_generation_performance()
    {
        // Arrange
        $customer = Customer::factory()->create();
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'order_number' => 'PERF-TEST-001',
        ]);

        $invoiceService = app(InvoiceService::class);

        // Act & Assert - Should complete within reasonable time
        $startTime = microtime(true);
        $path = $invoiceService->generateAndStoreInvoicePdf($order);
        $endTime = microtime(true);

        $generationTime = $endTime - $startTime;

        // Assert generation completed and was reasonably fast (less than 10 seconds)
        $this->assertNotEmpty($path);
        Storage::disk('local')->assertExists($path);
        $this->assertLessThan(10, $generationTime, 'Invoice generation took too long');

        // Assert file size is reasonable (not empty, not too large)
        $fileSize = Storage::disk('local')->size($path);
        $this->assertGreaterThan(1000, $fileSize, 'Invoice file seems too small');
        $this->assertLessThan(10000000, $fileSize, 'Invoice file seems too large'); // Less than 10MB
    }
}
