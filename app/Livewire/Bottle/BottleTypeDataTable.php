<?php

namespace App\Livewire\Bottle;

use App\DTOs\BottleType\UpdateBottleTypeDTO;
use App\Models\BottleType;
use App\Services\BottleType\BottleTypeService;
use HarroldWafo\LaravelCustomDatatable\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filters\DateRangeFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\TextFilter;

class BottleTypeDataTable extends BaseDataTable
{
    protected $model = BottleType::class;

    protected const DEFAULT_SORT_FIELD = 'name';
    protected const DEFAULT_SORT_DIRECTION = 'asc';

    protected $listeners = ['refreshComponent' => '$refresh'];

    protected function getExportFileName(): string
    {
        return 'types_de_bouteilles';
    }

    public function columns(): array
    {
        return [
            Column::make('Nom', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Capacité', 'capacity')
                ->sortable()
                ->searchable(),

            Column::make('Hauteur', 'height')
                ->sortable(),

            Column::make('Poids', 'weight')
                ->sortable(),

            Column::make('Rayon', 'radius')
                ->sortable(),

            Column::make('Prix du contenu', 'content_price')
                ->sortable(),

            Column::make('Prix bouteille + contenu', 'bottle_with_content_price')
                ->sortable(),

            Column::make('Actif', 'is_active')
                ->sortable()
                ->format(function ($value) {
                    $badgeClass = $value ? 'text-bg-success' : 'text-bg-danger';
                    $text = $value ? 'Oui' : 'Non';

                    return new HtmlString('<span class="badge '.$badgeClass.'">'.e($text).'</span>');
                }),

            Column::make('Date de création', 'created_at')
                ->sortable()
                ->format(fn ($value) => $value->format('d/m/Y H:i')),

            Column::make('Action', 'id')
                ->format(function ($value, $row) {
                    return new HtmlString(
                        view('partials.bottle-types.actions', ['bottleType' => $row])->render()
                    );
                }),
        ];
    }

    public function filters(): array
    {
        return [
            TextFilter::make('Nom du type')
                ->config(['placeholder' => 'Rechercher par nom...'])
                ->filter(function (Builder $builder, string $value) {
                    $builder->where('name', 'like', '%'.$value.'%');
                }),

            SelectFilter::make('Status')
                ->options([
                    '' => 'Tous',
                    '1' => 'Actif',
                    '0' => 'Inactif',
                ])
                ->filter(function (Builder $builder, string $value) {
                    if ($value !== '') {
                        $builder->where('is_active', (bool) $value);
                    }
                }),

            DateRangeFilter::make('Période de date de création')
                ->config([
                    'locale' => 'fr',
                    'altFormat' => 'd/m/Y',
                ])
                ->filter(function (Builder $builder, array $dateRange) {
                    $builder->whereBetween('created_at', [$dateRange['minDate'].' 00:00:00', $dateRange['maxDate'].' 23:59:59']);
                }),
        ];
    }

    public function builder(): Builder
    {
        return BottleType::query();
    }

    protected function customMapAttributes()
    {
        return [
            'is_active_formatted' => function ($row) {
                return $row->is_active ? 'Oui' : 'Non';
            },
            'created_at_formatted' => function ($row) {
                return $row->created_at ? $row->created_at->format('d/m/Y H:i') : '-';
            },
        ];
    }

    /**
     * Dispatches an event to show the edit modal for a specific bottle type.
     */
    public function showEditBottleTypeModal(int $bottleTypeId)
    {
        $this->dispatch('showEditBottleTypeModal', $bottleTypeId);
    }

    /**
     * Toggles the active status of a bottle type.
     */
    public function toggleBottleTypeStatus(int $bottleTypeId, bool $isActive)
    {
        try {
            $bottleTypeService = app(BottleTypeService::class);
            /** @var BottleType $bottleType */
            $bottleType = $bottleTypeService->find($bottleTypeId);

            $updateDto = new UpdateBottleTypeDTO(
                name: $bottleType->name,
                capacity: $bottleType->capacity,
                content_price: $bottleType->content_price,
                bottle_with_content_price: $bottleType->bottle_with_content_price,
                weight: $bottleType->weight,
                is_active: $isActive
            );

            $result = $bottleTypeService->update($bottleType, $updateDto->toArrayFiltered());

            if ($result) {
                $status = $isActive ? 'activé' : 'désactivé';
                $name = $bottleType->name;

                session()->flash('success', "Le type de bouteille a été {$status} avec succès.");

                $this->dispatch('show-notification', [
                    'type' => 'success',
                    'title' => 'Statut modifié !',
                    'message' => "Le type de bouteille {$name} a été {$status} avec succès.",
                    'timer' => 3000,
                ]);

                $this->dispatch('refreshComponent');
            }
        } catch (\Exception $e) {
            Log::error('Error toggling bottle type status: '.$e->getMessage());

            $this->dispatch('show-notification', [
                'type' => 'error',
                'title' => 'Erreur !',
                'message' => "Une erreur s'est produite lors de la modification du statut du type de bouteille.",
                'timer' => 3000,
            ]);
        }
    }

    /**
     * Handles the deletion of a bottle type.
     */
    public function deleteBottleType(int $bottleTypeId)
    {
        try {
            $bottleTypeService = app(BottleTypeService::class);
            /** @var BottleType */
            $bottleType = $bottleTypeService->find($bottleTypeId);
            $name = $bottleType->name;
            $result = $bottleTypeService->delete($bottleType);

            if ($result) {
                session()->flash('success', "Le type de bouteille {$name} a été supprimé avec succès.");

                $this->dispatch('show-notification', [
                    'type' => 'success',
                    'title' => 'Type de bouteille supprimé !',
                    'message' => "Le type de bouteille {$name} a été supprimé définitivement.",
                    'timer' => 3000,
                ]);

                $this->dispatch('refreshComponent');
            }
        } catch (\Exception $e) {
            Log::error('Error deleting bottle type: '.$e->getMessage());

            $this->dispatch('show-notification', [
                'type' => 'error',
                'title' => 'Erreur !',
                'message' => "Une erreur s'est produite lors de la suppression du type de bouteille.",
                'timer' => 3000,
            ]);
        }
    }
}
