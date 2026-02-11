<?php

namespace App\Livewire\Order;

use App\Enums\Currency;
use App\Enums\OrderStatus;
use App\Enums\ProductType;
use App\Models\DeliveryPerson;
use App\Models\Order;
use App\Models\User;
use App\Services\DistributionCenter\DistributionCenterService;
use HarroldWafo\LaravelCustomDatatable\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filters\DateRangeFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\MultiSelectFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\TextFilter;

class OrderDataTable extends BaseDataTable
{
    protected $model = Order::class;

    protected const DEFAULT_SORT_FIELD = 'order_date';
    protected const DEFAULT_SORT_DIRECTION = 'desc';

    public function configure(): void
    {
        parent::configure();

        $this->setDefaultSort('order_date', 'desc');
    }

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
                ->searchable()
                ->format(function ($value, $row) {
                    return $row->distributionCenter->name ?? '-';
                }),

            Column::make('Client', 'customer_id')
                ->sortable()
                ->searchable()
                ->format(function ($value, $row) {
                    $user = optional(optional($row->customer)->user);
                    if (! $user->first_name && ! $user->last_name) {
                        return '-';
                    }

                    return trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: '-';
                }),

            /*Column::make('Produits', 'id')
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
                }),*/

            Column::make('Total', 'total_amount')
                ->sortable()
                ->format(fn ($value) => Currency::from(config('countries.default_currency', 'XAF'))->format($value)),

            Column::make('Réf. Paiement', 'id')
                ->searchable()
                ->format(function ($value, $row) {
                    if ($row->payment) {
                        return new HtmlString(
                            '<span class="d-block">'.e($row->payment->payment_reference).'</span>'
                        );
                    }

                    return '-';
                }),

            Column::make('Livreur', 'delivery_person_id')
                ->sortable()
                ->searchable()
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
                ->format(function (OrderStatus $value) {
                    return new HtmlString(
                        '<span class="badge '.$value->getBadgeClass().'">'.e($value->label).'</span>'
                    );
                }),

            Column::make('Action', 'id')
                ->excludeFromColumnSelect()
                ->format(function ($value, $row) {
                    return new HtmlString(
                        '<a href="'.route('orders.details', $row->id).'" class="btn btn-sm btn-info"><i class="bi bi-eye"></i> Détails</a>'
                    );
                }),
        ];
    }

    /**
     * Get authorized distribution center options for the current user
     */
    protected function getDistributionCenterOptions(): array
    {
        $centers = DistributionCenterService::getForCurrentUser();

        $options = ['' => 'Tous'];

        foreach ($centers as $center) {
            $options[$center->id] = $center->name;
        }

        return $options;
    }

    /**
     * Get delivery person options for the filter.
     * Assumes a DeliveryPerson model exists and has a 'user' relationship.
     */
    protected function getDeliveryPersonOptions(): array
    {
        $deliveryPersons = DeliveryPerson::with('user')->get();

        $options = ['' => 'Tous les livreurs'];

        foreach ($deliveryPersons as $deliveryPerson) {
            if ($deliveryPerson->user) {
                $fullName = trim(($deliveryPerson->user->first_name ?? '').' '.($deliveryPerson->user->last_name ?? ''));
                $options[$deliveryPerson->id] = $fullName ?: 'Livreur Inconnu (ID: '.$deliveryPerson->id.')';
            }
        }

        return $options;
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
            SelectFilter::make('Centre de distribution')
                ->options($this->getDistributionCenterOptions())
                ->filter(function (Builder $builder, string $value) {
                    if ($value === '') {
                        return $builder;
                    }

                    return $builder->where('distribution_center_id', $value);
                }),

            SelectFilter::make('Livreur')
                ->options($this->getDeliveryPersonOptions())
                ->filter(function (Builder $builder, string $value) {
                    if ($value === '') {
                        return $builder;
                    }

                    return $builder->where('delivery_person_id', $value);
                }),

            SelectFilter::make('Type de bouteille')
                ->options([
                    '' => 'Tous les types',
                    'full' => 'Incluant consignes + recharges',
                    'recharge' => 'Incluant recharges',
                ])
                ->filter(function (Builder $builder, string $value) {
                    if ($value === '') {
                        return $builder;
                    }

                    if ($value === 'full') {
                        // orders having at least one full bottle
                        return $builder->whereHas('items', function (Builder $query) {
                            $query->where('bottle_type', 'bottle_with_content');
                        });
                    }

                    if ($value === 'recharge') {
                        // orders having at least one recharge
                        return $builder->whereHas('items', function (Builder $query) {
                            $query->where('bottle_type', 'content');
                        });
                    }

                    return $builder;
                }),

            TextFilter::make('N° Commande')
                ->config(['placeholder' => 'Rechercher un numéro...'])
                ->filter(function (Builder $builder, string $value) {
                    $builder->where('order_number', 'like', '%'.$value.'%');
                }),

            MultiSelectFilter::make('Statut')
                ->options($statusOptions)
                ->filter(function (Builder $builder, array $values) {
                    if (empty($values)) {
                        return $builder;
                    }

                    return $builder->whereIn('status', $values);
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
        $query = Order::query()
            ->with([
                'customer.user',
                'distributionCenter',
                'deliveryPerson.user',
                'items.productCategory',
                'payment',
            ]);

        $centers = DistributionCenterService::getForCurrentUser();
        $centerIds = $centers->pluck('id')->toArray();

        if (! empty($centerIds)) {
            $query->whereIn('distribution_center_id', $centerIds);
        }

        return $query;
    }

    /**
     * Override getExportQuery to export all filtered records when no selection is made
     */
    protected function getExportQuery(): Builder
    {
        $selected = $this->getSelected();

        // Si des éléments sont sélectionnés, exporter uniquement ceux-là
        if (! empty($selected)) {
            $query = $this->model::whereIn('id', $selected);

            if ($this->sorts && count($this->sorts) > 0) {
                foreach ($this->sorts as $column => $direction) {
                    $query->orderBy($column, $direction);
                }
            }

            return $query;
        }

        // Sinon, exporter toutes les commandes avec les filtres appliqués
        return $this->builder();
    }

    /**
     * Custom mapping for export to avoid field conflicts
     */
    protected function customMapAttributesForExport(): array
    {
        return [
            'order_number' => 'N° commande',
            'distribution_center_id' => 'Centre de distr.',
            'customer_id' => 'Client',
            'total_amount' => 'Total',
            'payment.payment_reference' => 'Réf. Paiement',
            'delivery_person_id' => 'Livreur',
            'order_date' => 'Date',
            'status' => 'Statut',
        ];
    }

    /**
     * Custom formatters for export (text-only, no HTML)
     */
    protected function getExportFormatters(): array
    {
        return [
            'order_number' => fn ($value) => $value ?? '-',
            'distribution_center_id' => fn ($value, $row) => $row->distributionCenter->name ?? '-',
            'customer_id' => function ($value, $row) {
                $user = optional(optional($row->customer)->user);
                if (! $user->first_name && ! $user->last_name) {
                    return '-';
                }

                return trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: '-';
            },
            'total_amount' => fn ($value) => $value ? Currency::from(config('countries.default_currency', 'XAF'))->format($value) : '-',
            'payment.payment_reference' => fn ($value) => $value ?? '-',
            'delivery_person_id' => function ($value, $row) {
                if (! $row->deliveryPerson) {
                    return '-';
                }
                $user = optional($row->deliveryPerson->user);
                if (! $user->first_name && ! $user->last_name) {
                    return '-';
                }

                return trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: '-';
            },
            'order_date' => fn ($value) => $value ? $value->format('d/m/Y') : '-',
            'status' => fn (OrderStatus $value) => $value->label,
        ];
    }
}
