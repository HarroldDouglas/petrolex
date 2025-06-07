<div class="btn-group dropdown-icon-none">
    <button
        class="btn btn-light-primary icon-btn w-30 h-30 me-0 dropdown-toggle"
        type="button" id="dropdownMenuButton{{ $distributionCenter->id }}"
        data-bs-toggle="dropdown" aria-expanded="false">
        <i class="ti ti-dots-vertical"></i>
    </button>
    <ul class="dropdown-menu"
        aria-labelledby="dropdownMenuButton{{ $distributionCenter->id }}">
        <li>
            <a class="dropdown-item"
                href="{{ route('distribution-centers.details', $distributionCenter->id) }}">
                <i class="ti ti-eye text-primary me-2"></i> Détail
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="{{ route('distribution-centers.edit', $distributionCenter->id) }}">
                <i class="ti ti-edit text-success me-2"></i> Editer
            </a>
        </li>
        @if ($distributionCenter->is_active)
            <li>
                <a class="dropdown-item" href="#" onclick="confirmActionWithInput({
                    method: 'toggleDistributionCenterStatus',
                    parameters: [{{ $distributionCenter->id }}],
                    title: 'Désactiver le centre de distribution',
                    text: 'Vous êtes sur le point de désactiver ce centre de distribution. Cette action affectera toutes les fonctionnalités associées.',
                    confirmWord: 'desactiver',
                    entityName: 'Centre: {{ str_replace("'", "\\'", $distributionCenter->name) }}',
                    icon: 'warning',
                    confirmText: 'Oui, désactiver',
                    confirmButtonIcon: 'ti ti-ban',
                    actionInProgressText: 'Désactivation en cours...'
                }); return false;">
                    <i class="ti ti-ban text-warning me-2"></i> Désactiver
                </a>
            </li>
        @else
            <li>
                <a class="dropdown-item" href="#" onclick="confirmActionWithInput({
                    method: 'toggleDistributionCenterStatus',
                    parameters: [{{ $distributionCenter->id }}],
                    title: 'Activer le centre de distribution',
                    text: 'Vous êtes sur le point d\'activer ce centre de distribution.',
                    confirmWord: 'activer',
                    entityName: 'Centre: {{ str_replace("'", "\\'", $distributionCenter->name) }}',
                    icon: 'info',
                    confirmText: 'Oui, activer',
                    confirmButtonIcon: 'ti ti-check',
                    actionInProgressText: 'Activation en cours...'
                }); return false;">
                    <i class="ti ti-check text-success me-2"></i> Activer
                </a>
            </li>
        @endif
    </ul>
</div>
