<ul class="nav nav-tabs tab-light-primary flex-nowrap overflow-auto" role="tablist" id="supply-tabs">
    <li class="nav-item" role="presentation">
        <a href="{{ route('supplies.edit', $supply->id) }}"
           class="nav-link text-nowrap {{ request()->routeIs('supplies.edit') ? 'active' : '' }}">
            <i class="ti ti-edit pe-1 ps-1"></i><span class="d-none d-sm-inline">Modifier l'approvisionnement</span><span class="d-sm-none">Modifier</span>
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a href="{{ route('supplies.register-products', $supply->id) }}"
           class="nav-link text-nowrap {{ request()->routeIs('supplies.register-products') ? 'active' : '' }}">
            <i class="ti ti-package pe-1 ps-1"></i><span class="d-none d-sm-inline">Enregistrement des produits</span><span class="d-sm-none">Produits</span>
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a href="{{ route('supplies.scan-bottles', $supply->id) }}"
           class="nav-link text-nowrap {{ request()->routeIs('supplies.scan-bottles') ? 'active' : '' }}">
            <i class="ti ti-barcode pe-1 ps-1"></i><span class="d-none d-sm-inline">Scan des codes-barres</span><span class="d-sm-none">Scan</span>
        </a>
    </li>
</ul>
