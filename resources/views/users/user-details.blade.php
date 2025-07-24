@extends('layout.master')
@section('title', 'Détails de l\'utilisateur')
@section('css')
    <!-- Data Table css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/datatable/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/datatable/datatable2/buttons.dataTables.min.css') }}">
@endsection
@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb start -->
        <div class="row m-1">
            <div class="col-12 ">
                <h4 class="main-title">Détails de l'utilisateur</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="#" class="f-s-14 f-w-500">
                            <span>
                                <i class="ph-duotone  ph-stack f-s-16"></i> Apps
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('users.list') }}" class="f-s-14 f-w-500">Utilisateurs</a>
                    </li>
                    <li class="active">
                        <a href="#" class="f-s-14 f-w-500">Détails de l'utilisateur</a>
                    </li>
                </ul>
            </div>
        </div>
        <!-- Breadcrumb end -->

        <!-- User Details start -->
        <div class="row order-details">
            <div class="col-xxl-10">
                <div class="row">
                    <!-- User Profile start -->
                    <div class="col-lg-4">
                        <div class="card order-details-card">
                            <div class="card-body">
                                <div class="profile-container">
                                    <div class="image-details">
                                        <div class="profile-image"
                                            style="background-image: url({{ $user->getFirstMediaUrl('images') ?: asset('build/assets/28-DUtk996K.jpg') }});"></div>
                                        <div class="profile-pic">
                                            <div class="avatar-upload">
                                                <div class="avatar-edit">
                                                    <input type="file" id="imageUpload"
                                                        accept=".png, .jpg, .jpeg">
                                                    <label for="imageUpload"><i class="ti ti-photo-heart"></i></label>
                                                </div>
                                                <div class="avatar-preview">
                                                    <div id="imgPreview">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="person-details">
                                        <h5 class="f-w-600">{{ $user->full_name }}
                                            @if($user->email_verified_at)
                                                <img src="{{ asset('assets/images/profile-app/01.png') }}" class="w-20 h-20"
                                                    alt="verified">
                                            @endif
                                        </h5>
                                        <p>{{ $user->roles->pluck('name')->join(', ') ?: 'Aucun rôle assigné' }}</p>

                                        <div class="my-2">
                                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary b-r-22">
                                                <i class="ti ti-edit"></i> Modifier
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- User Profile end -->

                    <!-- User Details start -->
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5>Détails de l'utilisateur</h5>
                            </div>
                            <div class="card-body">
                                <table class="project-details-table table table-borderless align-middle mb-0">
                                    <tbody>
                                        <tr>
                                            <td>
                                                <p class="f-w-600 mb-0">Nom complet</p>
                                            </td>
                                            <td class="text-end">
                                                {{ $user->full_name }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <p class="f-w-600 mb-0">Prénom</p>
                                            </td>
                                            <td class="text-end">
                                                {{ $user->first_name }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <p class="f-w-600 mb-0">Nom</p>
                                            </td>
                                            <td class="text-end">
                                                {{ $user->last_name }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <p class="f-w-600 mb-0">Email</p>
                                            </td>
                                            <td class="text-end">
                                                {{ $user->email }}
                                                @if($user->email_verified_at)
                                                    <span class="badge text-light-success ms-1">Vérifié</span>
                                                @else
                                                    <span class="badge text-light-warning ms-1">Non vérifié</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <p class="f-w-600 mb-0">Téléphone</p>
                                            </td>
                                            <td class="text-end">
                                                {{ $user->phone_number ?: 'Non renseigné' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <p class="f-w-600 mb-0">Adresse</p>
                                            </td>
                                            <td class="text-end">
                                                {{ $user->address ?: 'Non renseignée' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <p class="f-w-600 mb-0">Rôle(s)</p>
                                            </td>
                                            <td class="text-end">
                                                @if($user->roles->count() > 0)
                                                    @foreach($user->roles as $role)
                                                        <span class="badge text-light-info">{{ $role->name }}</span>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">Aucun rôle assigné</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <p class="f-w-600 mb-0">Centre(s) de distribution</p>
                                            </td>
                                            <td class="text-end">
                                                @if($user->distributionCenters->count() > 0)
                                                    @foreach($user->distributionCenters as $center)
                                                        <span class="badge text-light-secondary">{{ $center->name }}</span>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">Aucun centre assigné</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <p class="f-w-600 mb-0">Date de création</p>
                                            </td>
                                            <td class="text-end">
                                                <span class="text-primary">{{ $user->created_at->format('d M Y à H:i') }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <p class="f-w-600 mb-0">Dernière modification</p>
                                            </td>
                                            <td class="text-end">
                                                <span class="text-info">{{ $user->updated_at->format('d M Y à H:i') }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <p class="f-w-600 mb-0">Statut</p>
                                            </td>
                                            <td class="text-end">
                                                @if($user->is_active)
                                                    <span class="badge text-light-success">Actif</span>
                                                @else
                                                    <span class="badge text-light-danger">Inactif</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- User Details end -->

                </div>

            </div>

        </div>
        <!-- User Details end -->
    </div>
@endsection

@section('script')
    <!--customizer-->
    <div id="customizer"></div>

    <!-- datatable js -->
    <script src="{{ asset('assets/vendor/datatable/jquery.dataTables.min.js') }}"></script>

@endsection
