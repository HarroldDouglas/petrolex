<?php

namespace App\Livewire;

use App\Enums\EntityStatus;
use App\Models\DistributionCenter;
use App\Models\User;
use HarroldWafo\LaravelCustomDatatable\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filters\DateFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

class UserDataTable extends BaseDataTable
{
    protected $model = User::class;

    protected const DEFAULT_SORT_FIELD = 'last_name';
    protected const DEFAULT_SORT_DIRECTION = 'asc';

    protected function getExportFileName(): string
    {
        return 'utilisateurs';
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

    public function builder(): Builder
    {
        return User::query()
            ->with(['roles', 'accessibleDistributionCenters']);
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')
                ->sortable()
                ->deselected(),

            Column::make('Nom', 'last_name')
                ->sortable()
                ->searchable(),

            Column::make('Prénom', 'first_name')
                ->sortable()
                ->searchable(),

            Column::make('Centres de distribution')
                ->sortable(function (Builder $query, $direction) {
                    return $query->leftJoin('distribution_center_user', 'users.id', '=', 'distribution_center_user.user_id')
                        ->leftJoin('distribution_centers', 'distribution_center_user.distribution_center_id', '=', 'distribution_centers.id')
                        ->groupBy('users.id')
                        ->orderBy(DB::raw('GROUP_CONCAT(distribution_centers.name ORDER BY distribution_centers.name ASC SEPARATOR ", ")'), $direction);
                })
                ->searchable(function (Builder $query, string $searchTerm) {
                    $query->whereHas('accessibleDistributionCenters', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', "%$searchTerm%");
                    });
                })
                ->label(function ($row) {
                    if ($row->isGlobal()) {
                        return '<span class="badge bg-primary">Global</span>';
                    }

                    $centers = $row->accessibleDistributionCenters->pluck('name')->filter()->join(', ');

                    return $centers ?: '-';
                })
                ->html(),

            Column::make('Téléphone', 'phone_number')
                ->sortable()
                ->searchable(),

            Column::make('Fonction')
                ->label(function ($row) {
                    return $row->roles
                        ->map(function ($role) {
                            return \App\Enums\UserRole::from($role->name)->label;
                        })
                        ->join(', ');
                })
                ->sortable(fn ($query, $direction) => $query->orderBy('id', $direction)
                )
                ->searchable(function (Builder $query, string $searchTerm) {
                    $query->whereHas('roles', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', "%$searchTerm%");
                    });
                }),

            Column::make('Statut', 'is_active')
                ->sortable()
                ->html()
                ->format(function ($value, $row) {
                    if ($value) {
                        return '<span class="badge text-outline-'.EntityStatus::ACTIVE()->badge().'">'.EntityStatus::ACTIVE()->label.'</span>';
                    } else {
                        return '<span class="badge text-outline-'.EntityStatus::INACTIVE()->badge().'">'.EntityStatus::INACTIVE()->label.'</span>';
                    }
                }),

            Column::make('Dernière connexion', 'last_login_at')
                ->sortable()
                ->format(function ($value) {
                    return $value ? $value->format('d/m/Y H:i') : '-';
                }),

            Column::make('Créé le', 'created_at')
                ->sortable()
                ->format(fn ($value) => $value->format('d/m/Y'))
                ->deselected(),

            Column::make('Actions')
                ->label(
                    function ($row) {
                        return view('components.user-actions', ['user' => $row]);
                    }
                ),
        ];
    }

    public function filters(): array
    {
        return [
            SelectFilter::make('Fonction', 'role')
                ->options(['' => 'Toutes les fonctions'] +
                    DB::table('roles')
                        ->pluck('name', 'id')
                        ->map(function ($role) {
                            return \App\Enums\UserRole::tryFrom($role)->label ?? $role;
                        })
                        ->toArray()
                )
                ->filter(function (Builder $builder, string $value) {
                    if ($value === '') {
                        return $builder;
                    }

                    return $builder->whereHas('roles', function ($query) use ($value) {
                        $query->where('roles.id', $value);
                    });
                }),

            SelectFilter::make('Centre de distribution', 'distribution_center')
                ->options((function () {
                    $options = ['' => 'Tous les centres'];
                    $options['global'] = 'Global';

                    $centers = DistributionCenter::orderBy('name')
                        ->pluck('name', 'id')
                        ->toArray();

                    return $options + $centers;
                })())
                ->filter(function (Builder $builder, string $value) {
                    if ($value === '') {
                        return $builder;
                    }

                    if ($value === 'global') {
                        return $builder->whereDoesntHave('accessibleDistributionCenters');
                    }

                    return $builder->whereHas('accessibleDistributionCenters', function ($query) use ($value) {
                        $query->where('distribution_centers.id', $value);
                    });
                }),

            SelectFilter::make('Statut', 'is_active')
                ->options([
                    '' => 'Tous',
                    '1' => EntityStatus::ACTIVE()->label,
                    '0' => EntityStatus::INACTIVE()->label,
                ])
                ->filter(function (Builder $builder, string $value) {
                    if ($value === '') {
                        return $builder;
                    }

                    return $builder->where('is_active', $value);
                }),

            DateFilter::make('Créé après', 'created_at')
                ->config([
                    'placeholder' => 'Date de création minimum',
                    'locale' => 'fr',
                ])
                ->filter(function (Builder $builder, string $value) {
                    $builder->whereDate('created_at', '>=', $value);
                }),
        ];
    }
}
