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
    public int $perPage = 20;

    protected MunicipalityService $municipalityService;

    protected $queryString = [
        'search' => ['except' => '', 'as' => 's'],
        'perPage' => ['except' => 20, 'as' => 'per_page'],
    ];

    public function boot(MunicipalityService $municipalityService)
    {
        $this->municipalityService = $municipalityService;
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $municipalities = Municipality::query()
            ->with('city') // Eager load city relationship to avoid N+1 queries
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhereHas('city', function ($subQuery) {
                        $subQuery->where('name', 'like', '%'.$this->search.'%');
                    });
            })
            ->orderBy('created_at', 'desc') // Show newest municipalities first
            ->paginate($this->perPage);

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
