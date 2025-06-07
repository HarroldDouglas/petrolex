@props([
    'timer' => 3000,
    'position' => 'top-end',
    'icon' => session('success') ? 'success' : (session('error') ? 'error' : 'info'),
    'message' => session('success') ?? session('error') ?? '',
])

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