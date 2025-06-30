@extends('supplies.layout')

@section('title', 'Scan des codes-barres')
@section('page-title', 'Scan des codes-barres')
@section('breadcrumb-title', 'Scanner')

@section('tab-content')
    @livewire('supply.scan-bottles', [
        'supply' => $supply,
    ])
@endsection
