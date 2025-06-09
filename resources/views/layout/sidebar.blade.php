<!-- Menu Navigation starts -->
<nav>
    <div class="app-logo">
        <a class="logo d-inline-block" href="/">
            <img src="{{ asset('../assets/images/logo/isogaz-no-bg.png') }}" alt="#">
        </a>
    </div>
    <div class="app-nav" id="app-simple-bar">
        <ul class="main-nav p-0 mt-2">
            @php
                $permissionEnum = \App\Enums\PermissionEnum::class;
            @endphp

            @can($permissionEnum::REPORTS_MANAGE()->value)
                <li class="no-sub">
                    <a class="" href="{{ route('dashboard') }}">
                        <i class="iconoir-view-grid"></i> Tableau de bord
                    </a>
                </li>
            @endcan

            @can($permissionEnum::REPORTS_MANAGE()->value)
                <li class="no-sub">
                    <a class="" href="{{ route('stock_movement') }}">
                        <i class="iconoir-data-transfer-both"></i> Mouvement de stock
                    </a>
                </li>
            @endcan

            @canany([$permissionEnum::SUPPLIER_DELIVERIES_VIEW()->value, $permissionEnum::SUPPLIER_DELIVERIES_CREATE()->value])
                <li>
                    <a aria-expanded="false" class="" data-bs-toggle="collapse" href="#supply">
                        <i class="iconoir-database-restore"></i> Approvisionnements
                    </a>
                    <ul class="collapse" id="supply">
                        @can($permissionEnum::SUPPLIER_DELIVERIES_VIEW()->value)
                            <li><a href="{{ route('supplies.list') }}"> Liste</a></li>
                        @endcan
                        @can($permissionEnum::SUPPLIER_DELIVERIES_CREATE()->value)
                            <li><a href="{{ route('supplies.create') }}"> Nouveau</a></li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            @can($permissionEnum::PRODUCTS_VIEW()->value)
                <li>
                    <a aria-expanded="false" class="" data-bs-toggle="collapse" href="#bottles">
                        <i class="iconoir-gas"></i>
                        Bouteilles
                    </a>
                    <ul class="collapse" id="bottles">
                        <li><a href="{{ route('bottles.index') }}"> Liste</a></li>
                        <li><a href="{{ route('bottles.types.index') }}"> Types</a></li>
                    </ul>
                </li>
            @endcan

            @canany([$permissionEnum::PRODUCTS_VIEW()->value, $permissionEnum::PRODUCTS_CREATE()->value])
                <li>
                    <a aria-expanded="false" class="" data-bs-toggle="collapse" href="#accessories">
                        <i class="iconoir-box-3d-point"></i>
                        Accessoires
                    </a>
                    <ul class="collapse" id="accessories">
                        @can($permissionEnum::PRODUCTS_VIEW()->value)
                            <li><a href="{{ route('accessories.index') }}"> Liste</a></li>
                        @endcan
                        @can($permissionEnum::PRODUCTS_CREATE()->value)
                            <li><a href="{{ route('accessories.create') }}"> Nouveau</a></li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            @canany([$permissionEnum::USERS_VIEW()->value, $permissionEnum::USERS_CREATE()->value])
                <li>
                    <a aria-expanded="false" class="" data-bs-toggle="collapse" href="#users">
                        <i class="iconoir-user"></i> Utilisateurs
                    </a>
                    <ul class="collapse" id="users">
                        @can($permissionEnum::USERS_VIEW()->value)
                            <li><a href="{{ route('users.list') }}"> Liste</a></li>
                        @endcan
                        @can($permissionEnum::USERS_CREATE()->value)
                            <li><a href="{{ route('users.create') }}"> Nouveau</a></li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            @canany([$permissionEnum::DISTRIBUTION_CENTERS_VIEW()->value, $permissionEnum::DISTRIBUTION_CENTERS_CREATE()->value])
                <li>
                    <a aria-expanded="false" class="" data-bs-toggle="collapse" href="#point-of-sales">
                        <i class="iconoir-network"></i> Centres de distribution
                    </a>
                    <ul class="collapse" id="point-of-sales">
                        @can($permissionEnum::DISTRIBUTION_CENTERS_VIEW()->value)
                            <li><a href="{{ route('distribution-centers.list') }}"> Liste</a></li>
                        @endcan
                        @can($permissionEnum::DISTRIBUTION_CENTERS_CREATE()->value)
                            <li><a href="{{ route('distribution-centers.create') }}"> Nouveau</a></li>
                        @endcan
                    </ul>
                </li>
            @endcanany
        </ul>
    </div>

    <div class="menu-navs">
        <span class="menu-previous"><i class="ti ti-chevron-left"></i></span>
        <span class="menu-next"><i class="ti ti-chevron-right"></i></span>
    </div>

</nav>
<!-- Menu Navigation ends -->
