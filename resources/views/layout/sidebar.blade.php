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
            <li class="menu-title">
                <span>Accueil</span>
            </li>
            <li class="no-sub">
                <a class="" href="{{ route('dashboard') }}">
                    <i class="iconoir-home-alt"></i> Tableau de bord
                </a>
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
                <a aria-expanded="false" class="" data-bs-toggle="collapse" href="#point-of-sales">
                    <i class="iconoir-home-sale"></i>
                    Points de vente
                </a>
                <ul class="collapse" id="point-of-sales">
                    <li><a href="{{route('dashboard')}}"><i class="iconoir-plus"></i> Ajouter</a></li>
                    <li><a href="{{route('dashboard')}}"><i class="iconoir-list"></i> Lister</a></li>
                </ul>
            </li>
            <li>
                <a aria-expanded="false" class="" data-bs-toggle="collapse" href="#point-of-sales">
                    <i class="iconoir-cylinder"></i>
                    Bouteilles
                </a>
                <ul class="collapse" id="point-of-sales">
                    <li><a href="{{route('dashboard')}}"><i class="iconoir-plus"></i> Ajouter</a></li>
                    <li><a href="{{route('dashboard')}}"><i class="iconoir-list"></i> Lister</a></li>
                </ul>
            </li>
            <li>
                <a aria-expanded="false" class="" data-bs-toggle="collapse" href="#financial-page">
                    <i class="iconoir-money-square"></i>
                    Rapports Financiers
                </a>
                <ul class="collapse" id="financial-page">
                    <li><a href="{{route('dashboard')}}"><i class="iconoir-plus"></i> Ajouter</a></li>
                    <li><a href="{{route('dashboard')}}"><i class="iconoir-list"></i> Lister</a></li>
                </ul>
            </li>
            <li>
                <a aria-expanded="false" class="" data-bs-toggle="collapse" href="#orders-page">
                    <i class="iconoir-page"></i>
                    Suvivi des commandes Globales
                </a>
                <ul class="collapse" id="orders-page">
                    <li><a href="{{route('dashboard')}}"><i class="iconoir-plus"></i> Ajouter</a></li>
                    <li><a href="{{route('dashboard')}}"><i class="iconoir-list"></i> Lister</a></li>
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
