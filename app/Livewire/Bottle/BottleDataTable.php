<?php

namespace App\Livewire\Bottle;

use App\Enums\BottleStatus;
use App\Models\Bottle;
use App\Models\DistributionCenter;
use App\Models\User;
use App\Services\Bottle\BottleService;
use HarroldWafo\LaravelCustomDatatable\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
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

            Column::make('Type de bouteille', 'bottle_type_id')
                ->sortable()
                ->searchable(function (Builder $query, string $searchTerm) {
                    return $query->whereHas('bottleType', function (Builder $q) use ($searchTerm) {
                        $q->where('name', 'like', '%'.$searchTerm.'%');
                    });
                })
                ->format(function ($value, $row) {
                    return optional($row->bottleType)->name ?? '-';
                }),

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
                        view('components.bottle-actions', ['bottle' => $row])->render()
                    );
                }),
        ];
    }

    /**
     * Get authorized distribution center options for the current user
     */
    protected function getDistributionCenterOptions(): array
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            return ['' => 'Tous'];
        }

        $centerIds = $user->distributionCenters()->pluck('distribution_center_id')->toArray();
        $centers = DistributionCenter::whereIn('id', $centerIds)->orderBy('name')->get();

        $options = ['' => 'Tous'];

        foreach ($centers as $center) {
            $options[$center->id] = $center->name;
        }

        return $options;
    }

    public function filters(): array
    {
        return [
            SelectFilter::make('Centre de distribution')
                ->options($this->getDistributionCenterOptions())
                ->filter(function (Builder $builder, string $value) {
                    if ($value === '') {
                        return $builder;
                    }

                    return $builder->where('distribution_center_id', $value);
                }),

            TextFilter::make('Type de bouteille')
                ->config(['placeholder' => 'Rechercher un type...'])
                ->filter(function (Builder $builder, string $value) {
                    $builder->whereHas('bottleType', function (Builder $q) use ($value) {
                        $q->where('name', 'like', '%'.$value.'%');
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
                'bottleType',
                'distributionCenter',
            ]);

        /** @var User|null $user */
        $user = Auth::user();

        $centerIds = $user->distributionCenters()->pluck('distribution_center_id')->toArray();
        if (! empty($centerIds)) {
            $query->whereIn('distribution_center_id', $centerIds);
        }

        return $query;
    }

    protected function customMapAttributes()
    {
        return [
            'bottle_type_name' => function ($row) {
                return optional($row->bottleType)->name ?? '-';
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
        $bottleService = app(BottleService::class);

        $bottle = $bottleService->find($bottleId);
        if ($bottle) {
            $bottleService->updateStatus($bottleId, BottleStatus::from($status));
            $this->dispatch('refreshDatatable');
            session()->flash('success', 'Le status de la bouteille a été modifié avec succès!');
        }
    }
}
