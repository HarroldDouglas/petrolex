@props([
    'position' => 'top-end',
    'timer' => 3000,
    'showProgressBar' => true,
    'toast' => true
])

<script>
document.addEventListener('livewire:initialized', () => {
    Livewire.on('show-notification', (event) => {
        const data = event[0];
        
        Swal.fire({
            icon: data.type || 'success',
            title: data.title || 'Notification',
            text: data.message,
            showConfirmButton: false,
            timer: data.timer || {{ $timer }},
            timerProgressBar: {{ $showProgressBar ? 'true' : 'false' }},
            toast: {{ $toast ? 'true' : 'false' }},
            position: '{{ $position }}',
            customClass: {
                popup: 'swal2-toast-custom',
                title: 'swal2-toast-title',
                content: 'swal2-toast-content'
            },
            showClass: {
                popup: 'animate__animated animate__fadeInRight animate__faster'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutRight animate__faster'
            }
        });
    });
});
</script>

<style>
.swal2-toast-custom {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
    border-radius: 8px !important;
}

.swal2-toast-title {
    font-weight: 600 !important;
    font-size: 14px !important;
}

.swal2-toast-content {
    font-size: 13px !important;
}
</style>