<div>
    <div class="row mb-3">
        <div class="col-md-6">
            <input type="text" class="form-control" placeholder="Rechercher une municipalité..." wire:model.live.debounce.300ms="search">
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('municipalities.create') }}" class="btn btn-primary">Nouvelle Municipalité</a>
        </div>
    </div>

    @if($municipalities->isEmpty())
        <div class="alert alert-info">Aucune municipalité trouvée.</div>
    @else
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Ville</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($municipalities as $municipality)
                        <tr>
                            <td>{{ $municipality->id }}</td>
                            <td>{{ $municipality->name }}</td>
                            <td>{{ $municipality->city->name ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('municipalities.edit', $municipality->id) }}" class="btn btn-sm btn-info">Éditer</a>
                                <button wire:click="deleteMunicipality({{ $municipality->id }})" class="btn btn-sm btn-danger">Supprimer</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $municipalities->links() }}
    @endif
</div>