<?php

namespace App\Listeners\Order;

use App\Events\OrderCreatedEvent;
use App\Events\OrderStatusChanged;
use App\Services\Invoice\InvoiceService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class GenerateInvoicePdfListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run before timing out.
     */
    public int $timeout = 120;

    public function __construct(
        private InvoiceService $invoiceService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(OrderCreatedEvent|OrderStatusChanged $event): void
    {
        try {
            $order = $event->order;

            Log::info('Starting invoice PDF generation', [
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'event_type' => get_class($event),
            ]);

            // For OrderCreatedEvent, always generate
            if ($event instanceof OrderCreatedEvent) {
                $this->invoiceService->generateAndStoreInvoicePdf($order);
                Log::info('Invoice PDF generated for new order', ['order_id' => $order->id]);

                return;
            }

            // For OrderStatusChanged, regenerate for important status changes
            if ($event instanceof OrderStatusChanged) {
                $importantStatuses = ['confirmed', 'paid', 'processing', 'delivered'];

                if (in_array($event->newStatus?->value, $importantStatuses)) {
                    $this->invoiceService->regenerateInvoicePdf($order);
                    Log::info('Invoice PDF regenerated for status change', [
                        'order_id' => $order->id,
                        'old_status' => $event->oldStatus?->value,
                        'new_status' => $event->newStatus?->value,
                    ]);
                } else {
                    Log::debug('Skipping invoice regeneration for status change', [
                        'order_id' => $order->id,
                        'new_status' => $event->newStatus?->value,
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::critical('Invoice PDF generation failed', [
                'order_id' => $event->order->id,
                'customer_id' => $event->order->customer_id,
                'event_type' => get_class($event),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(OrderCreatedEvent|OrderStatusChanged $event, \Throwable $exception): void
    {
        Log::critical('Invoice PDF generation failed permanently after all retries', [
            'order_id' => $event->order->id,
            'customer_id' => $event->order->customer_id,
            'event_type' => get_class($event),
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);

        // TODO: Optionally send notification to developers/admins
        // Could also create a database entry to track failed generations
    }
}
