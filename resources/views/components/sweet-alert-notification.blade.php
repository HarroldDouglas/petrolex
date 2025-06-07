@props([
    'timer' => 3000,
    'position' => 'top-end',
    'icon' => session('success') ? 'success' : (session('error') ? 'error' : 'info'),
    'message' => session('success') ?? session('error') ?? '',
])

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/notifications/toastify.min.css') }}">
@endpush

@if($message)
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const Toast = Swal.mixin({
            toast: true,
            position: '{{ $position }}',
            showConfirmButton: false,
            timer: {{ $timer }},
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        Toast.fire({
            icon: '{{ $icon }}',
            title: '{{ $message }}'
        });
    });
</script>
@endif

@push('scripts')
    <script src="{{ asset('assets/vendor/notifications/toastify-js.js') }}"></script>

    <!-- sweetalert js-->
    <script src="{{ asset('assets/vendor/sweetalert/sweetalert.js') }}"></script>

    <!-- js -->
    <script src="{{ asset('assets/js/sweet_alert.js') }}"></script>
@endpush
