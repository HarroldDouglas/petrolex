<div class="app-user-permissions-section">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Gérer les permissions de {{ $user->full_name }}</h4>
            <p class="text-muted mb-0">
                Rôle actuel: 
                @if($user->roles->count() > 0)
                    <span class="badge bg-primary">{{ \App\Enums\UserRole::tryFrom($user->roles->first()->name)?->label }}</span>
                @else
                    <span class="text-warning">Aucun rôle assigné</span>
                @endif
            </p>
        </div>
        <a href="{{ route('users.list') }}" class="btn btn-primary">
            <i class="ti ti-arrow-left me-2"></i> Retour
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Permissions utilisateur</h5>
            <div class="card-subtitle text-muted">
                <div class="d-flex gap-4 mt-2 align-items-center flex-wrap">
                    <div class="d-flex align-items-center gap-2">
                        <input type="checkbox" checked class="permission-checkbox checkbox-role" style="pointer-events: none;" readonly>
                        <span class="small">Hérité du rôle (avec opacité)</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <input type="checkbox" checked class="permission-checkbox checkbox-direct" style="pointer-events: none;" readonly>
                        <span class="small">Permission directe</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <input type="checkbox" class="permission-checkbox checkbox-revoked" style="pointer-events: none;" readonly>
                        <span class="small">Permission révoquée</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <input type="checkbox" class="permission-checkbox checkbox-none" style="pointer-events: none;" readonly>
                        <span class="small">Non assigné</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card-body">
            <form wire:submit.prevent="submit" class="app-form">
                @if(!empty($permissionStates))
                    <div class="permissions-container row">
                        @foreach($permissionStates as $module => $moduleData)
                            <div class="permission-group col-6 mb-4">
                                <div class="module-header">
                                    <h6 class="text-primary fw-bold mb-0">{{ $moduleData['module'] }}</h6>
                                </div>
                                
                                <div class="permissions-list">
                                    <!-- Module Select All Checkbox -->
                                    <div class="permission-item-wrapper mb-2 p-2">
                                        <div class="form-check d-flex align-items-center">
                                            <div class="custom-checkbox-wrapper me-3">
                                                @php 
                                                    $groupState = $this->getGroupSelectionState($module);
                                                @endphp
                                                <input class="form-check-input permission-checkbox checkbox-group" 
                                                       type="checkbox" 
                                                       id="group_{{ $module }}"
                                                       wire:click="toggleGroup('{{ $module }}')"
                                                       @if($groupState['isFullySelected']) checked @endif
                                                       @if($groupState['isPartiallySelected']) style="opacity: 0.5;" @endif>
                                            </div>
                                            <label class="form-check-label flex-grow-1" 
                                                   for="group_{{ $module }}"
                                                   style="cursor: pointer;">
                                                <span class="permission-label">
                                                    Sélectionner tout
                                                </span>
                                            </label>
                                        </div>
                                    </div>

                                    @foreach($moduleData['permissions'] as $permission)
                                        <div class="permission-item-wrapper mb-2 p-2">
                                            @php
                                                $currentState = $this->getCurrentPermissionState($permission['name']);
                                                $isSelected = in_array($permission['name'], $selectedPermissions);
                                            @endphp
                                            <div class="form-check d-flex align-items-center">
                                                <div class="custom-checkbox-wrapper me-3">
                                                    <input class="form-check-input permission-checkbox checkbox-{{ $currentState }}" 
                                                           type="checkbox" 
                                                           id="permission_{{ $permission['name'] }}"
                                                           value="{{ $permission['name'] }}"
                                                           wire:model.live="selectedPermissions"
                                                           @if($isSelected) checked @endif>
                                                </div>
                                                
                                                <label class="form-check-label flex-grow-1" 
                                                       for="permission_{{ $permission['name'] }}"
                                                       style="cursor: pointer;">
                                                    <span class="permission-label 
                                                        @if($currentState === \App\Enums\UserPermissionState::ROLE()->value) text-muted @endif
                                                        @if($currentState === \App\Enums\UserPermissionState::REVOKED()->value) text-decoration-line-through text-danger @endif">
                                                        {{ $permission['label'] }}
                                                    </span>
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
                        Aucune permission disponible pour cet utilisateur.
                    </div>
                @endif

                <!-- Form Actions -->
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('users.list') }}" class="btn btn-light-danger">
                        <i class="ti ti-x"></i> Annuler
                    </a>
                    <button type="submit" 
                            class="btn btn-success"
                            wire:loading.attr="disabled"
                            wire:target="submit">
                        <span wire:loading.remove wire:target="submit">
                            <i class="ti ti-device-floppy me-2"></i> 
                            Enregistrer                
                        </span>
                        <span wire:loading wire:target="submit">
                            <i class="ti ti-loader-2 me-2 spinner-border spinner-border-sm"></i> 
                            Mise à jour...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .permissions-container {
            max-height: 700px;
            overflow-y: auto;
        }

        .permission-item-wrapper {
            transition: all 0.2s ease;
        }

        .permission-item-wrapper:hover {
            background-color: #e3f2fd !important;
            border-color: #007bff !important;
        }

        .permission-label {
            font-size: 0.95rem;
            font-weight: 500;
            color: #495057;
        }

        .module-header h6 {
            color: #007bff;
            font-size: 1.1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .permissions-list {
            max-height: 600px;
            overflow-y: auto;
        }

        .card-subtitle {
            font-size: 0.85rem;
        }

        .app-user-permissions-section .form-check-label {
            margin-bottom: 0;
            cursor: pointer;
        }

        /* Custom checkbox wrapper */
        .custom-checkbox-wrapper {
            position: relative;
            display: inline-block;
        }

        .permission-checkbox {
            width: 1.2em;
            height: 1.2em;
            cursor: pointer;
            position: relative;
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            border-radius: 3px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            outline: none !important;
            box-shadow: none !important;
        }

        /* Role permission style - blue with opacity */
        .checkbox-role {
            opacity: 0.6 !important;
            border: 2px solid #696cff !important;
            background: white !important;
        }

        .checkbox-role:checked {
            background-color: #696cff !important;
            border-color: #696cff !important;
        }

        .checkbox-role:checked::after {
            content: '✓';
            color: white;
            font-weight: 900;
            font-size: 1.1em;
            line-height: 1;
            text-shadow: 0 0 1px rgba(0,0,0,0.3);
        }

        /* Direct permission style - blue check */
        .checkbox-direct {
            border: 2px solid #696cff !important;
            background: white !important;
        }

        .checkbox-direct:checked {
            background-color: #696cff !important;
            border-color: #696cff !important;
        }

        .checkbox-direct:checked::after {
            content: '✓';
            color: white;
            font-weight: 900;
            font-size: 1.1em;
            line-height: 1;
            text-shadow: 0 0 1px rgba(0,0,0,0.3);
        }

        /* Revoked permission style - red with cross */
        .checkbox-revoked {
            border: 2px solid #dc3545 !important;
            background-color: #dc3545 !important;
        }

        .checkbox-revoked::after {
            content: '✕';
            color: white;
            font-weight: 900;
            font-size: 0.8em;
            line-height: 1;
            text-shadow: 0 0 1px rgba(0,0,0,0.3);
        }

        .checkbox-revoked:checked {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
        }

        .checkbox-revoked:focus,
        .checkbox-revoked:active {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
            outline: none !important;
            box-shadow: none !important;
        }

        /* None permission style - empty square */
        .checkbox-none {
            border: 2px solid #dee2e6 !important;
            background: white !important;
        }

        .checkbox-none:checked {
            background-color: #696cff !important;
            border-color: #696cff !important;
        }

        .checkbox-none:checked::after {
            content: '✓';
            color: white;
            font-weight: 900;
            font-size: 1.1em;
            line-height: 1;
            text-shadow: 0 0 1px rgba(0,0,0,0.3);
        }

        /* Group selection checkbox style */
        .checkbox-group {
            border: 2px solid #696cff !important;
            background: white !important;
        }

        .checkbox-group:checked {
            background-color: #696cff !important;
            border-color: #696cff !important;
        }

        .checkbox-group:checked::after {
            content: '✓';
            color: white;
            font-weight: 900;
            font-size: 1.1em;
            line-height: 1;
            text-shadow: 0 0 1px rgba(0,0,0,0.3);
        }

        /* Partial selection style */
        .checkbox-group[style*="opacity: 0.5"] {
            border-color: #696cff !important;
            background-color: #696cff !important;
        }

        .checkbox-group[style*="opacity: 0.5"]::after {
            content: '−';
            color: white;
            font-weight: 900;
            font-size: 1.2em;
            line-height: 1;
            text-shadow: 0 0 1px rgba(0,0,0,0.3);
        }

        /* Loading state styles */
        [wire\:loading] .permission-checkbox {
            opacity: 0.6;
            cursor: not-allowed;
        }

        [wire\:loading\.delay] .permission-item-wrapper {
            opacity: 0.8;
        }

        /* Form loading styles */
        form[wire\:loading] {
            opacity: 0.7;
        }

        /* Dynamic state classes for labels */
        .label-state-role {
            color: #6c757d !important;
        }

        .label-state-direct {
            color: #495057 !important;
        }

        .label-state-revoked {
            text-decoration: line-through !important;
            color: #dc3545 !important;
        }

        .label-state-none {
            color: #495057 !important;
        }
    </style>

</div>