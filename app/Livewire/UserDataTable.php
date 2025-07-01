<?php

namespace App\Livewire;

use App\DTOs\User\UpdateUserDTO;
use App\Enums\EntityStatus;
use App\Models\DistributionCenter;
use App\Models\User;
use App\Services\DistributionCenter\DistributionCenterService;
use App\Services\User\UserService;
use HarroldWafo\LaravelCustomDatatable\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

    public function builder(): Builder
    {
        $query = User::query()
            ->with(['roles', 'accessibleDistributionCenters', 'deliveryPerson']);

        $centers = DistributionCenterService::getForCurrentUser();
        $centerIds = $centers->pluck('id')->toArray();
        $allCenters = DistributionCenter::count();

        if (! empty($centerIds)) {
            // If user doesn't have access to all centers, only show users of their centers
            if ($centers->count() < $allCenters) {
                $query->whereHas('accessibleDistributionCenters', function ($subQ) use ($centerIds) {
                    $subQ->whereIn('distribution_centers.id', $centerIds);
                });
            } else {
                // User has access to all centers, show all users including global users
                $query->where(function ($q) use ($centerIds) {
                    $q->whereHas('accessibleDistributionCenters', function ($subQ) use ($centerIds) {
                        $subQ->whereIn('distribution_centers.id', $centerIds);
                    })
                        ->orWhereDoesntHave('accessibleDistributionCenters');
                });
            }
        }

        return $query;
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
                ->label(function (User $row) {
                    return match (true) {
                        $row->isDeliveryPerson() => view('components.user-actions-for-delivery-person', ['user' => $row]),
                        $row->isCustomer() => view('components.user-actions-for-customer', ['user' => $row]),
                        default => view('components.user-actions-for-global', ['user' => $row]),
                    };
                })
                ->html(),
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
                    $centers = DistributionCenterService::getForCurrentUser();
                    $allCenters = DistributionCenter::count();

                    $options = ['' => 'Tous les centres'];

                    if ($centers->count() >= $allCenters) {
                        $options['global'] = 'Global';
                    }

                    foreach ($centers as $center) {
                        $options[$center->id] = $center->name;
                    }

                    return $options;
                })())
                ->filter(function (Builder $builder, string $value) {
                    if ($value === '') {
                        return $builder;
                    }

                    if ($value === 'global') {
                        $totalCenters = DistributionCenter::count();

                        return $builder->whereHas('accessibleDistributionCenters', function ($query) {}, '=', $totalCenters);
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

    public function toggleUserStatus($userId)
    {
        try {
            $userService = app(UserService::class);
            $user = $userService->find($userId);
            $currentStatus = $user->is_active;
            $newStatus = ! $currentStatus;

            $updateDto = new UpdateUserDTO(
                is_active: $newStatus,
            );

            $result = $userService->update($user, $updateDto->toArrayFiltered());

            if ($result) {
                $status = $newStatus ? 'activé' : 'désactivé';
                $name = $user->full_name;

                session()->flash('success', "L'utilisateur a été {$status} avec succès.");

                $this->dispatch('show-notification', [
                    'type' => 'success',
                    'title' => 'Statut modifié !',
                    'message' => "L'utilisateur {$name} a été {$status} avec succès.",
                    'timer' => 3000,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error toggling user status: '.$e->getMessage());

            $this->dispatch('show-notification', [
                'type' => 'error',
                'title' => 'Erreur !',
                'message' => "Une erreur s'est produite lors de la modification du statut de l'utilisateur.",
                'timer' => 3000,
            ]);
        }
    }

    public function deleteUser($userId)
    {
        try {
            $userService = app(UserService::class);
            $user = $userService->find($userId);
            $name = $user->full_name;
            $result = $userService->delete($user);

            if ($result) {
                session()->flash('success', "L'utilisateur {$name} a été supprimé avec succès.");

                $this->dispatch('show-notification', [
                    'type' => 'success',
                    'title' => 'Utilisateur supprimé !',
                    'message' => "L'utilisateur {$name} a été supprimé définitivement.",
                    'timer' => 3000,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error deleting user: '.$e->getMessage());

            $this->dispatch('show-notification', [
                'type' => 'error',
                'title' => 'Erreur !',
                'message' => "Une erreur s'est produite lors de la suppression de l'utilisateur.",
                'timer' => 3000,
            ]);
        }
    }
}
