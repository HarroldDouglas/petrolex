<?php

namespace App\Livewire\Dashboard;

use App\Enums\OrderStatus;
use App\Enums\ProductType;
use App\Models\Order;
use HarroldWafo\LaravelCustomDatatable\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filters\DateFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\DateRangeFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\TextFilter;

class DashboardDataTable extends BaseDataTable
{
    protected $model = Order::class;

    protected const DEFAULT_SORT_FIELD = 'order_date';
    protected const DEFAULT_SORT_DIRECTION = 'desc';

    protected function getExportFileName(): string
    {
        return 'commandes';
    }

    public bool $rememberColumnSelection = true;
    public bool $rememberFilters = true;
    public bool $rememberSort = true;
    public bool $rememberPerPage = true;

    public function configure(): void
    {
        parent::configure();

        $this->setPrimaryKey('id')
            ->setTableWrapperAttributes([
                'class' => 'table-responsive',
            ])
            ->setTableAttributes([
                'class' => 'table table-striped table-hover',
            ])
            ->setTheadAttributes([
                'class' => 'table-light',
            ])
            ->setDefaultSort(self::DEFAULT_SORT_FIELD, self::DEFAULT_SORT_DIRECTION)
            ->setPerPageAccepted([10, 25, 50, 100])
            ->setPerPage(10);
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
        return [
            TextFilter::make('N° Commande')
                ->config(['placeholder' => 'Rechercher un numéro...'])
                ->filter(function (Builder $builder, string $value) {
                    $builder->where('order_number', 'like', '%'.$value.'%');
                }),

            SelectFilter::make('Statut')
                ->options(array_merge(['' => 'Tous'] + OrderStatus::labels()))
                ->filter(function (Builder $builder, string $value) {
                    if ($value === '') {
                        return $builder;
                    }

                    return $builder->where('status', $value);
                }),

            DateFilter::make('Date après')
                ->config([
                    'placeholder' => 'Date minimum',
                    'locale' => 'fr',
                ])
                ->filter(function (Builder $builder, string $value) {
                    $builder->whereDate('order_date', '>=', $value);
                }),

            DateRangeFilter::make('Période')
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

    protected function customMapAttributes()
    {
        return [
            'customer_name' => function ($row) {
                return optional($row->customer)->user->name ?? '-';
            },
            'distribution_center_name' => function ($row) {
                return optional($row->distributionCenter)->name ?? '-';
            },
            'delivery_person_name' => function ($row) {
                return optional($row->deliveryPerson)->user->name ?? '-';
            },
            'products' => function ($row) {
                $products = $row->products;
                if ($products->isEmpty()) {
                    return '-';
                }

                $productNames = [];
                foreach ($products as $product) {
                    $productName = '';
                    if ($product->product_type == ProductType::BOTTLE()) {
                        $productName = optional($product->bottleType)->name ?? 'Bouteille';
                    } elseif ($product->product_type == ProductType::ACCESSORY()) {
                        $productName = optional($product->accessoryType)->name ?? 'Accessoire';
                    }

                    if ($productName) {
                        $productNames[] = $productName.' (x'.$product->pivot->quantity.')';
                    }
                }

                return implode(', ', $productNames);
            },
            'status_formatted' => function ($row) {
                return $row->status->label;
            },
        ];
    }
}
