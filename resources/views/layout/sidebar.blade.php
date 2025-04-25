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
                    <i class="iconoir-home-alt"></i> Tableau de bord
                </a>
            </li>
            <li>
                <a aria-expanded="false" class="" data-bs-toggle="collapse" href="#analytics">
                    <i class="iconoir-dashboard"></i>
                    Suivi et Rapports
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
                    <i class="iconoir-user-square"></i>
                    Utilisateurs
                </a>
                <ul class="collapse" id="users">
                    <li><a href="{{route('users.create')}}"><i class="iconoir-plus"></i> Ajouter</a></li>
                    <li><a href="{{route('users.list')}}"><i class="iconoir-list"></i> Lister</a></li>
                </ul>
            </li>
            <li>
                <a aria-expanded="false" class="" data-bs-toggle="collapse" href="#bottles">
                    <i class="iconoir-cylinder"></i>
                    Bouteilles
                </a>
                <ul class="collapse" id="bottles">
                    <li><a href="{{route('dashboard')}}"><i class="iconoir-plus"></i> Ajouter</a></li>
                    <li><a href="{{route('dashboard')}}"><i class="iconoir-list"></i> Lister</a></li>
                </ul>
            </li>
            <li>
                <a aria-expanded="false" class="" data-bs-toggle="collapse" href="#point-of-sales">
                    <i class="iconoir-home-sale"></i>
                    Points de distribution
                </a>
                <ul class="collapse" id="point-of-sales">
                    <li><a href="{{route('warehouses.create')}}"><i class="iconoir-plus"></i> Ajouter</a></li>
                    <li><a href="{{route('warehouses.list')}}"><i class="iconoir-list"></i> Lister</a></li>
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
