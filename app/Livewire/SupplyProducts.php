<?php

namespace App\Livewire;

use Livewire\Component;

class SupplyProducts extends Component
{
    public $showGasForm = false;
    public $showAccessoryForm = false;
    public $selectedBottleType = '';
    public $quantity = '';
    public $outgoingQuantity = '';
    public $accessoryName = '';
    public $accessoryQuantity = '';
    public $products = [];
    public $productToDelete = null;
    public $isEditing = false;
    public $editProductId = null;
    public $usedBottleTypes = [];
    public $usedAccessoryTypes = [];

    public $bottleTypes = [
        'Bouteille de 6KG',
        'Bouteille de 9KG',
        'Bouteille de 12KG',
        'Bouteille de 15KG',
    ];

    public $accessoryTypes = [
        'Détenteur',
        'Tuyau de gaz',
        'Brûleur',
        'Vanne',
        'Régulateur de pression',
    ];

    public function registerGasBottle()
    {
        $this->validate([
            'selectedBottleType' => 'required',
            'quantity' => 'required|numeric|min:1',
            'outgoingQuantity' => 'required|numeric|min:0|lte:quantity',
        ]);

        if ($this->isEditing) {
            $this->updateProduct();
        } else {
            $this->products[] = [
                'id' => count($this->products) + 1,
                'type' => 'Bouteilles',
                'detail' => $this->selectedBottleType,
                'quantity' => $this->quantity,
                'outgoingQuantity' => $this->outgoingQuantity,
            ];

            $this->updateUsedTypes();
            session()->flash('message', 'Bouteilles ajoutées avec succès!');
        }

        $this->resetForms();
    }

    public function registerAccessory()
    {
        $this->validate([
            'accessoryName' => 'required',
            'accessoryQuantity' => 'required|numeric|min:1',
        ]);

        if ($this->isEditing) {
            $this->updateProduct();
        } else {
            $this->products[] = [
                'id' => count($this->products) + 1,
                'type' => 'Accessoires',
                'detail' => $this->accessoryName,
                'quantity' => $this->accessoryQuantity,
                'outgoingQuantity' => 0,
            ];

            $this->updateUsedTypes();
            session()->flash('message', 'Accessoire ajouté avec succès!');
        }

        $this->resetForms();
    }

    public function updateUsedTypes()
    {
        $this->usedBottleTypes = [];
        $this->usedAccessoryTypes = [];

        foreach ($this->products as $product) {
            if ($product['type'] === 'Bouteilles') {
                $this->usedBottleTypes[] = $product['detail'];
            } elseif ($product['type'] === 'Accessoires') {
                $this->usedAccessoryTypes[] = $product['detail'];
            }
        }
    }

    public function editProduct($id)
    {
        $productIndex = $this->findProductIndex($id);

        if ($productIndex !== false) {
            $product = $this->products[$productIndex];

            $this->isEditing = true;
            $this->editProductId = $id;

            if ($product['type'] === 'Bouteilles') {
                $this->selectedBottleType = $product['detail'];
                $this->quantity = $product['quantity'];
                $this->outgoingQuantity = $product['outgoingQuantity'] ?? 0;
                $this->showGasForm = true;
                $this->showAccessoryForm = false;
            } else {
                $this->accessoryName = $product['detail'];
                $this->accessoryQuantity = $product['quantity'];
                $this->showAccessoryForm = true;
                $this->showGasForm = false;
            }
        }
    }

    public function updateProduct()
    {
        $productIndex = $this->findProductIndex($this->editProductId);

        if ($productIndex !== false) {
            $product = $this->products[$productIndex];

            if ($product['type'] === 'Bouteilles') {
                $this->products[$productIndex]['detail'] = $this->selectedBottleType;
                $this->products[$productIndex]['quantity'] = $this->quantity;
                $this->products[$productIndex]['outgoingQuantity'] = $this->outgoingQuantity;
            } else {
                $this->products[$productIndex]['detail'] = $this->accessoryName;
                $this->products[$productIndex]['quantity'] = $this->accessoryQuantity;
            }

            $this->isEditing = false;
            $this->editProductId = null;
            $this->updateUsedTypes();
            session()->flash('message', 'Produit mis à jour avec succès!');
        }
    }

    public function confirmDeleteProduct($id)
    {
        $this->productToDelete = $id;
        $this->dispatch('showDeleteModal');
    }

    public function deleteProduct()
    {
        if ($this->productToDelete) {
            $index = $this->findProductIndex($this->productToDelete);
            if ($index !== false) {
                array_splice($this->products, $index, 1);
                $this->updateUsedTypes();
                session()->flash('message', 'Produit supprimé avec succès!');
            }
            $this->productToDelete = null;
            $this->dispatch('hideDeleteModal');
        }
    }

    public function scanBottles($id)
    {
        $productIndex = $this->findProductIndex($id);
        if ($productIndex !== false) {
            $product = $this->products[$productIndex];
            // Extraire juste l'identifiant du type de bouteille (ex: "6KG" de "Bouteille de 6KG")
            $bottleType = preg_replace('/[^0-9]/', '', $product['detail']);

            // Redirection vers la page de scan des bouteilles
            return redirect()->route('supplies.scan-bottles', [
                'supply_id' => 1, // Dans un cas réel, ce serait l'ID réel de l'approvisionnement
                'type_id' => $bottleType,
            ]);
        }
    }

    public function toggleGasForm()
    {
        $this->showGasForm = ! $this->showGasForm;
        if ($this->showGasForm) {
            $this->showAccessoryForm = false;
        }
        $this->resetInputFields();
    }

    public function toggleAccessoryForm()
    {
        $this->showAccessoryForm = ! $this->showAccessoryForm;
        if ($this->showAccessoryForm) {
            $this->showGasForm = false;
        }
        $this->resetInputFields();
    }

    private function resetForms()
    {
        $this->resetInputFields();
        $this->showGasForm = false;
        $this->showAccessoryForm = false;
        $this->isEditing = false;
        $this->editProductId = null;
    }

    private function resetInputFields()
    {
        $this->selectedBottleType = '';
        $this->quantity = '';
        $this->outgoingQuantity = '';
        $this->accessoryName = '';
        $this->accessoryQuantity = '';
    }

    private function findProductIndex($id)
    {
        foreach ($this->products as $index => $product) {
            if ($product['id'] == $id) {
                return $index;
            }
        }

        return false;
    }

    public function mount()
    {
        $this->updateUsedTypes();
    }

    public function render()
    {
        return view('livewire.supply-products');
    }
}
