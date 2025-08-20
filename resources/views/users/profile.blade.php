@extends('layout.master')
@section('title', 'User Profile')
@section('css')
    <!-- select2 css -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/select/select2.min.css') }}">

    <!-- livewire multiple select -->
    <link rel="stylesheet" href="{{ asset('assets/css/multiple-select.css') }}">
@endsection
@section('main-content')
    <div class="container-fluid">
        <!-- Breadcrumb start -->
        <div class="row m-1">
            <div class="col-8 p-0">
                <h4 class="main-title">Mon Profil</h4>
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="{{ route('users.list') }}" class="f-s-14 f-w-500">
                            <span>
                                <i class="ph-duotone ph-stack f-s-16"></i> Utilisateurs
                            </span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="#" 
                            class="f-s-14 f-w-500">Profil</a>
                    </li>
                </ul>
            </div>
        </div>
        <!-- Breadcrumb end -->

        <!-- Add User start -->
        @livewire('user.edit-profile-form', ['user' => $user])
        <!-- Add User end -->
        
@endsection

@section('script')
    <!-- select2 -->
    <script src="{{ asset('assets/vendor/select/select2.min.js') }}"></script>
@endsection
