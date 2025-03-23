@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/notifications/toastify.min.css') }}">
@endpush

@if (session('sweet_alert'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: "{{ session('sweet_alert.title') }}",
                text: "{{ session('sweet_alert.text') }}",
                icon: "{{ session('sweet_alert.icon') }}",
                showConfirmButton: {{ session('sweet_alert.showConfirmButton') ? 'true' : 'false' }},
                timer: {{ session('sweet_alert.timer') ?? 'null' }},
                customClass: {
                    popup: '{{ session('sweet_alert.customClass.popup') ?? '' }}',
                    title: '{{ session('sweet_alert.customClass.title') ?? '' }}',
                }
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
