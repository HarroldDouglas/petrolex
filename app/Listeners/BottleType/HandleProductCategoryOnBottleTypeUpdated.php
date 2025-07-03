<?php

namespace App\Listeners\BottleType;

use App\DTOs\BottleType\ProductCategoryCityPriceDTO;
use App\Enums\ProductType;
use App\Events\BottleType\BottleTypeUpdatedEvent;
use App\Repositories\Contracts\ProductCategoryRepositoryInterface;
use App\Services\ProductCategoryCityPrice\ProductCategoryCityPriceService;
use Illuminate\Support\Facades\Log;

class HandleProductCategoryOnBottleTypeUpdated
{
    public function __construct(
        private ProductCategoryRepositoryInterface $productCategoryRepository,
        private ProductCategoryCityPriceService $cityPriceService
    ) {}

    public function handle(BottleTypeUpdatedEvent $event): void
    {
        Log::debug('=== DÉBUT HandleProductCategoryOnBottleTypeUpdated ===');
        Log::debug('BottleType ID: '.$event->bottleType->id);

        Log::debug('STACK TRACE: ', [
            'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10),
        ]);

        Log::debug('Event data: ', ['data' => $event->data]);

        $productCategory = $this->productCategoryRepository->findByIdAndType(
            $event->bottleType->id,
            ProductType::BOTTLE()
        );

        if (! $productCategory) {
            Log::warning('ProductCategory not found for BottleType ID: '.$event->bottleType->id);

            return;
        }

        Log::debug('ProductCategory found: ', [
            'id' => $productCategory->id,
            'name' => $productCategory->name ?? 'N/A',
            'current_city_prices_count' => $productCategory->cityPrices()->count(),
        ]);

        // Supprimer les anciens prix
        $deletedCount = $productCategory->cityPrices()->count();
        $productCategory->cityPrices()->delete();
        Log::debug('Deleted city prices count: '.$deletedCount);

        // Vérifier les données d'entrée
        Log::debug('bottleTypeCityPrices empty check: ', [
            'is_null' => is_null($event->data->bottleTypeCityPrices),
            'is_empty' => empty($event->data->bottleTypeCityPrices),
            'count' => $event->data->bottleTypeCityPrices ? count($event->data->bottleTypeCityPrices) : 0,
        ]);

        if (! empty($event->data->bottleTypeCityPrices)) {
            Log::debug('Processing city prices...');

            // Log chaque DTO
            foreach ($event->data->bottleTypeCityPrices as $index => $dto) {
                Log::debug("DTO $index: ", [
                    'type' => get_class($dto),
                    'data' => $dto->toArray(),
                ]);
            }

            $cityPricesData = array_map(
                fn (ProductCategoryCityPriceDTO $dto) => $dto->toArray(),
                $event->data->bottleTypeCityPrices
            );

            Log::debug('Prepared city prices data: ', $cityPricesData);

            try {
                $createdPrices = $productCategory->cityPrices()->createMany($cityPricesData);
                Log::debug('Created city prices: ', [
                    'count' => count($createdPrices),
                    'created_ids' => $createdPrices->pluck('id')->toArray(),
                ]);
            } catch (\Exception $e) {
                Log::error('Error creating city prices: '.$e->getMessage());
                Log::error('Stack trace: '.$e->getTraceAsString());
            }
        } else {
            Log::debug('No city prices to create');
        }
    }
}
