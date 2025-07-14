<?php

namespace App\Livewire\Order;

use App\Enums\BottleOrderType;
use App\Enums\PaymentMethod;
use App\Enums\ProductType;
use App\Services\DistributionCenter\DistributionCenterService;
use App\Services\User\UserService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Livewire\Component;

abstract class AbstractOrderForm extends Component
{
    public $customer_id;
    public $delivery_address_id;
    public $distribution_center_id;
    public $delivery_type;
    public array $items = [];
    public EloquentCollection $customers;
    protected UserService $userService;
    protected DistributionCenterService $distributionCenterService;

    public function boot(UserService $userService, DistributionCenterService $distributionCenterService)
    {
        $this->userService = $userService;
        $this->distributionCenterService = $distributionCenterService;
    }

    public EloquentCollection $distributionCenters;
    public EloquentCollection $customerAddresses;

    public function updatedCustomer($value)
    {
        $this->customerAddresses = new EloquentCollection;
        $this->delivery_address_id = null;

        if ($value) {
            /** @var \App\Models\User|null $user */
            $user = $this->customers->firstWhere('id', $value);
            if ($user && $user->customer) {
                $this->customerAddresses = $user->customer->deliveryAddresses;
            }
        }
    }

    public array $paymentMethods = [];

    public Collection $allAvailableProducts;

    public bool $showOptionField = false;
    public array $productOptions = [];
    public ?array $selectedProductDetails = null;

    public array $productOptionPrices = [];

    public string $selectedProduct = '';
    public string $selectedOption = '';
    public int $productOptionQuantity = 1;

    public $customer;
    public $distribution_center;
    public $customer_address;
    public string $payment_method = '';

    public function initialize()
    {
        $this->customers = new EloquentCollection();
        $this->customerAddresses = new EloquentCollection();
        $this->distributionCenters = new EloquentCollection();
        $this->allAvailableProducts = new Collection();
        $this->paymentMethods = collect(PaymentMethod::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label])
            ->toArray();

        $this->fetchCustomers();
        $this->fetchDistributionCenters();
    }

    private function fetchCustomers()
    {
        try {
            $this->customers = $this->userService->getAllCustomers();
        } catch (\Exception $e) {
            session()->flash('error', 'Exception lors du chargement des clients: '.$e->getMessage());
            $this->customers = new EloquentCollection;
        }
    }

    private function fetchDistributionCenters()
    {
        try {
            $this->distributionCenters = $this->distributionCenterService->getAll();
        } catch (\Exception $e) {
            session()->flash('error', 'Exception lors du chargement des centres de distribution: '.$e->getMessage());
            $this->distributionCenters = new EloquentCollection;
        }
    }

    public function updatedDistributionCenter($value)
    {
        // Reset product selection and options if distribution center changes or is cleared
        $this->selectedProduct = '';
        $this->selectedOption = '';
        $this->showOptionField = false;
        $this->productOptions = [];
        $this->selectedProductDetails = null;
        $this->productOptionPrices = [];

        if ($value) {
            $this->allAvailableProducts = $this->distributionCenterService->getProducts($value);
        } else {
            $this->allAvailableProducts = new Collection();
        }
    }

    public function updatedSelectedProduct($value)
    {
        $this->resetValidation('selectedProduct');
        $this->selectedOption = '';
        $this->showOptionField = false;
        $this->productOptions = [];
        $this->selectedProductDetails = null;
 
        if (! empty($value)) {
            /** @var \App\Models\ProductCategory|null $productCategory */
            $productCategory = $this->allAvailableProducts->firstWhere('id', (int) $value);
            
            if ($productCategory) {
                $this->selectedProductDetails = [
                    'id' => $productCategory->id,
                    'name' => $productCategory->name,
                    'product_type' => $productCategory->product_type->value,
                    'productTypeInstance' => null,
                ];

                $productTypeInstance = $productCategory->productTypeInstance;

                if ($productTypeInstance) {
                    if ($productCategory->product_type === ProductType::BOTTLE()) {
                        $this->showOptionField = true;
                        $this->selectedProductDetails['productTypeInstance'] = [
                            'content_price' => $productTypeInstance->content_price,
                            'bottle_with_content_price' => $productTypeInstance->bottle_with_content_price,
                        ];
                        $this->productOptions = [
                            BottleOrderType::FULL()->value => BottleOrderType::FULL()->label.' ('.$productTypeInstance->bottle_with_content_price.' XAF)',
                            BottleOrderType::RECHARGE()->value => BottleOrderType::RECHARGE()->label.' ('.$productTypeInstance->content_price.' XAF)',
                        ];
                    } elseif ($productCategory->product_type === ProductType::ACCESSORY()) {
                        $this->selectedProductDetails['productTypeInstance'] = [
                            'price' => $productTypeInstance->price,
                        ];
                    }
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
        /** @var array|null $selectedProductDetails */
        $selectedProductDetails = $this->selectedProductDetails;

        if (! $selectedProductDetails) {
            $this->addError('selectedProduct', 'Veuillez sélectionner un produit valide.');

            return;
        }

        $productName = $selectedProductDetails['name'];
        $productType = $selectedProductDetails['product_type'];
        $price = 0;
        $optionName = '';

        if ($productType === ProductType::BOTTLE()->value) {
            if (empty($this->selectedOption)) {
                $this->addError('selectedOption', 'Veuillez sélectionner une option pour la bouteille.');

                return;
            }
            
            $bottleType = $selectedProductDetails['productTypeInstance'];
            if ($this->selectedOption === BottleOrderType::FULL()->value) {
                $price = $bottleType['bottle_with_content_price'];
                $optionName = BottleOrderType::FULL()->label;
            } elseif ($this->selectedOption === BottleOrderType::RECHARGE()->value) {
                $price = $bottleType['content_price'];
                $optionName = BottleOrderType::RECHARGE()->label;
            }
           
        } else {
            $price = $selectedProductDetails['productTypeInstance']['price'];
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
            'product_type' => $productType,
            'option' => $this->selectedOption,
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
