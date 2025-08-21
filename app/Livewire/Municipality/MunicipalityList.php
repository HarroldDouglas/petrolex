<?php

namespace App\Livewire\Municipality;

use App\Models\Geography\Municipality;
use App\Services\Geography\MunicipalityService;
use Livewire\Component;
use Livewire\WithPagination;

class MunicipalityList extends Component
{
    use WithPagination;

    public string $search = '';

    protected MunicipalityService $municipalityService;

    protected $queryString = [
        'search' => ['except' => '', 'as' => 's'],
    ];

    public function boot(MunicipalityService $municipalityService)
    {
        $this->municipalityService = $municipalityService;
    }

    public function render()
    {
        $municipalities = Municipality::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'. $this->search .'%');
            })
            ->paginate(10);

        return view('livewire.municipality.municipality-list', [
            'municipalities' => $municipalities,
        ]);
    }

    public function deleteMunicipality(int $municipalityId)
    {
        $municipality = $this->municipalityService->find($municipalityId);
        $this->municipalityService->deleteMunicipality($municipality);

        session()->flash('success', 'Municipalité supprimée avec succès.');
    }
}
