@extends('layout.master')
@section('title', 'Order List')
@section('css')

@endsection
@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb start -->
        <div class="row m-1">
            <div class="col-12 ">
                <h4 class="main-title"> liste des utilisateurs</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="#" class="f-s-14 f-w-500">
                      <span>
                        <i class="ph-duotone  ph-stack f-s-16"></i> Apps
                      </span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="f-s-14 f-w-500">Utilisateurs</a>
                    </li>
                    <li class="active">
                        <a href="#" class="f-s-14 f-w-500">Liste des utilisateurs</a>
                    </li>
                </ul>
            </div>
        </div>
        <!-- Breadcrumb end -->

        <div class="row">
            <div class="card">
                <div class="card-body p-0">
                    <!-- table -->
                    <div class="table-responsive">
                        <table class="table table-bottom-border align-middle mb-0">
                            <thead>
                            <tr>
                                <th class="text-start w-20">Nom complet</th>
                                <th class="w-10">Point de distribution</th>
                                <th class="w-10">Téléphone</th>
                                <th class="w-15">Fonction</th>
                                <th class="w-15">Date</th>
                                <th class="w-5">Status</th>
                                <th class="w-25">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="d-flex align-items-center gap-2">
                                        <div class="h-25 w-25 d-flex-center b-r-50 overflow-hidden text-bg-primary">
                                            <img src="{{ asset('../assets/images/avtar/1.png') }}" alt="" class="img-fluid">
                                        </div>
                                        <span class="title-text mb-0">Jean Dupont</span>
                                    </td>
                                    <td>Point A</td>
                                    <td>+237 690 123 456</td>
                                    <td>Responsable point de distribution</td>
                                    <td>10 Avr,2024 08:30</td>
                            <td><span class="badge text-light-info">ACTIF</span></td>
                                    <td>
                                        <a href="{{ route('users.details', 2) }}" target="_blank" class="btn btn-light-primary icon-btn w-30 h-30 b-r-22 me-1"><i class="ti ti-eye"></i></a>
                                        <button class="btn btn-light-success icon-btn w-30 h-30 b-r-22 me-1" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><i class="ti ti-edit"></i></button>
                                        <button class="btn btn-light-danger icon-btn w-30 h-30 b-r-22 delete-btn"><i class="ti ti-trash"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="d-flex align-items-center gap-2">
                                        <div class="h-25 w-25 d-flex-center b-r-50 overflow-hidden text-bg-primary">
                                            <img src="{{ asset('../assets/images/avtar/2.png') }}" alt="" class="img-fluid">
                                        </div>
                                        <span class="title-text mb-0">Marie Ndongo</span>
                                    </td>
                                    <td>Point B</td>
                                    <td>+237 698 345 221</td>
                                    <td>Responsable point de distribution</td>
                                    <td>11 Avr,2024 09:45</td>
                                    <td><span class="badge text-light-danger">INACTIF</span></td>
                                    <td>
                                        <a href="{{ route('users.details', 3) }}" target="_blank" class="btn btn-light-primary icon-btn w-30 h-30 b-r-22 me-1"><i class="ti ti-eye"></i></a>
                                        <button class="btn btn-light-success icon-btn w-30 h-30 b-r-22 me-1" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><i class="ti ti-edit"></i></button>
                                        <button class="btn btn-light-danger icon-btn w-30 h-30 b-r-22 delete-btn"><i class="ti ti-trash"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="d-flex align-items-center gap-2">
                                        <div class="h-25 w-25 d-flex-center b-r-50 overflow-hidden text-bg-primary">
                                            <img src="{{ asset('../assets/images/avtar/3.png') }}" alt="" class="img-fluid">
                                        </div>
                                        <span class="title-text mb-0">Alain Kamdem</span>
                                    </td>
                                    <td>Point C</td>
                                    <td>+237 677 890 456</td>
                                    <td>Responsable point de distribution</td>
                                    <td>12 Avr,2024 11:00</td>
                                    <td><span class="badge text-light-info">ACTIF</span></td>
                                    <td>
                                        <a href="{{ route('users.details', 4) }}" target="_blank" class="btn btn-light-primary icon-btn w-30 h-30 b-r-22 me-1"><i class="ti ti-eye"></i></a>
                                        <button class="btn btn-light-success icon-btn w-30 h-30 b-r-22 me-1" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><i class="ti ti-edit"></i></button>
                                        <button class="btn btn-light-danger icon-btn w-30 h-30 b-r-22 delete-btn"><i class="ti ti-trash"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="d-flex align-items-center gap-2">
                                        <div class="h-25 w-25 d-flex-center b-r-50 overflow-hidden text-bg-primary">
                                            <img src="{{ asset('../assets/images/avtar/4.png') }}" alt="" class="img-fluid">
                                        </div>
                                        <span class="title-text mb-0">Fatou Diop</span>
                                    </td>
                                    <td>Point D</td>
                                    <td>+237 655 789 123</td>
                                    <td>Responsable point de distribution</td>
                                    <td>13 Avr,2024 14:15</td>
                                    <td><span class="badge text-light-danger">INACTIF</span></td>
                                    <td>
                                        <a href="{{ route('users.details', 5) }}" target="_blank" class="btn btn-light-primary icon-btn w-30 h-30 b-r-22 me-1"><i class="ti ti-eye"></i></a>
                                        <button class="btn btn-light-success icon-btn w-30 h-30 b-r-22 me-1" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><i class="ti ti-edit"></i></button>
                                        <button class="btn btn-light-danger icon-btn w-30 h-30 b-r-22 delete-btn"><i class="ti ti-trash"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="d-flex align-items-center gap-2">
                                        <div class="h-25 w-25 d-flex-center b-r-50 overflow-hidden text-bg-primary">
                                            <img src="{{ asset('../assets/images/avtar/5.png') }}" alt="" class="img-fluid">
                                        </div>
                                        <span class="title-text mb-0">Jacques Mba</span>
                                    </td>
                                    <td>Point D</td>
                                    <td>+237 699 654 789</td>
                                    <td>Responsable point de distribution</td>
                                    <td>14 Avr,2024 10:30</td>
                                    <td><span class="badge text-light-info">ACTIF</span></td>
                                    <td>
                                        <a href="{{ route('users.details', 6) }}" target="_blank" class="btn btn-light-primary icon-btn w-30 h-30 b-r-22 me-1"><i class="ti ti-eye"></i></a>
                                        <button class="btn btn-light-success icon-btn w-30 h-30 b-r-22 me-1" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><i class="ti ti-edit"></i></button>
                                        <button class="btn btn-light-danger icon-btn w-30 h-30 b-r-22 delete-btn"><i class="ti ti-trash"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="d-flex align-items-center gap-2">
                                        <div class="h-25 w-25 d-flex-center b-r-50 overflow-hidden text-bg-primary">
                                            <img src="{{ asset('../assets/images/avtar/6.png') }}" alt="" class="img-fluid">
                                        </div>
                                        <span class="title-text mb-0">Céline Ngono</span>
                                    </td>
                                    <td>Point E</td>
                                    <td>+237 675 888 999</td>
                                    <td>Responsable point de distribution</td>
                                    <td>15 Avr,2024 16:45</td>
                                    <td><span class="badge text-light-danger">INACTIF</span></td>
                                    <td>
                                        <a href="{{ route('users.details', 7) }}" target="_blank" class="btn btn-light-primary icon-btn w-30 h-30 b-r-22 me-1"><i class="ti ti-eye"></i></a>
                                        <button class="btn btn-light-success icon-btn w-30 h-30 b-r-22 me-1" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><i class="ti ti-edit"></i></button>
                                        <button class="btn btn-light-danger icon-btn w-30 h-30 b-r-22 delete-btn"><i class="ti ti-trash"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="d-flex align-items-center gap-2">
                                        <div class="h-25 w-25 d-flex-center b-r-50 overflow-hidden text-bg-primary">
                                            <img src="{{ asset('../assets/images/avtar/7.png') }}" alt="" class="img-fluid">
                                        </div>
                                        <span class="title-text mb-0">Michel Ekoume</span>
                                    </td>
                                    <td>Point F</td>
                                    <td>+237 662 222 333</td>
                                    <td>Responsable point de distribution</td>
                                    <td>16 Avr,2024 09:00</td>
                                    <td><span class="badge text-light-info">ACTIF</span></td>
                                    <td>
                                        <a href="{{ route('users.details', 8) }}" target="_blank" class="btn btn-light-primary icon-btn w-30 h-30 b-r-22 me-1"><i class="ti ti-eye"></i></a>
                                        <button class="btn btn-light-success icon-btn w-30 h-30 b-r-22 me-1" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><i class="ti ti-edit"></i></button>
                                        <button class="btn btn-light-danger icon-btn w-30 h-30 b-r-22 delete-btn"><i class="ti ti-trash"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="d-flex align-items-center gap-2">
                                        <div class="h-25 w-25 d-flex-center b-r-50 overflow-hidden text-bg-primary">
                                            <img src="{{ asset('../assets/images/avtar/8.png') }}" alt="" class="img-fluid">
                                        </div>
                                        <span class="title-text mb-0">Brigitte Owono</span>
                                    </td>
                                    <td>Point G</td>
                                    <td>+237 699 888 444</td>
                                    <td>Responsable point de distribution</td>
                                    <td>17 Avr,2024 15:30</td>
                                    <td><span class="badge text-light-danger">INACTIF</span></td>
                                    <td>
                                        <a href="{{ route('users.details', 9) }}" target="_blank" class="btn btn-light-primary icon-btn w-30 h-30 b-r-22 me-1"><i class="ti ti-eye"></i></a>
                                        <button class="btn btn-light-success icon-btn w-30 h-30 b-r-22 me-1" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><i class="ti ti-edit"></i></button>
                                        <button class="btn btn-light-danger icon-btn w-30 h-30 b-r-22 delete-btn"><i class="ti ti-trash"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="d-flex align-items-center gap-2">
                                        <div class="h-25 w-25 d-flex-center b-r-50 overflow-hidden text-bg-primary">
                                            <img src="{{ asset('../assets/images/avtar/9.png') }}" alt="" class="img-fluid">
                                        </div>
                                        <span class="title-text mb-0">Eric Fokou</span>
                                    </td>
                                    <td>Point H</td>
                                    <td>+237 699 321 789</td>
                                    <td>Responsable point de distribution</td>
                                    <td>18 Avr,2024 13:20</td>
                                    <td><span class="badge text-light-info">ACTIF</span></td>
                                    <td>
                                        <a href="{{ route('users.details', 10) }}" target="_blank" class="btn btn-light-primary icon-btn w-30 h-30 b-r-22 me-1"><i class="ti ti-eye"></i></a>
                                        <button class="btn btn-light-success icon-btn w-30 h-30 b-r-22 me-1" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><i class="ti ti-edit"></i></button>
                                        <button class="btn btn-light-danger icon-btn w-30 h-30 b-r-22 delete-btn"><i class="ti ti-trash"></i></button>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>
                    <!-- table -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Modifier un utilisateur</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form class="app-form">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Nom</label>
                                <input type="text" class="form-control" placeholder="Nom" id="last_name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">Prénom</label>
                                <input type="text" class="form-control" placeholder="Prénom" id="first_name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="text" class="form-control" placeholder="email@example.com" id="email">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Téléphone</label>
                                <input type="text" class="form-control" placeholder="690102030" id="phone">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="point_vente" class="form-label">Point de distribution</label>
                                <select class="form-select" id="point_vente">
                                    <option value="Point A">Point A</option>
                                    <option value="Point B">Point B</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="statut" class="form-label">Statut</label>
                                <select class="form-select" id="statut">
                                    <option value="actif">Actif</option>
                                    <option value="inactif">Inactif</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer px-4">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-primary">Modifier</button>
                </div>
        </div>
    </div>

@endsection

@section('script')
<!--customizer-->
<div id="customizer"></div>

<!-- js-->
<script src="{{ asset('assets/js/orders_list.js') }}"></script>

@endsection
