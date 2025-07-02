<div>
    <form wire:submit.prevent="save" class="app-form">
        <div class="row">
            <div class="col-lg-3">
                <div class="card order-details-card h-380">
                    <div class="card-body">
                        <div class="profile-container">
                            <div class="image-details h-200">
                                <div class="profile-pic">
                                    <div class="avatar-upload">
                                        @if($this->isEditable)
                                        <div class="avatar-edit">
                                            <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                                id="image" wire:model="image" accept="image/jpeg,image/jpg,image/png,image/webp">
                    
                                            <label for="image"><i class="ti ti-photo-heart"></i></label>
                                        </div>
                                        @endif
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
                                    wire:model.live.debounce.500ms="last_name"
                                    @disabled(!$this->isEditable)>
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
                                    wire:model.live.debounce.500ms="first_name"
                                    @disabled(!$this->isEditable)>
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
                                    wire:model.live.debounce.500ms="email"
                                    @disabled(!$this->isEditable)>
                                @error('email')
                                    <div class="invalid-feedback">{{ __($message) }}</div>
                                @enderror
                            </div>
                    
                            <div class="col-md-6 mb-3">
                                <label for="phone_number" class="form-label">Téléphone</label>
                                <input type="text" 
                                    class="form-control @error('phone_number') is-invalid @enderror" 
                                    placeholder="690102030"
                                    id="phone_number"
                                    wire:model.live.debounce.500ms="phone_number"
                                    @disabled(!$this->isEditable)>
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
                                        wire:model.live.debounce.500ms="password"
                                        @disabled(!$this->isEditable)>
                                    <button class="btn btn-outline-secondary" type="button" wire:click="$toggle('showPassword')">
                                        <i class="ti ti-{{ $showPassword ? 'eye-off' : 'eye' }}"></i>
                                    </button>
                                    @error('password')
                                        <div class="invalid-feedback">{{ __($message) }}</div>
                                    @enderror
                                </div>
                            </div>
                            @if($this->isEditable)
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
                            @endif
                        </div>
                        
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </form>
</div>