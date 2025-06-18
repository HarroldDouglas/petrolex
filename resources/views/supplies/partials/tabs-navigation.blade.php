<ul class="nav nav-tabs tab-light-primary" role="tablist" id="supply-tabs">
    <li class="nav-item" role="presentation">
        <a href="{{ route('supplies.edit', $supply->id) }}" 
           class="nav-link {{ request()->routeIs('supplies.edit') ? 'active' : '' }}">
            <i class="ti ti-edit pe-1 ps-1"></i>Modifier l'approvisionnement
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a href="{{ route('supplies.register-products', $supply->id) }}" 
           class="nav-link {{ request()->routeIs('supplies.register-products') ? 'active' : '' }}">
            <i class="ti ti-package pe-1 ps-1"></i>Enregistrement des produits
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a href="{{ route('supplies.scan-bottles', $supply->id) }}" 
           class="nav-link {{ request()->routeIs('supplies.scan-bottles') ? 'active' : '' }}">
            <i class="ti ti-barcode pe-1 ps-1"></i>Scan des codes-barres
        </a>
    </li>
</ul>
