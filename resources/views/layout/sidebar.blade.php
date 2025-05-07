<!-- Menu Navigation starts -->
<nav>
    <div class="app-logo">
        <a class="logo d-inline-block" href="{{ route('index') }}">
            <img src="{{ asset('../assets/images/logo/isogaz-no-bg.png') }}" alt="#">
        </a>

        <span class="bg-light-primary toggle-semi-nav">
            <i class="ti ti-chevrons-right f-s-20"></i>
        </span>
    </div>
    <div class="app-nav" id="app-simple-bar">
        <ul class="main-nav p-0 mt-2">
            <li class="no-sub">
                <a class="" href="{{ route('dashboard') }}">
                    <i class="iconoir-view-grid"></i> Tableau de bord
                </a>
            </li>
            <li>
                <a aria-expanded="false" class="" data-bs-toggle="collapse" href="#analytics">
                    <i class="iconoir-stats-up-square"></i> Suivi et Rapports
                </a>
                <ul class="collapse" id="analytics">
                    <li><a href="{{route('warehouse_stock_dashboard')}}"> Stock & Ventes</a></li>
                    <li><a href="{{route('stock_movement')}}"> Mouvement de stock</a></li>
                    <li><a href="{{route('order_report')}}"> Commandes & Livraisons</a></li>
                    <li><a href="{{route('financial_report')}}">Rapport financier</a></li>
                    <li><a href="{{route('transaction_report')}}">Transactions</a></li>
                </ul>
            </li>
            <li>
                <a aria-expanded="false" class="" data-bs-toggle="collapse" href="#users">
                    <i class="iconoir-user-badge-check"></i> Utilisateurs
                </a>
                <ul class="collapse" id="users">
                    <li><a href="{{route('users.create')}}">Ajouter</a></li>
                    <li><a href="{{route('users.list')}}">Lister</a></li>
                </ul>
            </li>
            <li>
                <a aria-expanded="false" class="" data-bs-toggle="collapse" href="#supply">
                    <i class="iconoir-database-restore"></i> Approvisionnements
                </a>
                <ul class="collapse" id="supply">
                    <li><a href="{{route('supplies.create')}}">Ajouter</a></li>
                    <li><a href="{{route('supplies.list')}}">Lister</a></li>
                </ul>
            </li>
            <li class="no-sub">
                <a class="" href="{{route('bottles.list')}}">
                    <i class="iconoir-cylinder"></i> Bouteilles
                </a>
            </li>
            <li>
                <a aria-expanded="false" class="" data-bs-toggle="collapse" href="#point-of-sales">
                    <i class="iconoir-home-simple-door"></i> Points de distribution
                </a>
                <ul class="collapse" id="point-of-sales">
                    <li><a href="{{route('warehouses.create')}}">Ajouter</a></li>
                    <li><a href="{{route('warehouses.list')}}">Lister</a></li>
                </ul>
            </li>
        </ul>
    </div>

    <div class="menu-navs">
        <span class="menu-previous"><i class="ti ti-chevron-left"></i></span>
        <span class="menu-next"><i class="ti ti-chevron-right"></i></span>
    </div>

</nav>
<!-- Menu Navigation ends -->
