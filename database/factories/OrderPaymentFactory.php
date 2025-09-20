<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderPayment>
 */
class OrderPaymentFactory extends Factory
{
    protected $model = OrderPayment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $paymentMethod = $this->faker->randomElement(PaymentMethod::values());
        $amount = $this->faker->randomFloat(2, 1000, 10000);

        return [
            'order_id' => Order::factory(),
            'payment_method' => $paymentMethod,
            'amount_paid' => $amount,
            'amount_due' => $amount,
            'payment_status' => $this->faker->randomElement(PaymentStatus::values()),
            'payment_reference' => 'PAY-'.$this->faker->unique()->numberBetween(100000, 999999),
            'payment_date' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'payment_notes' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Create a successful payment
     */
    public function successful(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => PaymentStatus::PAID(),
            'payment_date' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Create a pending payment
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => PaymentStatus::PENDING(),
            'payment_date' => null,
        ]);
    }

    /**
     * Create a failed payment
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => PaymentStatus::FAILED(),
            'payment_date' => null,
        ]);
    }
}
