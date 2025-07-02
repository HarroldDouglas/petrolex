<?php

namespace App\Livewire\Bottle;

use App\Enums\BottleStatus;
use App\Models\Bottle;
use App\Services\Bottle\BottleService;
use App\Services\DistributionCenter\DistributionCenterService;
use HarroldWafo\LaravelCustomDatatable\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filters\MultiSelectFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\TextFilter;

class BottleDataTable extends BaseDataTable
{
    protected $model = Bottle::class;
    protected const DEFAULT_SORT_FIELD = 'created_at';
    protected const DEFAULT_SORT_DIRECTION = 'desc';

    protected $listeners = ['refreshComponent' => '$refresh'];

    protected const IN_STOCK_EMPTY = 'in_stock_empty';
    protected const IN_STOCK_FILLED = 'in_stock_filled';

    protected function getExportFileName(): string
    {
        return 'bouteilles';
    }

    public function columns(): array
    {
        return [
            Column::make('Code-barre', 'barcode')
                ->sortable()
                ->searchable(),
            Column::make('Type de bouteille')
                ->sortable(function (Builder $query, string $direction) {
                    return $query->orderBy('product_category_id', $direction);
                })
                ->searchable(function (Builder $query, string $searchTerm) {
                    return $query->whereHas('product.productCategory', function (Builder $q) use ($searchTerm) {
                        $q->whereHas('productTypeInstance', function (Builder $q2) use ($searchTerm) {
                            $q2->where('name', 'like', '%'.$searchTerm.'%');
                        });
                    });
                })
                ->label(fn ($row, Column $column) => $row->product?->productCategory?->name ?? '-'),

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

            Column::make('Date d\'enregistrement', 'created_at')
                ->sortable()
                ->format(fn ($value) => $value->format('d/m/Y')),

            Column::make('État', 'status')
                ->sortable()
                ->format(function ($value, $row) {
                    $badgeClass = match ($value) {
                        BottleStatus::IN_STOCK() => $row->is_filled ? 'text-bg-success' : 'text-bg-primary',
                        BottleStatus::WITH_DELIVERY_PERSON() => 'text-bg-warning',
                        BottleStatus::WITH_CLIENT() => 'text-bg-info',
                        BottleStatus::LOST_STOLEN() => 'text-bg-danger',
                        BottleStatus::RETURNED_TO_SUPPLIER() => 'text-bg-dark',
                        default => 'text-bg-secondary',
                    };

                    $label = $value->label;
                    if ($value === BottleStatus::IN_STOCK()) {
                        $label .= $row->is_filled ? ' (Pleine)' : ' (Vide)';
                    }

                    return new HtmlString(
                        '<span class="badge '.$badgeClass.'">'.e($label).'</span>'
                    );
                }),

            Column::make('Action', 'id')
                ->format(function ($value, $row) {
                    return new HtmlString(
                        view('partials.bottles.actions', ['bottle' => $row])->render()
                    );
                }),
        ];
    }

    public function filters(): array
    {
        return [
            SelectFilter::make('Centre de distribution', 'distribution_center')
                ->options((function () {

                    $centers = DistributionCenterService::getForCurrentUser();

                    $options = ['' => 'Tous les centres'];

                    foreach ($centers as $center) {
                        $options[$center->id] = $center->name;
                    }

                    return $options;
                })())
                ->filter(function (Builder $builder, string $value) {
                    if ($value === '') {
                        return $builder;
                    }

                    return $builder->where('distribution_center_id', $value);
                }),

            TextFilter::make('Type de bouteille')
                ->config(['placeholder' => 'Rechercher un type...'])
                ->filter(function (Builder $builder, string $value) {
                    $builder->whereHas('product.productCategory', function (Builder $q) use ($value) {
                        $q->whereHas('productTypeInstance', function (Builder $q2) use ($value) {
                            $q2->where('name', 'like', '%'.$value.'%');
                        });
                    });
                }),

            MultiSelectFilter::make('Etat')
                ->options($this->getBottleStatusOptions())
                ->filter(function (Builder $builder, array $values) {
                    if (empty($values)) {
                        return;
                    }

                    $builder->where(function (Builder $query) use ($values) {
                        foreach ($values as $value) {
                            if ($value === self::IN_STOCK_EMPTY) {
                                // In stock (Empty)
                                $query->orWhere(function ($q) {
                                    $q->where('status', BottleStatus::IN_STOCK())
                                        ->where('is_filled', false);
                                });
                            } elseif ($value === self::IN_STOCK_FILLED) {
                                // In stock (Full)
                                $query->orWhere(function ($q) {
                                    $q->where('status', BottleStatus::IN_STOCK())
                                        ->where('is_filled', true);
                                });
                            } else {
                                $query->orWhere('status', $value);
                            }
                        }
                    });
                }),
        ];
    }

    /**
     * Generate options for the bottle status filter.
     */
    private function getBottleStatusOptions(): array
    {
        $options = [];

        foreach (BottleStatus::labels() as $value => $label) {
            if ($value === 'IN_STOCK') {
                $options[self::IN_STOCK_EMPTY] = 'En stock (Vides)';
                $options[self::IN_STOCK_FILLED] = 'En stock (Pleines)';
            } else {
                $options[$value] = $label;
            }
        }

        return $options;
    }

    public function builder(): Builder
    {
        $query = Bottle::query()
            ->with([
                'product.productCategory',
                'distributionCenter',
            ])
            ->join('products', 'bottles.product_id', '=', 'products.id')
            ->join('product_categories', 'products.product_category_id', '=', 'product_categories.id')
            ->select([
                'bottles.*',
                'product_categories.id as product_category_id',
            ]);

        $distributionCenters = DistributionCenterService::getForCurrentUser();

        $centerIds = $distributionCenters->pluck('id')->toArray();
        if (! empty($centerIds)) {
            $query->whereIn('bottles.distribution_center_id', $centerIds);
        }

        return $query;
    }

    protected function customMapAttributes()
    {
        return [
            'bottle_type_name' => function ($row) {
                return $row->product?->productCategory?->name ?? '-';
            },
            'status_formatted' => function ($row) {
                $label = $row->status->label ?? '-';
                if ($row->status === BottleStatus::IN_STOCK()) {
                    $label .= $row->is_filled ? ' (Pleine)' : ' (Vide)';
                }

                return $label;
            },
            'created_at_formatted' => function ($row) {
                return $row->created_at ? $row->created_at->format('d/m/Y') : '-';
            },
            'barcode' => function ($row) {
                return $row->barcode ?? '-';
            },
        ];
    }

    public function showBottleHistory($bottleId)
    {
        // Changed from direct parameter to named parameter array
        $this->dispatch('showBottleHistory', bottleId: $bottleId);
    }

    public function changeBottleStatus($bottleId, $status)
    {
        try {
            $bottleService = app(BottleService::class);
            $bottle = $bottleService->find($bottleId);

            if (! $bottle) {
                $this->dispatch('show-notification', [
                    'type' => 'error',
                    'title' => 'Erreur !',
                    'message' => "La bouteille sélectionnée n'existe pas.",
                    'timer' => 3000,
                ]);

                return;
            }

            $oldStatus = $bottle->status->label;
            $newStatus = BottleStatus::from($status);

            // TODO update this when we will remove updateStatus
            $bottleService->updateStatus($bottleId, $newStatus);

            // On vérifie que le statut a été mis à jour en récupérant à nouveau la bouteille
            $updatedBottle = $bottleService->find($bottleId);
            if ($updatedBottle && $updatedBottle->status === $newStatus) {
                $statusLabel = $newStatus->label;
                $barcode = $bottle->barcode;

                if ($newStatus === BottleStatus::LOST_STOLEN()) {
                    $message = "La bouteille {$barcode} a été déclarée perdue avec succès.";
                } elseif ($newStatus === BottleStatus::IN_STOCK()) {
                    $message = "La bouteille {$barcode} a été marquée comme retrouvée avec succès.";
                } else {
                    $message = "Le statut de la bouteille {$barcode} a été changé à '{$statusLabel}' avec succès.";
                }

                $this->dispatch('show-notification', [
                    'type' => 'success',
                    'title' => 'Succès !',
                    'message' => $message,
                    'timer' => 3000,
                ]);

                $this->dispatch('refreshDatatable');
            } else {
                $this->dispatch('show-notification', [
                    'type' => 'error',
                    'title' => 'Erreur !',
                    'message' => "Une erreur s'est produite lors de la modification du statut de la bouteille.",
                    'timer' => 3000,
                ]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error changing bottle status: '.$e->getMessage());

            $this->dispatch('show-notification', [
                'type' => 'error',
                'title' => 'Erreur !',
                'message' => "Une erreur s'est produite lors de la modification du statut de la bouteille : ".$e->getMessage(),
                'timer' => 3000,
            ]);
        }
    }
}
