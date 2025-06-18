@extends('supplies.layout')

@section('title', 'Enregistrement des produits')
@section('page-title', 'Enregistrement des produits')
@section('breadcrumb-title', 'Produits')

@section('tab-content')
    @livewire('supply.manage-supply-products', ['supplyId' => $supply->id])
@endsection
