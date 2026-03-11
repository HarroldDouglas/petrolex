<?php

namespace App\Console\Commands\Test;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\DeliveryPerson;
use App\Models\Order;
use App\Models\OrderPayment;
use Illuminate\Console\Command;

class UpdateOrderStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:update-order-status {order_id} {--delivery_person_id= : The ID of the delivery person to assign}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the status and payment method of a given order to PAID/processing and paid, optionally assigning a delivery person.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (app()->isProduction()) {
            $this->error('This command is disabled in production.');

            return self::FAILURE;
        }

        $orderId = $this->argument('order_id');
        $deliveryPersonId = $this->option('delivery_person_id');

        /** @var \App\Models\Order|null $order */
        $order = Order::find($orderId);

        if (! $order) {
            $this->error("Order with ID {$orderId} not found.");

            return self::FAILURE;
        }

        if ($deliveryPersonId) {
            /** @var \App\Models\DeliveryPerson|null $deliveryPerson */
            $deliveryPerson = DeliveryPerson::find($deliveryPersonId);

            if (! $deliveryPerson) {
                $this->error("Delivery Person with ID {$deliveryPersonId} not found.");

                return self::FAILURE;
            }

            $order->assignToDeliveryPerson($deliveryPersonId);
            $this->info("  - Delivery Person assigned: {$deliveryPerson->user->email}");
        }
        // Ensure order status is PAID after any operation
        $order->status = OrderStatus::PAID();
        $order->save();

        // Update or create OrderPayment
        /** @var \App\Models\OrderPayment $orderPayment */
        $orderPayment = $order->payment()->firstOrNew([]);

        // Update payment status to PAID
        $orderPayment->payment_status = PaymentStatus::PAID();

        // Select a random payment method
        $randomPaymentMethod = PaymentMethod::cases()[array_rand(PaymentMethod::cases())];
        $orderPayment->payment_method = $randomPaymentMethod;

        // Associate with order if new and save
        if (! $orderPayment->exists) {
            $orderPayment->order_id = $order->id;
        }
        $orderPayment->save();

        $this->info("Order {$orderId} updated successfully:");
        $this->info("  - Status: {$order->status->value}");
        $this->info("  - Payment Status: {$orderPayment->payment_status->value}");
        $this->info("  - Payment Method: {$orderPayment->payment_method->value}");

        return self::SUCCESS;
    }
}
