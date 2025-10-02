@extends('layout.master')
@section('title', "Gérer les permissions - {$user->full_name}")

@section('main-content')
    <div class="container-fluid">
        <livewire:user.manage-user-permissions :user="$user" />
    </div>
@endsection