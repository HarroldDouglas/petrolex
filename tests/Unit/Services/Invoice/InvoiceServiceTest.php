<?php

namespace Tests\Unit\Services\Invoice;

use App\Models\Customer;
use App\Models\Order;
use App\Services\Invoice\InvoiceService;
use App\Services\Order\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InvoiceServiceTest extends TestCase
{
    use RefreshDatabase;

    private InvoiceService $invoiceService;
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->invoiceService = app(InvoiceService::class);

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
    public function it_can_generate_and_store_invoice_pdf()
    {
        // Act
        $filePath = $this->invoiceService->generateAndStoreInvoicePdf($this->order);

        // Assert
        $this->assertNotEmpty($filePath);
        $this->assertEquals("invoices/{$this->order->customer_id}/facture-TEST-12345.pdf", $filePath);

        // Verify file exists in storage
        Storage::disk('local')->assertExists($filePath);

        // Verify file is not empty
        $fileSize = Storage::disk('local')->size($filePath);
        $this->assertGreaterThan(0, $fileSize);
    }

    /** @test */
    public function it_can_get_invoice_path_for_existing_invoice()
    {
        // Arrange
        $this->invoiceService->generateAndStoreInvoicePdf($this->order);

        // Act
        $path = $this->invoiceService->getInvoicePath($this->order->id);

        // Assert
        $this->assertNotNull($path);
        $this->assertTrue(file_exists($path));
        $this->assertStringContainsString('facture-TEST-12345.pdf', $path);
    }

    /** @test */
    public function it_returns_null_when_invoice_does_not_exist()
    {
        // Act
        $path = $this->invoiceService->getInvoicePath($this->order->id);

        // Assert
        $this->assertNull($path);
    }

    /** @test */
    public function it_returns_null_for_non_existent_order()
    {
        // Act
        $path = $this->invoiceService->getInvoicePath(999999);

        // Assert
        $this->assertNull($path);
    }

    /** @test */
    public function it_can_check_if_invoice_exists()
    {
        // Assert invoice doesn't exist initially
        $this->assertFalse($this->invoiceService->invoiceExists($this->order->id));

        // Generate invoice
        $this->invoiceService->generateAndStoreInvoicePdf($this->order);

        // Assert invoice now exists
        $this->assertTrue($this->invoiceService->invoiceExists($this->order->id));
    }

    /** @test */
    public function it_can_regenerate_invoice_pdf()
    {
        // Arrange - Generate initial invoice
        $initialPath = $this->invoiceService->generateAndStoreInvoicePdf($this->order);
        $initialSize = Storage::disk('local')->size($initialPath);

        // Act - Regenerate
        $newPath = $this->invoiceService->regenerateInvoicePdf($this->order);

        // Assert
        $this->assertEquals($initialPath, $newPath);
        Storage::disk('local')->assertExists($newPath);

        // Verify new file was generated (might have different size due to timestamp)
        $newSize = Storage::disk('local')->size($newPath);
        $this->assertGreaterThan(0, $newSize);
    }

    /** @test */
    public function it_creates_customer_directory_if_not_exists()
    {
        // Arrange
        $customerDir = "invoices/{$this->order->customer_id}";

        // Verify directory doesn't exist initially
        $this->assertFalse(Storage::disk('local')->exists($customerDir));

        // Act
        $this->invoiceService->generateAndStoreInvoicePdf($this->order);

        // Assert
        $this->assertTrue(Storage::disk('local')->exists($customerDir));
    }

    /** @test */
    public function it_generates_correct_filename_with_order_number()
    {
        // Act
        $filePath = $this->invoiceService->generateAndStoreInvoicePdf($this->order);

        // Assert
        $this->assertStringContainsString('facture-TEST-12345.pdf', $filePath);
    }

    /** @test */
    public function it_generates_correct_filename_with_order_id_when_no_order_number()
    {
        // Arrange - Create a new order without order_number
        $customer = Customer::factory()->create();
        $distributionCenter = \App\Models\DistributionCenter::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create([
            'customer_id' => $customer->id,
        ]);

        $orderWithoutNumber = Order::factory()->create([
            'customer_id' => $customer->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $deliveryAddress->id,
            'order_number' => null,
        ]);

        // Act
        $filePath = $this->invoiceService->generateAndStoreInvoicePdf($orderWithoutNumber);

        // Assert
        $this->assertStringContainsString("facture-{$orderWithoutNumber->id}.pdf", $filePath);
    }

    /** @test */
    public function it_throws_exception_when_order_details_not_found()
    {
        // Arrange - Mock OrderService to return null
        $mockOrderService = $this->createMock(OrderService::class);
        $mockOrderService->method('getOrderWithGroupedItems')->willReturn(null);

        $invoiceService = new InvoiceService($mockOrderService);

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Order details not found for order {$this->order->id}");

        $invoiceService->generateAndStoreInvoicePdf($this->order);
    }

    /** @test */
    public function it_returns_correct_invoice_url()
    {
        // Arrange
        $this->invoiceService->generateAndStoreInvoicePdf($this->order);

        // Act
        $url = $this->invoiceService->getInvoiceUrl($this->order->id);

        // Assert
        $this->assertNotNull($url);
        $this->assertStringContainsString('orders/'.$this->order->id.'/download/invoice', $url);
    }

    /** @test */
    public function it_returns_null_url_when_invoice_does_not_exist()
    {
        // Act
        $url = $this->invoiceService->getInvoiceUrl($this->order->id);

        // Assert
        $this->assertNull($url);
    }
}
