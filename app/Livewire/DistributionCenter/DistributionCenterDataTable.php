<?php

namespace App\Livewire\DistributionCenter;

use App\DTOs\DistributionCenter\UpdateDistributionCenterDTO;
use App\Models\DistributionCenter;
use App\Services\DistributionCenter\DistributionCenterService;
use HarroldWafo\LaravelCustomDatatable\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use Rappasoft\LaravelLivewireTables\Views\Column;

// TODO: we should add column rating(note moyenne), which can be calculated from the orders table
class DistributionCenterDataTable extends BaseDataTable
{
    protected $model = DistributionCenter::class;

    protected const DEFAULT_SORT_FIELD = 'created_at';
    protected const DEFAULT_SORT_DIRECTION = 'desc';

    protected function getExportFileName(): string
    {
        return 'centres-distribution';
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')
                ->sortable()
                ->deselected(),

            Column::make('Nom', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Pays', 'country')
                ->sortable()
                ->searchable(),

            Column::make('Ville', 'city')
                ->sortable()
                ->searchable(),

            Column::make('Quartier', 'neighborhood')
                ->sortable()
                ->searchable(),

            Column::make('Adresse', 'address')
                ->sortable()
                ->searchable(),

            Column::make('Téléphone', 'phone')
                ->sortable()
                ->searchable(),

            Column::make('Date', 'created_at')
                ->sortable()
                ->format(fn ($value) => $value->format('d/m/Y')),

            Column::make('Statut', 'is_active')
                ->sortable()
                ->format(function ($value) {
                    $status = $value ? 'Actif' : 'Inactif';
                    $badgeClass = $value ? 'bg-success' : 'bg-danger';

                    return new HtmlString(
                        '<span class="badge '.$badgeClass.'">'.e($status).'</span>'
                    );
                }),

            Column::make('Actions')
                ->label(
                    function ($row) {
                        return view('partials.distribution-centers.actions', ['distributionCenter' => $row]);
                    }
                ),
        ];
    }

    public function builder(): Builder
    {
        return DistributionCenter::query();
    }

    protected function customMapAttributes()
    {
        return [
            'status' => function ($row) {
                return $row->is_active ? 'Actif' : 'Inactif';
            },
        ];
    }

    /**
     * Toggle the active status of a distribution center
     */
    public function toggleDistributionCenterStatus($distributionCenterId)
    {
        try {
            $distributionCenterService = app(DistributionCenterService::class);
            $distributionCenter = $distributionCenterService->find($distributionCenterId);

            $currentStatus = $distributionCenter->is_active;
            $newStatus = ! $currentStatus;

            $updateDto = new UpdateDistributionCenterDTO(
                is_active: $newStatus
            );

            $result = $distributionCenterService->update($distributionCenter, $updateDto);

            if ($result) {
                $status = $newStatus ? 'activé' : 'désactivé';
                $name = $distributionCenter->name;

                session()->flash('success', "Le centre de distribution a été {$status} avec succès.");

                $this->dispatch('show-notification', [
                    'type' => 'success',
                    'title' => 'Statut modifié !',
                    'message' => "Le centre de distribution {$name} a été {$status} avec succès.",
                    'timer' => 3000,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error toggling distribution center status: '.$e->getMessage());

            $this->dispatch('show-notification', [
                'type' => 'error',
                'title' => 'Erreur !',
                'message' => "Une erreur s'est produite lors de la modification du statut du centre de distribution.",
                'timer' => 3000,
            ]);
        }
    }
}
