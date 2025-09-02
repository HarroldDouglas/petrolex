<?php

namespace Database\Factories;

use App\Enums\Language;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\DeliveryPerson;
use App\Models\DistributionCenter;
use App\Models\Geography\Country;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        $cameroon = Country::where('code', 'CM')->first();

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => strtolower($firstName.'.'.$lastName.'@example.com'),
            'phone_number' => '6'.fake()->numerify('#########'),
            'address' => fake()->address(),
            'country_id' => $cameroon?->id,
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'language' => fake()->randomElement(Language::getValues()),
            'remember_token' => Str::random(10),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): self
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create a manager user.
     */
    public function manager(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'email' => 'manager_'.Str::random(5).'@example.com',
            ];
        });
    }

    /**
     * Create a accountant user.
     */
    public function accountant(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'email' => 'accountant_'.Str::random(5).'@example.com',
            ];
        });
    }

    /**
     * Create a center manager user.
     */
    public function centerManager(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'email' => 'center_'.Str::random(5).'@example.com',
            ];
        });
    }

    /**
     * Create a warehouse manager user.
     */
    public function warehouseManager(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'email' => 'warehouse_'.Str::random(5).'@example.com',
            ];
        });
    }

    /**
     * Create a delivery person user.
     */
    public function deliveryPerson(?int $centerId = null, bool $isActive = true): static
    {
        return $this->state(function (array $attributes) {
            return [
                'email' => 'delivery_'.Str::random(5).'@example.com',
            ];
        })->afterCreating(function (User $user) use ($centerId, $isActive) {
            $user->assignRole(UserRole::DELIVERY_PERSON()->value);

            $deliveryPerson = DeliveryPerson::create([
                'user_id' => $user->id,
            ]);

            if ($centerId) {
                $deliveryPerson->distributionCenters()->sync([
                    $centerId => [
                        'is_active' => $isActive,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);
            } else {
                $randomCenter = DistributionCenter::inRandomOrder()->first();
                if ($randomCenter) {
                    $deliveryPerson->distributionCenters()->sync([
                        $centerId => [
                            'is_active' => $isActive,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                    ]);
                }
            }
        });
    }

    /**
     * Create a customer user.
     */
    public function customer(?float $currentBalance = null, int $addressesCount = 1): static
    {
        return $this->state(function (array $attributes) {
            return [
                'email' => 'customer_'.Str::random(5).'@example.com',
            ];
        })->afterCreating(function (User $user) use ($currentBalance, $addressesCount) {
            $user->assignRole(UserRole::CUSTOMER()->value);

            $customer = Customer::create([
                'user_id' => $user->id,
                'current_balance' => $currentBalance ?? fake()->randomFloat(2, 0, 500),
            ]);

            if ($addressesCount > 0) {
                // First address is the default home address
                CustomerDeliveryAddress::factory()
                    ->for($customer)
                    ->default()
                    ->create();

                if ($addressesCount > 1) {
                    CustomerDeliveryAddress::factory()
                        ->for($customer)
                        ->count($addressesCount - 1)
                        ->create();
                }
            }
        });
    }
}
