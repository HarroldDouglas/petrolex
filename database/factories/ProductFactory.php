<?php

namespace Database\Factories;

use App\Enums\BottleStatus;
use App\Enums\ProductType;
use App\Models\AccessoryType;
use App\Models\BottleType;
use App\Models\DistributionCenter;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'product_category_id' => ProductCategory::inRandomOrder()->first()->id,
        ];
    }

    /**
     * Méthode générique pour créer un produit avec un type spécifique
     *
     * @param  ProductType  $productType  Type de produit (BOTTLE ou ACCESSORY)
     * @param  int|null  $productCategoryId  ID de catégorie de produit (optionnel)
     * @param  int|null  $typeId  ID du type spécifique (bouteille ou accessoire)
     * @param  string  $typeModelClass  Classe du modèle de type (BottleType ou AccessoryType)
     * @return array État à appliquer au produit
     */
    protected function createProductState(
        ProductType $productType,
        ?int $productCategoryId,
        ?int $typeId,
        string $typeModelClass
    ): array {
        if ($productCategoryId) {
            return [
                'product_category_id' => $productCategoryId,
            ];
        }

        $productTypeModel = $typeId ? $typeModelClass::find($typeId) : $typeModelClass::inRandomOrder()->first();

        if (! $productTypeModel) {
            throw new \RuntimeException("Aucun modèle de type {$typeModelClass} trouvé.");
        }

        $productCategory = ProductCategory::where('product_type', $productType)
            ->where('product_type_id', $productTypeModel->id)
            ->first();

        if (! $productCategory) {
            throw new \RuntimeException("Aucune catégorie de produit trouvée pour {$productType->name} avec ID: {$productTypeModel->id}");
        }

        return [
            'product_category_id' => $productCategory->id,
        ];
    }

    /**
     * Détermine le modèle de type approprié (bouteille ou accessoire)
     *
     * @param  Product  $product  Produit créé
     * @param  ProductType  $productType  Type de produit (BOTTLE ou ACCESSORY)
     * @param  int|null  $typeId  ID du type spécifique (si fourni)
     * @param  string  $typeModelClass  Classe du modèle de type
     * @param  string  $idField  Nom du champ ID dans la catégorie de produit
     * @return Model Instance du modèle de type
     */
    protected function resolveProductTypeModel(
        Product $product,
        ProductType $productType,
        ?int $typeId,
        string $typeModelClass,
        string $idField = 'product_type_id'
    ): Model {
        // Si l'ID du type est fourni, utilisons-le
        if ($typeId) {
            $typeModel = $typeModelClass::find($typeId);
            if ($typeModel) {
                return $typeModel;
            }
        }

        if ($product->productCategory && $product->productCategory->product_type === $productType) {
            $typeModel = $typeModelClass::find($product->productCategory->$idField);
            if ($typeModel) {
                return $typeModel;
            }
        }

        $typeModel = $typeModelClass::inRandomOrder()->first();

        if (! $typeModel) {
            throw new \RuntimeException("Aucun modèle de type {$typeModelClass} trouvé. Assurez-vous que des types existent.");
        }

        return $typeModel;
    }

    public function bottle(
        ?int $productCategoryId = null,
        ?int $bottleTypeId = null,
        ?int $distributionCenterId = null,
        array $bottleAttributes = []
    ): static {
        return $this->state(function (array $attributes) use ($productCategoryId, $bottleTypeId) {
            return $this->createProductState(
                ProductType::BOTTLE(),
                $productCategoryId,
                $bottleTypeId,
                BottleType::class
            );
        })->afterCreating(function (Product $product) use ($bottleTypeId, $distributionCenterId, $bottleAttributes) {
            $bottleType = $this->resolveProductTypeModel(
                $product,
                ProductType::BOTTLE(),
                $bottleTypeId,
                BottleType::class
            );

            $distributionCenter = $distributionCenterId ?: DistributionCenter::inRandomOrder()->first()->id;

            $defaultAttributes = [
                'product_id' => $product->id,
                'bottle_type_id' => $bottleType->id,
                'distribution_center_id' => $distributionCenter,
                'barcode' => 'BT'.strtoupper(Str::random(8)),
                'is_filled' => fake()->boolean(65),
                'status' => BottleStatus::IN_STOCK(),
            ];

            $product->bottle()->create(array_merge($defaultAttributes, $bottleAttributes));
        });
    }

    public function accessory(
        ?int $productCategoryId = null,
        ?int $accessoryTypeId = null,
        ?int $distributionCenterId = null,
        array $accessoryAttributes = []
    ): static {
        return $this->state(function (array $attributes) use ($productCategoryId, $accessoryTypeId) {
            return $this->createProductState(
                ProductType::ACCESSORY(),
                $productCategoryId,
                $accessoryTypeId,
                AccessoryType::class
            );
        })->afterCreating(function (Product $product) use ($accessoryTypeId, $distributionCenterId, $accessoryAttributes) {
            $accessoryType = $this->resolveProductTypeModel(
                $product,
                ProductType::ACCESSORY(),
                $accessoryTypeId,
                AccessoryType::class
            );

            $distributionCenter = $distributionCenterId ?: DistributionCenter::inRandomOrder()->first()->id;

            $defaultAttributes = [
                'product_id' => $product->id,
                'accessory_type_id' => $accessoryType->id,
                'distribution_center_id' => $distributionCenter,
            ];

            $product->accessory()->create(array_merge($defaultAttributes, $accessoryAttributes));
        });
    }
}
