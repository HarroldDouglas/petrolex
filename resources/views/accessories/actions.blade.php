<div class="btn-group dropdown-icon-none">
    <button class="btn btn-light-primary icon-btn w-30 h-30 me-0 dropdown-toggle" type="button"
        id="dropdownMenuButton{{ $product->id }}" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="ti ti-dots-vertical"></i>
    </button>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $product->id }}">
        <li>
            <a class="dropdown-item" href="{{ route('accessories.edit', $product->id) }}">
                <i class="iconoir-edit text-info me-2"></i> Modifier
            </a>
        </li>
        @if ($product->is_active)
            <li>
                <a class="dropdown-item" href="#"
                    onclick="confirmActionWithInput({
                    method: 'toggleAccessoryStatus',
                    parameters: [{{ $product->id }}],
                    componentId: '{{ $this->getId() }}',
                    title: 'Désactiver l\'accessoire',
                    text: 'Vous êtes sur le point de désactiver cet accessoire. Cette action affectera toutes les fonctionnalités associées.',
                    confirmWord: 'desactiver',
                    entityName: 'Accessoire: {{ str_replace("'", "\\'", $product->name) }}',
                    icon: 'warning',
                    confirmText: 'Oui, désactiver',
                    confirmButtonIcon: 'ti ti-ban',
                    actionInProgressText: 'Désactivation en cours...'
                }); return false;">
                    <i class="iconoir-xmark-circle text-warning me-2"></i>
                    Désactiver
                </a>
            </li>
        @else
            <li>
                <a class="dropdown-item" href="#"
                    onclick="confirmActionWithInput({
                    method: 'toggleAccessoryStatus',
                    parameters: [{{ $product->id }}],
                    componentId: '{{ $this->getId() }}',
                    title: 'Activer l\'accessoire',
                    text: 'Vous êtes sur le point d\'activer cet accessoire.',
                    confirmWord: 'activer',
                    entityName: 'Accessoire: {{ str_replace("'", "\\'", $product->name) }}',
                    icon: 'info',
                    confirmText: 'Oui, activer',
                    confirmButtonIcon: 'ti ti-check',
                    actionInProgressText: 'Activation en cours...'
                }); return false;">
                    <i class="iconoir-check-circle text-success me-2"></i>
                    Activer
                </a>
            </li>
        @endif
        <li>
            <a class="dropdown-item" href="#"
                onclick="confirmActionWithInput({
                method: 'deleteAccessory',
                parameters: [{{ $product->id }}],
                componentId: '{{ $this->getId() }}',
                title: 'Supprimer l\'accessoire',
                text: 'Vous êtes sur le point de supprimer définitivement cet accessoire.',
                confirmWord: 'supprimer',
                entityName: 'Accessoire: {{ str_replace("'", "\\'", $product->name) }}',
                icon: 'warning',
                confirmText: 'Supprimer définitivement',
                confirmButtonIcon: 'ti ti-trash',
                actionInProgressText: 'Suppression en cours...',
                id: {{ $product->id }}
            }); return false;">
                <i class="iconoir-trash text-danger me-2"></i> Supprimer
            </a>
        </li>
    </ul>
</div>
