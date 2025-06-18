@extends('supplies.layout')

@section('title', 'Modifier un approvisionnement')
@section('page-title', 'Modifier un approvisionnement')
@section('breadcrumb-title', 'Modifier')

@section('tab-content')
    @livewire('supply.edit-supply-delivery', ['supplyId' => $supply->id])
@endsection
