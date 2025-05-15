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
            <li class="no-sub">
                <a class="" href="{{ route('stock_movement') }}">
                    <i class="iconoir-data-transfer-both"></i> Mouvement de stock
                </a>
            </li>
            <li>
                <a aria-expanded="false" class="" data-bs-toggle="collapse" href="#users">
                    <i class="iconoir-user"></i> Utilisateurs
                </a>
                <ul class="collapse" id="users">
                    <li><a href="{{ route('users.create') }}"> Nouveau</a></li>
                    <li><a href="{{ route('users.list') }}"> Liste</a></li>
                </ul>
            </li>
            <li>
                <a aria-expanded="false" class="" data-bs-toggle="collapse" href="#supply">
                    <i class="iconoir-database-restore"></i> Approvisionnements
                </a>
                <ul class="collapse" id="supply">
                    <li><a href="{{ route('supplies.create') }}"> Nouveau</a></li>
                    <li><a href="{{ route('supplies.list') }}"> Liste</a></li>
                </ul>
            </li>
            <li>
                <a aria-expanded="false" class="" data-bs-toggle="collapse" href="#bottles">
                    <i class="iconoir-gas"></i>
                    Bouteilles
                </a>
                <ul class="collapse" id="bottles">
                    <li><a href="{{ route('bottles.list') }}"> Liste</a></li>
                    <li><a href="{{ route('bottles.types') }}"> Types</a></li>
                </ul>
            </li>
            <li>
                <a aria-expanded="false" class="" data-bs-toggle="collapse" href="#products">
                    <i class="iconoir-box-3d-point"></i>
                    Produits
                </a>
                <ul class="collapse" id="products">
                    <li><a href="{{ route('products.create') }}"> Nouveau</a></li>
                    <li><a href="{{ route('products.list') }}"> Liste</a></li>
                </ul>
            </li>
            <li>
                <a aria-expanded="false" class="" data-bs-toggle="collapse" href="#point-of-sales">
                    <i class="iconoir-network"></i> Points de distribution
                </a>
                <ul class="collapse" id="point-of-sales">
                    <li><a href="{{ route('warehouses.create') }}"> Nouveau</a></li>
                    <li><a href="{{ route('warehouses.list') }}"> Liste</a></li>
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
