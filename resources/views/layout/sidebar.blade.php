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

            @canany([$permissionEnum::ORDERS_VIEW()->value])
                <li class="no-sub">
                    <a class="" href="{{ route('orders.list') }}">
                        <i class="iconoir-cart-alt"></i>
                        {{ auth()->user()->role == \App\Enums\UserRole::CENTER_MANAGER() ? 'Mes Commandes' : 'Commandes' }}
                    </a>
                </li>
            @endcanany

            @canany([$permissionEnum::SUPPLIER_DELIVERIES_VIEW()->value,
                $permissionEnum::SUPPLIER_DELIVERIES_CREATE()->value])
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

            @can($permissionEnum::PRODUCTS_VIEW()->value)
                <li class="no-sub">
                    <a class="" href="{{ route('products.verify-categories') }}">
                        <i class="iconoir-check-circle"></i> Vérifier les catégories
                    </a>
                </li>
            @endcan

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

            @canany([$permissionEnum::ROLES_VIEW()->value,
                $permissionEnum::ROLES_CREATE()->value])
                <li>
                    <a aria-expanded="false" class="" data-bs-toggle="collapse" href="#roles">
                        <i class="iconoir-shield-check"></i> Rôles et permissions
                    </a>
                    <ul class="collapse" id="roles">
                        @can($permissionEnum::ROLES_VIEW()->value)
                            <li><a href="{{ route('roles.list') }}"> Liste</a></li>
                        @endcan
                        @can($permissionEnum::ROLES_CREATE()->value)
                            <li><a href="{{ route('roles.create') }}"> Nouveau</a></li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            @canany([$permissionEnum::DISTRIBUTION_CENTERS_VIEW()->value,
                $permissionEnum::DISTRIBUTION_CENTERS_CREATE()->value,
                $permissionEnum::DISTRIBUTION_CENTER_MANAGE_OWN()->value])
                <li>
                    <a aria-expanded="false" class="" data-bs-toggle="collapse" href="#point-of-sales">
                        <i class="iconoir-network"></i>
                        {{ auth()->user()->hasRole('center_manager') ? 'Mon Centre' : 'Centres de distribution' }}
                    </a>
                    <ul class="collapse" id="point-of-sales">
                        @canany([$permissionEnum::DISTRIBUTION_CENTERS_VIEW()->value, $permissionEnum::DISTRIBUTION_CENTER_MANAGE_OWN()->value])
                            <li>
                                <a href="{{ route('distribution-centers.list') }}">
                                    {{ auth()->user()->hasRole('center_manager') ? 'Voir mon centre' : 'Liste' }}
                                </a>
                            </li>
                        @endcanany
                        @can($permissionEnum::DISTRIBUTION_CENTERS_CREATE()->value)
                            <li><a href="{{ route('distribution-centers.create') }}"> Nouveau</a></li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            @canany([$permissionEnum::MUNICIPALITIES_VIEW()->value,
                $permissionEnum::MUNICIPALITIES_CREATE()->value])
                <li>
                    <a aria-expanded="false" class="" data-bs-toggle="collapse" href="#municipalities">
                        <i class="iconoir-building"></i> Municipalités
                    </a>
                    <ul class="collapse" id="municipalities">
                        @can($permissionEnum::MUNICIPALITIES_VIEW()->value)
                            <li><a href="{{ route('municipalities.index') }}"> Liste</a></li>
                        @endcan
                        @can($permissionEnum::MUNICIPALITIES_CREATE()->value)
                            <li><a href="{{ route('municipalities.create') }}"> Nouveau</a></li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            @canany([$permissionEnum::MUNICIPALITIES_VIEW()->value,
                $permissionEnum::MUNICIPALITIES_CREATE()->value])
                <li>
                    <a aria-expanded="false" class="" data-bs-toggle="collapse" href="#neighborhoods">
                        <i class="iconoir-map-pin"></i> Quartiers
                    </a>
                    <ul class="collapse" id="neighborhoods">
                        @can($permissionEnum::MUNICIPALITIES_VIEW()->value)
                            <li><a href="{{ route('neighborhoods.index') }}"> Liste</a></li>
                        @endcan
                        @can($permissionEnum::MUNICIPALITIES_CREATE()->value)
                            <li><a href="{{ route('neighborhoods.create') }}"> Nouveau</a></li>
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
