@extends('layout.master')

@section('title')
    Test DataTable
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/livewire-tables.css') }}">
@endpush

@section('main-content')
    <div class="container-fluid px-4">
        <h1 class="mt-4">Test DataTable</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Test DataTable</li>
        </ol>
        <div class="card mb-4">
            <div class="card-body">
                <livewire:users-table />
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('notify', (event) => {
                Swal.fire({
                    icon: event[0].type,
                    title: event[0].message,
                    showConfirmButton: false,
                    timer: 3000
                });
            });
        });
    </script>
@endpush