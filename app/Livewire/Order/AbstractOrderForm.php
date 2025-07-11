<?php

namespace App\Livewire\Order;

use App\Enums\PaymentMethod;
use App\Enums\ProductType;
use Livewire\Component;

abstract class AbstractOrderForm extends Component
{
    public $customer_id;
    public $delivery_address_id;
    public $distribution_center_id;
    public $delivery_type;
    public array $items = [];
    public array $customers = [
        ['id' => 1, 'full_name' => 'Jean Dupont'],
        ['id' => 2, 'full_name' => 'Marie Claire'],
    ];

    public array $distributionCenters = [
        ['id' => 1, 'name' => 'Centre Douala'],
        ['id' => 2, 'name' => 'Centre Yaoundé'],
    ];
    public array $customerAddresses = [
        ['id' => 1, 'full_address' => '123 Rue Principale, Douala'],
        ['id' => 2, 'full_address' => '456 Avenue Centrale, Yaoundé'],
    ];

    public array $paymentMethods = [];

    public array $allAvailableProducts = [];

    private function getFakeProducts(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Bouteille de 6Kg',
                'product_type' => ProductType::BOTTLE()->value,
                'productTypeInstance' => [
                    'capacity' => 6,
                    'height' => 45.5,
                    'weight' => 5.2,
                    'radius' => 15.2,
                    'content_price' => 6500,
                    'bottle_with_content_price' => 18500,
                ],
                'pivot' => [
                    'stock_filled' => 40,
                    'stock' => 0,
                ],
            ],
            [
                'id' => 2,
                'name' => 'Bouteille de 9Kg',
                'product_type' => ProductType::BOTTLE()->value,
                'productTypeInstance' => [
                    'capacity' => 9,
                    'height' => 50.0,
                    'weight' => 6.5,
                    'radius' => 17.0,
                    'content_price' => 8000,
                    'bottle_with_content_price' => 22000,
                ],
                'pivot' => [
                    'stock_filled' => 32,
                    'stock' => 0,
                ],
            ],
            [
                'id' => 3,
                'name' => 'Tuyau de gaz standard 5m',
                'product_type' => ProductType::ACCESSORY()->value,
                'productTypeInstance' => [
                    'price' => 2500,
                ],
                'pivot' => [
                    'stock_filled' => 0,
                    'stock' => 61,
                ],
            ],
            [
                'id' => 4,
                'name' => 'Détendeur universel',
                'product_type' => ProductType::ACCESSORY()->value,
                'productTypeInstance' => [
                    'price' => 3500,
                ],
                'pivot' => [
                    'stock_filled' => 0,
                    'stock' => 60,
                ],
            ],
        ];
    }

    public bool $showOptionField = false;
    public array $productOptions = [];
    public ?array $selectedProductDetails = null;

    public array $productOptionPrices = [];

    public string $selectedProduct = ''; // This will now hold the productCategory ID
    public string $selectedOption = '';
    public int $productOptionQuantity = 1;

    public $customer;
    public $distribution_center;
    public $customer_address;
    public string $payment_method = '';

    public function initialize()
    {
        $this->paymentMethods = collect(PaymentMethod::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label])
            ->toArray();

        $this->allAvailableProducts = $this->getFakeProducts();
    }

    public function updatedSelectedProduct($value)
    {
        $this->resetValidation('selectedProduct');
        $this->selectedOption = '';
        $this->showOptionField = false;
        $this->productOptions = [];
        $this->selectedProductDetails = null;

        if (! empty($value)) {
            $productCategory = collect($this->allAvailableProducts)->firstWhere('id', (int) $value);

            if ($productCategory) {
                $this->selectedProductDetails = $productCategory;

                if ($productCategory['product_type'] === ProductType::BOTTLE()->value) {
                    $this->showOptionField = true;
                    $bottleType = $productCategory['productTypeInstance'];
                    $this->productOptions = [
                        \App\Enums\BottleOrderType::FULL()->value => \App\Enums\BottleOrderType::FULL()->label.' ('.$bottleType['bottle_with_content_price'].' XAF)',
                        \App\Enums\BottleOrderType::RECHARGE()->value => \App\Enums\BottleOrderType::RECHARGE()->label.' ('.$bottleType['content_price'].' XAF)',
                    ];
                }
            }
        }
    }

    public function render()
    {
        return view('livewire.order.order-form');
    }

    public function isProductAddButtonDisabled(): bool
    {
        if (empty($this->selectedProduct) || empty($this->productOptionQuantity)) {
            return true;
        }

        if ($this->showOptionField && empty($this->selectedOption)) {
            return true;
        }

        if ((float) $this->productOptionQuantity <= 0) {
            return true;
        }

        return false;
    }

    public function addProduct()
    {
        if (! $this->selectedProductDetails) {
            $this->addError('selectedProduct', 'Veuillez sélectionner un produit valide.');

            return;
        }

        $productName = $this->selectedProductDetails['name'];
        $productType = $this->selectedProductDetails['product_type'];
        $price = 0;
        $optionName = '';

        if ($productType === ProductType::BOTTLE()->value) {
            if (empty($this->selectedOption)) {
                $this->addError('selectedOption', 'Veuillez sélectionner une option pour la bouteille.');

                return;
            }
            $bottleType = $this->selectedProductDetails['productTypeInstance'];
            if ($this->selectedOption === \App\Enums\BottleOrderType::FULL()->value) {
                $price = $bottleType['bottle_with_content_price'];
                $optionName = \App\Enums\BottleOrderType::FULL()->label;
            } elseif ($this->selectedOption === \App\Enums\BottleOrderType::RECHARGE()->value) {
                $price = $bottleType['content_price'];
                $optionName = \App\Enums\BottleOrderType::RECHARGE()->label;
            }
        } else {
            $price = $this->selectedProductDetails['productTypeInstance']['price'];
        }

        // Check for duplicate product-option combination
        foreach ($this->productOptionPrices as $item) {
            if ($item['product_id'] === (int) $this->selectedProduct && $item['option'] === $this->selectedOption) {
                $this->addError('selectedProduct', 'Ce produit avec cette option existe déjà dans le panier.');

                return;
            }
        }

        $this->productOptionPrices[] = [
            'product_id' => (int) $this->selectedProduct,
            'name' => $productName,
            'product_type' => $productType, // Add product_type here
            'option' => $this->selectedOption, // Store the enum value
            'price' => $price,
            'quantity' => $this->productOptionQuantity,
        ];

        // Reset fields after adding
        $this->selectedProduct = '';
        $this->selectedOption = '';
        $this->productOptionQuantity = 1;
        $this->showOptionField = false;
        $this->productOptions = [];
        $this->selectedProductDetails = null;
    }

    public function removeProductOptionPrice($index)
    {
        unset($this->productOptionPrices[$index]);
        $this->productOptionPrices = array_values($this->productOptionPrices);
    }

    abstract public function save();
}
