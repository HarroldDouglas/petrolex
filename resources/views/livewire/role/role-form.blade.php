<div class="app-role-section">
    <form wire:submit.prevent="submit" class="app-form">
        <div class="row">
            <!-- Role Name -->
            <div class="col-md-6 mb-3">
                <label for="name" class="form-label">Nom du rôle <span class="text-danger">*</span></label>
                <input type="text" 
                       class="form-control @error('name') is-invalid @enderror" 
                       id="name" 
                       wire:model.live="name" 
                       placeholder="Entrez le nom du rôle">
                @error('name') 
                    <div class="invalid-feedback">{{ $message }}</div> 
                @enderror
            </div>

            <!-- Guard Name -->
            <div class="col-md-6 mb-3">
                <label for="guard_name" class="form-label">Guard</label>
                <select class="form-select @error('guard_name') is-invalid @enderror" 
                        id="guard_name" 
                        wire:model.live="guard_name">
                    <option value="web">Web</option>
                    <option value="api">API</option>
                </select>
                @error('guard_name') 
                    <div class="invalid-feedback">{{ $message }}</div> 
                @enderror
            </div>
        </div>

        <!-- Permissions Section -->
        <div class="row mb-4">
            <div class="">
                <h5 class="mb-3">Permissions</h5>
                
                @if(!empty($availablePermissions))
                    <div class="permissions-container row">
                        @foreach($availablePermissions as $module => $moduleData)
                            <div class="permission-group col-6">
                                <!-- Group Header with Select All Checkbox -->
                                <div class="d-flex align-items-center justify-content-between @if($loop->even) p-3 pt-0 ps-2 @endif ">
                                    <h6 class="text-primary fw-bold mb-0 p-0">{{ $moduleData['module'] }}</h6>
                                </div>
                                
                                <!-- Permissions Grid -->
                                <div class="permissions-list">
                                    <div class="permission-item-wrapper mb-2 p-2">
                                        <div class="form-check d-flex align-items-center">
                                            <input class="form-check-input  me-3" 
                                                type="checkbox" 
                                                id="group_{{ $module }}"
                                                wire:click="toggleGroup('{{ $module }}')"
                                                @if($this->isGroupFullySelected($module)) checked @endif
                                                @if($this->isGroupPartiallySelected($module)) style="opacity: 0.5;" @endif>
                                            <label class="form-check-label small text-muted" for="group_{{ $module }}">
                                                Sélectionner
                                            </label>
                                        </div>
                                    </div>
                                    @foreach($moduleData['permissions'] as $permission)
                                        <div class="permission-item-wrapper mb-2 p-2">
                                            <div class="form-check d-flex align-items-center">
                                                <input class="form-check-input me-3" 
                                                       type="checkbox" 
                                                       value="{{ $permission['name'] }}" 
                                                       id="permission_{{ $permission['name'] }}"
                                                       wire:model.live="selectedPermissions">
                                                <label class="form-check-label flex-grow-1" for="permission_{{ $permission['name'] }}">
                                                    {{ $permission['label'] }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
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
                    <div class="text-danger mt-2">{{ $message }}</div> 
                @enderror
            </div>
        </div>

        <!-- Form Actions -->
        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('roles.list') }}" class="btn btn-light-danger">
                <i class="ti ti-x"></i> Annuler
            </a>
            <button type="submit" 
                    class="btn btn-success"
                    wire:loading.attr="disabled"
                    wire:target="submit">
                <span wire:loading.remove wire:target="submit">
                    <i class="ti ti-device-floppy me-2"></i> 
                    Enregistrer'               
                </span>
                <span wire:loading wire:target="submit">
                    <i class="ti ti-loader-2 me-2 spinner-border spinner-border-sm"></i> 
                    {{ isset($role) ? 'Modification...' : 'Création...' }}
                </span>
            </button>
        </div>
    </form>
    
    <style>
        .permissions-container {
            max-height: 600px;
            overflow-y: auto;
        }

        .permission-item-wrapper {
            transition: all 0.2s ease;
        }

        .permission-item-wrapper:hover {
            background-color: #e3f2fd;
            border-color: #007bff !important;
        }

        .form-check-label {
            font-size: 0.95rem;
            color: #495057;
            cursor: pointer;
            font-weight: 500;
        }

        .form-check-input {
            width: 1.2em;
            height: 1.2em;
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: #007bff;
            border-color: #007bff;
        }

        .permission-group h6 {
            color: #007bff;
            font-size: 1.1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .group-checkbox {
            transform: scale(1.2);
        }

        .permissions-list {
            max-height: 700px;
            overflow: auto;
        }
    </style>
</div>