<?php

namespace Database\Factories;

use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\DeliveryPerson;
use App\Models\DistributionCenter;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $customer = Customer::inRandomOrder()->first();
        $distributionCenter = DistributionCenter::inRandomOrder()->first();
        $deliveryAddress = CustomerDeliveryAddress::where('customer_id', $customer->id)
            ->inRandomOrder()->first() ?? CustomerDeliveryAddress::factory()->create(['customer_id' => $customer->id]);

        return [
            'customer_id' => $customer->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $deliveryAddress->id,
            'delivery_person_id' => null,
            'order_number' => 'ORD-'.$this->faker->unique()->bothify('######'),
            'delivery_type' => $this->faker->randomElement(DeliveryType::values()),
            'status' => OrderStatus::CONFIRMED(),
            'payment_status' => $this->faker->randomElement(PaymentStatus::values()),
            'payment_method' => $this->faker->randomElement(PaymentMethod::values()),
            'subtotal' => 0,
            'delivery_fee' => $this->faker->randomElement([0, 500, 1000]),
            'total_amount' => 0,
            'order_date' => now(),
            'delivery_date' => null,
            'comments' => null,
            'center_comments' => null,
            'rating' => null,
        ];
    }

    /**
     * Order with confirmed status
     */
    public function confirmed(): static
    {
        return $this->state(function () {
            return [
                'status' => OrderStatus::CONFIRMED(),
                'delivery_date' => null,
                'delivery_person_id' => null,
                'comments' => null,
                'rating' => null,
            ];
        });
    }

    /**
     * Order with processing status
     */
    public function processing(): static
    {
        return $this->state(function () {
            $deliveryPerson = DeliveryPerson::inRandomOrder()->first();

            return [
                'status' => OrderStatus::PROCESSING(),
                'delivery_date' => null,
                'delivery_person_id' => $deliveryPerson?->id,
                'comments' => null,
                'rating' => null,
            ];
        });
    }

    /**
     * Order with delivered status
     */
    public function delivered(): static
    {
        return $this->state(function () {
            $deliveryPerson = DeliveryPerson::inRandomOrder()->first();

            return [
                'status' => OrderStatus::DELIVERED(),
                'delivery_date' => now()->subDays(rand(1, 3)),
                'delivery_person_id' => $deliveryPerson?->id,
            ];
        });
    }

    /**
     * Order with cancelled status
     */
    public function cancelled(): static
    {
        return $this->state(function () {
            return [
                'status' => OrderStatus::CANCELLED(),
                'delivery_date' => null,
                'delivery_person_id' => null,
            ];
        });
    }
}
