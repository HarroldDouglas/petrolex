@extends('layout.master')
@section('title', 'Order Details')
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
                <h4 class="main-title">Details de l'utilisateur</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="#" class="f-s-14 f-w-500">
                      <span>
                        <i class="ph-duotone  ph-stack f-s-16"></i> Apps
                      </span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="f-s-14 f-w-500">Utilisateur</a>
                    </li>
                    <li class="active">
                        <a href="#" class="f-s-14 f-w-500">Details de l'utilisateur </a>
                    </li>
                </ul>
            </div>
        </div>
        <!-- Breadcrumb end -->

        <!-- Order Details start -->
        <div class="row order-details">
            <div class="col-xxl-10">
                <div class="row">
                    <!-- User Details start -->
                    <div class="col-lg-4">
                        <div class="card order-details-card">
                            <div class="card-body">
                                <div class="profile-container">
                                    <div class="image-details">
                                        <div class="profile-image" style="background-image: url(/build/assets/28-DUtk996K.jpg);"></div>
                                        <div class="profile-pic">
                                            <div class="avatar-upload">
                                                <div class="avatar-edit">
                                                    <input type="file" id="imageUpload" accept=".png')}}, .jpg')}}, .jpeg">
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
                                        <h5 class="f-w-600">Kamdem Boniface
                                            <img src="{{ asset('../assets/images/profile-app/01.png') }}" class="w-20 h-20" alt="instagram-check-mark">
                                        </h5>
                                        <p>Responsable de point de distribution</p>

                                        <div class="my-2">
                                            <button type="button" class="btn btn-primary b-r-22" id="followButton"> <i class="ti ti-user"></i>
                                                Profile</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Order Details end -->

                    <!-- Customer Details start -->
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5>Details de l'utilisateur</h5>
                            </div>
                            <div class="card-body">
                                <table class=" project-details-table table table-borderless align-middle mb-0">
                                    <tbody>
                                    <tr>
                                        <td>
                                            <p class="f-w-600 mb-0">Nom</p>
                                        </td>
                                        <td class="text-end">
                                            Kamdem Boniface
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <p class="f-w-600 mb-0">Email</p>
                                        </td>
                                        <td class="text-end">
                                            leonor@gmail.com
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <p class="f-w-600 mb-0">Téléphone</p>
                                        </td>
                                        <td class="text-end">
                                            +237 698 345 221
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <p class="f-w-600 mb-0">Adresse</p>
                                        </td>
                                        <td class="text-end">
                                            Longkak, fgerz
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <p class="f-w-600 mb-0">Poste</p>
                                        </td>
                                        <td class="text-end">
                                            Responsable de point de distribution
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <p class="f-w-600 mb-0">Point de distribution</p>
                                        </td>
                                        <td class="text-end">
                                            Mimboman
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <p class="f-w-600 mb-0">Date de création</p>
                                        </td>
                                        <td class="text-end"><span class="text-danger">20 Jul
                                                                            2024</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <p class="f-w-600 mb-0">Status</p>
                                        </td>
                                        <td class="text-end"><span class="badge text-light-primary">
                                                                            Actif</span> </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- Customer Details end -->

                </div>

            </div>

        </div>
        <!-- Order Details end -->
    </div>
@endsection

@section('script')
<!--customizer-->
<div id="customizer"></div>

<!-- datatable js -->
<script src="{{ asset('assets/vendor/datatable/jquery.dataTables.min.js') }}"></script>

@endsection
