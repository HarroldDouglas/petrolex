<?php

namespace App\Livewire\Order;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
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

    public array $availableOptions = [];

    public array $availableProducts = [
        'Bouteille 6kg',
        'Bouteille 12,5kg',
        'Bouteille 39kg',
        'Bouteille 50kg',
    ];
    public array $productOptionPrices = [];

    public string $selectedProduct = '';
    public $selectedOption = '';
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
    }

    public function rules()
    {
        // @phpstan-ignore-next-line
        return $this->customRequest()->rules();
    }

    public function messages()
    {
        return $this->customRequest()->messages();
    }

    /**
     * Get the request class for validation
     */
    abstract protected function customRequest(): FormRequest;

    public function render()
    {
        return view('livewire.order.order-form');
    }

    /**
     * Real-time validation for each field
     */
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function isProductAddButtonDisabled(): bool
    {

        if (empty($this->selectedProduct) || empty($this->selectedOption)
        || empty($this->productOptionQuantity)) {
            return true;
        }

        if ((float) $this->productOptionQuantity <= 0) {
            return true;
        }

        return false;
    }

    public function addProduct()
    {

        foreach ($this->productOptionPrices as $item) {
            if ($item['product'] === $this->selectedProduct && $item['option'] === $this->selectedOption) {
                $this->addError('selectedProduct', 'Ce produit avec cette option existe déjà.');

                return;
            }
        }

        $this->productOptionPrices[] = [
            'product' => $this->selectedProduct,
            'name' => $this->selectedProduct,
            'option' => $this->selectedOption,
            'price' => 6000, // Get this from API
            'quantity' => $this->productOptionQuantity,
        ];

        $this->selectedProduct = '';
        $this->selectedOption = '';
        $this->productOptionQuantity = 1;
    }

    public function removeProductOptionPrice($index)
    {
        unset($this->productOptionPrices[$index]);
        $this->productOptionPrices = array_values($this->productOptionPrices);
    }

    abstract public function save();
}
