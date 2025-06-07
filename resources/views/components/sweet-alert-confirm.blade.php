@props([
    'title' => 'Confirmation',
    'text' => 'Voulez-vous vraiment effectuer cette action ?',
    'icon' => 'question',
    'confirmText' => 'Oui, confirmer',
    'cancelText' => 'Annuler',
    'method' => '',
    'parameters' => [],
    'functionName' => 'confirmAction'
])

<script>
function {{ $functionName }}() {
    Swal.fire({
        title: '{{ $title }}',
        text: '{{ $text }}',
        icon: '{{ $icon }}',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="ti ti-check me-1"></i>{{ $confirmText }}',
        cancelButtonText: '<i class="ti ti-x me-1"></i>{{ $cancelText }}',
        reverseButtons: true,
        focusCancel: true,
        customClass: {
            confirmButton: 'btn btn-success me-2',
            cancelButton: 'btn btn-secondary ms-2',
            actions: 'gap-2'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            @this.call('{{ $method }}', {!! json_encode($parameters) !!});
        }
    });
}
</script>
