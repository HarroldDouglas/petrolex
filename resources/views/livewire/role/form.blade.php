<div>
    <form wire:submit.prevent="submit">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="name">Nom du Rôle</label>
                <input class="form-control @error('name') is-invalid @enderror" id="name" type="text" 
                       wire:model.defer="name" placeholder="ex: custom_manager">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <div class="form-text">Utilisez des lettres minuscules, chiffres et underscores uniquement.</div>
            </div>

            <div class="col-md-6">
                <label class="form-label" for="guard_name">Guard</label>
                <select class="form-select @error('guard_name') is-invalid @enderror" id="guard_name" wire:model.defer="guard_name">
                    <option value="web">Web</option>
                    <option value="api">API</option>
                </select>
                @error('guard_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-12">
                <label class="form-label">Permissions</label>
                
                @if(!empty($availablePermissions))
                    <div class="permissions-grid" style="max-height: 400px; overflow-y: auto;">
                        @foreach($availablePermissions as $moduleData)
                            <div class="card mb-3">
                                <div class="card-header py-2">
                                    <h6 class="card-title mb-0">{{ $moduleData['module'] }}</h6>
                                </div>
                                <div class="card-body py-2">
                                    <div class="row">
                                        @foreach($moduleData['permissions'] as $permission)
                                            <div class="col-md-3 col-sm-6 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           id="perm_{{ $permission['name'] }}"
                                                           wire:model.defer="selectedPermissions"
                                                           value="{{ $permission['name'] }}">
                                                    <label class="form-check-label small" for="perm_{{ $permission['name'] }}">
                                                        {{ $permission['label'] }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        Aucune permission disponible.
                    </div>
                @endif
                
                @error('selectedPermissions') 
                    <span class="text-danger text-sm d-block">{{ $message }}</span>
                @enderror
            </div>

            <div class="col-12">
                <div class="mt-4 d-flex justify-content-end gap-2 flex-column flex-sm-row text-end">
                    <a href="{{ route('roles.list') }}" class="btn btn-light-danger">
                        <i class="ti ti-x"></i> Annuler
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="ti ti-check"></i> {{ $role ? 'Mettre à jour' : 'Créer le rôle' }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>