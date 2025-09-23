<div>
    <form wire:submit.prevent="save" class="app-form">
        <div class="row">
            <div class="col-lg-3">
                <div class="card order-details-card">
                    <div class="card-body">
                        <div class="profile-container">
                            <div class="image-details h-200">
                                <div class="profile-pic">
                                    <div class="avatar-upload">
                                        <div class="avatar-edit">
                                            <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                                id="image" wire:model="image" accept="image/jpeg,image/jpg,image/png,image/webp">
                    
                                            <label for="image"><i class="ti ti-photo-heart"></i></label>
                                        </div>
                                        <div class="avatar-preview">
                                            <div class="avatar-preview" style="{{ $this->imagePreviewStyle }}"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="person-details">
                                <h5 class="f-w-600">Photo de profil
                                    <img src="{{ asset('assets/images/profile-app/01.png') }}" class="w-20 h-20"
                                        alt="instagram-check-mark">
                                </h5>
                                <p>Ajouter une photo de profil</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-9 col-xxl-9">
                <div class="card">
                    <div class="card-body">
                        <div class="app-product-section">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Nom</label>
                                <input type="text" 
                                    class="form-control @error('last_name') is-invalid @enderror" 
                                    placeholder="Nom" 
                                    id="last_name"
                                    wire:model.live.debounce.500ms="last_name">
                                @error('last_name')
                                    <div class="invalid-feedback">{{ __($message) }}</div>
                                @enderror
                            </div>
                    
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">Prénom</label>
                                <input type="text" 
                                    class="form-control @error('first_name') is-invalid @enderror" 
                                    placeholder="Prénom" 
                                    id="first_name"
                                    wire:model.live.debounce.500ms="first_name">
                                @error('first_name')
                                    <div class="invalid-feedback">{{ __($message) }}</div>
                                @enderror
                            </div>
                    
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" 
                                    class="form-control @error('email') is-invalid @enderror" 
                                    placeholder="email@example.com"
                                    id="email"
                                    wire:model.live.debounce.500ms="email">
                                @error('email')
                                    <div class="invalid-feedback">{{ __($message) }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="country_code" class="form-label">Pays</label>
                                <select class="form-select @error('country_code') is-invalid @enderror" id="country_code" wire:model.live.debounce.500ms="country_code">
                                    <option value="">Sélectionner un pays</option>
                                    @foreach($countries as $code => $name)
                                        <option value="{{ $code }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('country_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                    
                            <div class="col-md-6 mb-3">
                                <label for="language" class="form-label">Langue</label>
                                <select class="form-select @error('language') is-invalid @enderror" id="language" wire:model.live.debounce.500ms="language">
                                    <option value="">Sélectionner une langue</option>
                                    @foreach($languages as $langCode => $langLabel)
                                        <option value="{{ $langCode }}">{{ $langLabel }}</option>
                                    @endforeach
                                </select>
                                @error('language')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="phone_number" class="form-label">Téléphone</label>
                                <input type="text" 
                                    class="form-control @error('phone_number') is-invalid @enderror" 
                                    placeholder="690102030"
                                    id="phone_number"
                                    wire:model.live.debounce.500ms="phone_number">
                                @error('phone_number')
                                    <div class="invalid-feedback">{{ __($message) }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Mot de passe</label>
                                <div class="input-group">
                                    <input type="{{ $showPassword ? 'text' : 'password' }}"
                                        class="form-control @error('password') is-invalid @enderror"
                                        placeholder="Mot de passe"
                                        id="password"
                                        wire:model.live.debounce.500ms="password">
                                    <button class="btn btn-outline-secondary" type="button" wire:click="$toggle('showPassword')">
                                        <i class="ti ti-{{ $showPassword ? 'eye-off' : 'eye' }}"></i>
                                    </button>
                                    @error('password')
                                        <div class="invalid-feedback">{{ __($message) }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="is_active" class="form-label">Statut</label>
                                <select class="form-select @error('is_active') is-invalid @enderror" 
                                        id="is_active"
                                        wire:model.live.debounce.500ms="is_active">
                                    <option value="{{ \App\Enums\EntityStatus::active()->value }}" selected>{{ \App\Enums\EntityStatus::active()->label }}</option>
                                    <option value="{{ \App\Enums\EntityStatus::inactive()->value }}">{{ \App\Enums\EntityStatus::inactive()->label }}</option>
                                </select>
                                @error('is_active')
                                    <div class="invalid-feedback">{{ __($message) }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="role" class="form-label">Fonction</label>

                                <select class="form-select @error('role') is-invalid @enderror" 
                                        id="role" 
                                        wire:model.live.debounce.500ms.live="role">
                                    <option value="">Sélectionner une fonction</option>
                                    @foreach($allowedRoles as $roleKey => $roleValue)
                                        <option value="{{ $roleKey }}">{{ $roleValue }}</option>
                                    @endforeach
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            @if($showDistributionCenters)
                            <div class="col-md-6 mb-3">
                                <label for="distribution_center_ids" class="form-label">Centre de distribution</label>
                                <div>
                                    <livewire:multiple-select
                                        :options="$availableDistributionCenters"
                                        :parent-event="'distribution-centers:selection-changed'"
                                        :selected-options="$distribution_center_ids ?? []"
                                        :wire:key="'distribution-centers-select'"
                                    />
                                </div>
                                @error('distribution_center_ids')
                                    <span class="text-danger text-sm d-block">{{ $message }}</span>
                                @enderror
                            </div>
                            @endif
                            <div class="col-12">
                                <div class="mt-4 d-flex justify-content-end gap-2 flex-column flex-sm-row text-end">
                                    <a href="{{ route('users.list') }}" class="btn btn-light-danger">
                                        <i class="ti ti-x"></i> Annuler
                                    </a>
                                    <button type="submit" class="btn btn-success" wire:loading.attr="disabled">
                                        <i class="ti ti-device-floppy"></i> 
                                        Enregistrer
                                        <span wire:loading wire:target="submit">...</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </form>
</div>