<?php

namespace App\Listeners;

use App\Events\OrderCreatedEvent;
use App\Models\ProductCategory;
use App\Services\ProductCategoryService;

class AddOrderItemsToOrderListener
{
    /**
     * Create the event listener.
     */
    public function __construct(
        private ProductCategoryService $productCategoryService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(OrderCreatedEvent $event): void
    {
        $productCategories = ProductCategory::whereIn('id', array_column($event->orderItemsData, 'product_category_id'))->get();

        $itemsData = collect($event->orderItemsData)
            ->map(function ($itemData) use ($productCategories) {

                $productCategory = $productCategories->where('id', $itemData['product_category_id'])->first();

                $unitPrice = $this->productCategoryService->getProductPrice(
                    $productCategory,
                    $itemData['option']
                );

                $itemData['unit_price'] = $unitPrice;
                $itemData['total_price'] = $unitPrice * $itemData['quantity'];

                return $itemData;
            })
            ->toArray();

        $event->order->items()->createMany($itemsData);
    }
}
