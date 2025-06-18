@extends('supplies.layout')

@section('title', 'Scan des codes-barres')
@section('page-title', 'Scan des codes-barres')
@section('breadcrumb-title', 'Scanner')

@section('tab-content')
    @livewire('scan-bottles', [
        'supplyId' => $supply->id,
        'productId' => $supply->product->id ?? null,
        'bottleType' => $supply->product->type ?? null,
        'quantity' => $supply->product->quantity ?? 0,
        'supplyTitle' => $supply->title ?? null,
        'supplyDate' => $supply->supply_date ?? null,
    ])
@endsection
