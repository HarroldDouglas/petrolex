<?php

namespace Database\Factories;

use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\DistributionCenter;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $customer = Customer::inRandomOrder()->first();
        $center = DistributionCenter::inRandomOrder()->first();
        $deliveryAddress = CustomerDeliveryAddress::where('customer_id', $customer?->id)->inRandomOrder()->first();
        $deliveryPerson = User::role(UserRole::DELIVERY_PERSON()->value)->inRandomOrder()->first();

        if (! $customer || ! $center) {
            throw new \RuntimeException('Required data missing. Make sure to run required seeders first.');
        }

        if (! $deliveryAddress) {
            throw new \RuntimeException('No delivery addresses found for customers. Please seed customer addresses first.');
        }

        $subtotal = fake()->randomFloat(2, 50, 500);
        $deliveryFee = fake()->randomElement([0, 10, 15, 20]);
        $totalAmount = $subtotal + $deliveryFee;

        return [
            'customer_id' => $customer->id,
            'delivery_address_id' => $deliveryAddress->id,
            'delivery_person_id' => fake()->optional(0.7)->randomElement([$deliveryPerson?->id]),
            'distribution_center_id' => $center->id,
            'order_number' => 'ORD-'.fake()->unique()->numerify('######'),
            'delivery_type' => fake()->randomElement(DeliveryType::values()),
            'status' => fake()->randomElement(OrderStatus::values()),
            'payment_method' => fake()->randomElement(PaymentMethod::values()),
            'payment_status' => fake()->randomElement(PaymentStatus::values()),
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'total_amount' => $totalAmount,
            'order_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'delivery_date' => fake()->optional(0.6)->dateTimeBetween('-2 weeks', '+1 week'),
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }

    /**
     * Configure the order as confirmed
     */
    public function confirmed(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => OrderStatus::CONFIRMED(),
                'payment_status' => PaymentStatus::PAID(),
            ];
        });
    }

    /**
     * Configure the order as processing
     */
    public function processing(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => OrderStatus::PROCESSING(),
                'payment_status' => PaymentStatus::PAID(),
            ];
        });
    }

    /**
     * Configure the order as delivered
     */
    public function delivered(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => OrderStatus::DELIVERED(),
                'payment_status' => PaymentStatus::PAID(),
                'delivery_date' => fake()->dateTimeBetween('-2 weeks', 'now'),
            ];
        });
    }

    /**
     * Configure the order as cancelled
     */
    public function cancelled(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => OrderStatus::CANCELLED(),
                'notes' => fake()->sentence(),
            ];
        });
    }

    /**
     * Configure the order for fast delivery
     */
    public function fastDelivery(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'delivery_type' => 'fast',
                'delivery_fee' => fake()->randomFloat(2, 20, 50),
            ];
        });
    }
}
