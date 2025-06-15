<?php

namespace App\Livewire\Order;

use App\Enums\OrderStatus;
use App\Enums\ProductType;
use App\Models\Order;
use HarroldWafo\LaravelCustomDatatable\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filters\DateRangeFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\TextFilter;

class OrderDataTable extends BaseDataTable
{
    protected $model = Order::class;

    protected const DEFAULT_SORT_FIELD = 'order_date';
    protected const DEFAULT_SORT_DIRECTION = 'desc';

    protected function getExportFileName(): string
    {
        return 'commandes';
    }

    public function columns(): array
    {
        return [
            Column::make('N° commande', 'order_number')
                ->sortable()
                ->searchable(),

            Column::make('Centre de distr.', 'distribution_center_id')
                ->sortable()
                ->searchable(function (Builder $query, string $searchTerm) {
                    return $query->whereHas('distributionCenter', function (Builder $q) use ($searchTerm) {
                        $q->where('name', 'like', '%'.$searchTerm.'%');
                    });
                })
                ->format(function ($value, $row) {
                    return $row->distributionCenter->name ?? '-';
                }),

            Column::make('Client', 'customer_id')
                ->sortable()
                ->searchable(function (Builder $query, string $searchTerm) {
                    return $query->whereHas('customer.user', function (Builder $q) use ($searchTerm) {
                        $q->where(function ($subQuery) use ($searchTerm) {
                            $subQuery->where('first_name', 'like', '%'.$searchTerm.'%')
                                ->orWhere('last_name', 'like', '%'.$searchTerm.'%')
                                ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%'.$searchTerm.'%']);
                        });
                    });
                })
                ->format(function ($value, $row) {
                    $user = optional(optional($row->customer)->user);
                    if (! $user->first_name && ! $user->last_name) {
                        return '-';
                    }

                    return trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: '-';
                }),

            Column::make('Produits', 'id')
                ->format(function ($value, $row) {
                    $orderItems = $row->items;
                    if ($orderItems->isEmpty()) {
                        return '-';
                    }

                    // ✅ Grouper les produits par type
                    $groupedProducts = [];

                    foreach ($orderItems as $orderItem) {
                        $product = $orderItem->product;
                        $productName = '';
                        $quantity = $orderItem->quantity;

                        if ($product->product_type == ProductType::BOTTLE()) {
                            $productName = optional($product->bottle->bottleType)->name ?? 'Bouteille';
                        } elseif ($product->product_type == ProductType::ACCESSORY()) {
                            $productName = optional($product->accessory->accessoryType)->name ?? 'Accessoire';
                        }

                        if ($productName) {
                            // ✅ Grouper par nom de produit
                            if (isset($groupedProducts[$productName])) {
                                $groupedProducts[$productName] += $quantity;
                            } else {
                                $groupedProducts[$productName] = $quantity;
                            }
                        }
                    }

                    // ✅ Créer le HTML final
                    $html = '';
                    foreach ($groupedProducts as $productName => $totalQuantity) {
                        $html .= '<span class="badge bg-secondary me-1 mb-1">'.
                            e($productName).' ×'.$totalQuantity.'</span>';
                    }

                    return new HtmlString($html ?: '-');
                }),

            Column::make('Total (CFA)', 'total_amount')
                ->sortable()
                ->format(fn ($value) => number_format($value, 0, ',', ' ').' CFA'),

            Column::make('Livreur', 'delivery_person_id')
                ->sortable()
                ->searchable(function (Builder $query, string $searchTerm) {
                    return $query->whereHas('deliveryPerson.user', function (Builder $q) use ($searchTerm) {
                        $q->where(function ($subQuery) use ($searchTerm) {
                            $subQuery->where('first_name', 'like', '%'.$searchTerm.'%')
                                ->orWhere('last_name', 'like', '%'.$searchTerm.'%')
                                ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%'.$searchTerm.'%']);
                        });
                    });
                })
                ->format(function ($value, $row) {
                    if (! $row->deliveryPerson) {
                        return '-';
                    }
                    $user = optional($row->deliveryPerson->user);
                    if (! $user->first_name && ! $user->last_name) {
                        return '-';
                    }

                    return trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: '-';
                }),

            Column::make('Date', 'order_date')
                ->sortable()
                ->format(fn ($value) => $value->format('d/m/Y')),

            Column::make('Statut', 'status')
                ->sortable()
                ->format(function ($value) {
                    $badgeClass = match ($value) {
                        OrderStatus::CONFIRMED() => 'bg-primary',
                        OrderStatus::PROCESSING() => 'bg-info',
                        OrderStatus::DELIVERED() => 'bg-success',
                        OrderStatus::CANCELLED() => 'bg-danger',
                        default => 'bg-secondary',
                    };

                    return new HtmlString(
                        '<span class="badge '.$badgeClass.'">'.e($value->label).'</span>'
                    );
                }),

            Column::make('Action', 'id')
                ->format(function ($value, $row) {
                    return new HtmlString(
                        '<a href="'.route('orders.details', $row->id).'" class="btn btn-sm btn-info"><i class="bi bi-eye"></i> Détails</a>'
                    );
                }),
        ];
    }

    public function filters(): array
    {
        $statusOptions = [];
        $labels = OrderStatus::labels();
        $values = OrderStatus::values();

        foreach ($labels as $key => $label) {
            if (isset($values[$key])) {
                $statusOptions[$values[$key]] = $label;
            }
        }

        return [
            TextFilter::make('N° Commande')
                ->config(['placeholder' => 'Rechercher un numéro...'])
                ->filter(function (Builder $builder, string $value) {
                    $builder->where('order_number', 'like', '%'.$value.'%');
                }),

            SelectFilter::make('Statut')
                ->options(array_merge(['' => 'Tous'], $statusOptions))
                ->filter(function (Builder $builder, string $value) {
                    if ($value === '') {
                        return $builder;
                    }

                    return $builder->where('status', $value);
                }),

            DateRangeFilter::make('Période de date de commande')
                ->config([
                    'locale' => 'fr',
                    'altFormat' => 'd/m/Y',
                ])
                ->filter(function (Builder $builder, array $dateRange) {
                    $builder->whereBetween('order_date', [$dateRange['minDate'].' 00:00:00', $dateRange['maxDate'].' 23:59:59']);
                }),
        ];
    }

    public function builder(): Builder
    {
        return Order::query()
            ->with([
                'customer.user',
                'distributionCenter',
                'deliveryPerson.user',
                'items.product.bottle.bottleType',
                'items.product.accessory.accessoryType',
            ]);
    }
}
