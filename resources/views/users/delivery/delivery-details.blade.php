@extends('layout.master')
@section('title', 'Détail du livreur') {{-- Changed to Livreur for consistency --}}
@section('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/datatable/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/datatable/datatable2/buttons.dataTables.min.css') }}">
@endsection
@section('main-content')
    <div class="container-fluid">
        <div class="row m-1">
            <div class="col-8 p-0">
                <h4 class="main-title">Détail du livreur</h4> {{-- Consistent title --}}
                <ul class="app-line-breadcrumbs mb-3">
                    <li class="">
                        <a href="{{ route('users.list') }}" class="f-s-14 f-w-500"> {{-- Link back to users list --}}
                            <span>
                                <i class="ph-duotone ph-stack f-s-16"></i> Livreurs
                            </span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="#" class="f-s-14 f-w-500">Détails du livreur </a>
                    </li>
                </ul>
            </div>
            <div class="col-4 text-end">
                <a href="{{ route('users.list') }}" class="btn btn-primary">
                    <i class="ti ti-arrow-back"></i> Retour à la liste
                </a>
            </div>
        </div>
        {{-- IMPORTANT: Use the correct variable names passed from the controller --}}
        @if ($user && $deliveryPerson) {{-- Check both user and deliveryPerson --}}
            <div class="row order-details p-0">
                <div class="col-12 mb-3">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card order-details-card">
                                <div class="card-body">
                                    <div class="profile-container">
                                        <div class="image-details">
                                            <div class="profile-image"
                                                style="background-image: url('{{ $user->profile_picture_url ?? asset('assets/images/default-avatar.jpg') }}');"> {{-- Dynamic profile picture --}}
                                            </div>
                                            <div class="profile-pic">
                                                <div class="avatar-upload">
                                                    <div class="avatar-edit">
                                                        {{-- This part is for image upload, which isn't handled by this view's scope.
                                                             Consider removing or making it a Livewire component if upload is intended here.
                                                             For now, I'm keeping it but noting its current lack of functionality. --}}
                                                        <input type="file" id="imageUpload" accept=".png, .jpg, .jpeg">
                                                        <label for="imageUpload"><i class="ti ti-photo-heart"></i></label>
                                                    </div>
                                                    <div class="avatar-preview">
                                                        <div id="imgPreview" style="background-image: url('{{ $user->profile_picture_url ?? asset('assets/images/default-avatar.jpg') }}');"> {{-- Also dynamic --}}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="person-details">
                                            <h5 class="f-w-600">
                                                {{ $user->first_name ?? '-' }} {{ $user->last_name ?? '-' }} {{-- Using $user --}}
                                                {{-- The checkmark image might be for verification, adjust as needed --}}
                                                <img src="{{ asset('assets/images/profile-app/01.png') }}" class="w-20 h-20" alt="verified">
                                            </h5>

                                            <div class="my-2">
                                                {{-- This "Profile" button seems to have no defined action.
                                                     If it's meant to edit the profile, you'd link it there.
                                                     For now, it's just a button. Removed the ID if not used for JS. --}}
                                                <button type="button" class="btn btn-primary b-r-22">
                                                    <i class="ti ti-user"></i> Profil
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Détails du livreur (ID: {{ $deliveryPerson->id }})</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="f-w-600 text-dark"><i
                                                    class="ti ti-user text-secondary f-s-18 me-2"></i>Nom</h6>
                                        <div class="text-end">
                                            <p>{{ $user->last_name ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-3">
                                        <h6 class="f-w-600 text-dark"><i
                                                    class="ti ti-user text-secondary f-s-18 me-2"></i>Prénom</h6>
                                        <div class="text-end">
                                            <p>{{ $user->first_name ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-3">
                                        <h6 class="f-w-600 text-dark"><i class="ti ti-mail f-s-18 text-secondary me-2"></i>Email
                                        </h6>
                                        <div class="text-end">
                                            <p>{{ $user->email ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-3">
                                        <h6 class="f-w-600 text-dark"><i
                                                    class="ti ti-device-mobile f-s-18 text-secondary me-2"></i>Téléphone</h6>
                                        <div class="text-end">
                                            <p>{{ $user->phone_number ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-3">
                                        <h6 class="f-w-600 text-dark"><i
                                                    class="ti ti-map-pin f-s-18 text-secondary me-2"></i>Adresse</h6>
                                        <div class="text-end">
                                            <p>{{ $user->address ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-3">
                                        <h6 class="f-w-600 text-dark"><i
                                                    class="ti ti-calendar-stats text-secondary f-s-18 me-2"></i>Date de création</h6>
                                        <div class="text-end">
                                            <p>{{ $user->created_at ? $user->created_at->format('d/m/Y') : '-' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                    <div class="row mt-1">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Commandes du livreur</h5>
                                </div>
                                <div class="card-body">
                                    @if ($deliveryPersonOrders && $deliveryPersonOrders->count() > 0) 
                                        <div class="table-responsive">
                                            <table id="deliveryOrdersTable" class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Numéro</th>
                                                        <th>Status</th>
                                                        <th>Date</th>
                                                        <th>Client</th> 
                                                        <th>Montant</th> 
                                                        <th>Actions</th> 
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($deliveryPersonOrders as $order)
                                                        <tr>
                                                            <td>
                                                                <a href="{{ route('orders.details', $order->id) }}">{{ $order->id }}</a>
                                                            </td>
                                                            <td>
                                                                @php
                                                                    $badgeClass = 'text-outline-success'; 
                                                                    if ($order->status === 'En cours') {
                                                                        $badgeClass = 'text-outline-warning';
                                                                    } elseif ($order->status === 'Annulée') {
                                                                        $badgeClass = 'text-outline-danger';
                                                                    } elseif ($order->status === 'Livrée') { 
                                                                        $badgeClass = 'text-outline-success';
                                                                    }
                                                                @endphp
                                                                <span class="badge {{ $badgeClass }}">{{ $order->status }}</span>
                                                            </td>
                                                            <td>{{ $order->order_date ? \Carbon\Carbon::parse($order->order_date)->format('d/m/Y H:i') : '-' }}</td>
                                                            <td>{{ $order->customer->user->full_name ?? ($order->customer->user->first_name . ' ' . $order->customer->user->last_name ?? '-') }}</td>
                                                            <td>{{ number_format($order->total_amount ?? 0, 2) }} XAF</td>
                                                            <td>
                                                                <a href="{{ route('orders.details', $order->id) }}" class="btn btn-sm btn-outline-primary">
                                                                    <i class="bi bi-eye"></i> Voir
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p>Ce livreur n'a pas encore de commandes attribuées.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        </div>

                </div>
            </div>
            @else
            <p>Livreur non trouvé ou profil incomplet.</p>
        @endif
    </div>
@endsection

@section('script')
    <div id="customizer"></div>

    <script src="{{ asset('assets/vendor/datatable/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatable/datatable2/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatable/datatable2/buttons.print.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatable/datatable2/jszip.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatable/datatable2/pdfmake.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatable/datatable2/vfs_fonts.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatable/datatable2/buttons.html5.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable for orders
            $('#deliveryOrdersTable').DataTable({
                dom: 'Bfrtip', // Show buttons
                buttons: [
                    'copyHtml5',
                    'excelHtml5',
                    'csvHtml5',
                    'pdfHtml5',
                    'print'
                ],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/French.json" // French localization
                },
                "order": [[2, "desc"]] // Sort by Date column (index 2) descending by default
            });

            // Optional: Handle profile image upload preview (requires more JS/backend)
            // This is just a placeholder for the preview, actual upload needs server-side logic.
            $("#imageUpload").change(function() {
                if (this.files && this.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('#imgPreview').css('background-image', 'url(' + e.target.result + ')');
                        $('#imgPreview').hide();
                        $('#imgPreview').fadeIn(650);
                    }
                    reader.readAsDataURL(this.files[0]);
                }
            });
        });
    </script>
@endsection