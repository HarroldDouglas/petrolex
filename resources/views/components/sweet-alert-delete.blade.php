@props([
    'title' => 'Supprimer l\'élément',
    'text' => 'Vous êtes sur le point de supprimer définitivement cet élément.',
    'entityName' => '',
    'confirmWord' => 'supprimer',
    'placeholder' => 'Tapez "supprimer" pour confirmer',
    'confirmText' => 'Supprimer définitivement',
    'cancelText' => 'Annuler',
    'method' => '',
    'parameters' => [],
    'functionName' => 'confirmDelete'
])

<script>
function {{ $functionName }}() {
    Swal.fire({
        title: '{{ $title }}',
        html: `
            <p class="mb-3">{{ $text }}</p>
            @if($entityName)<p class="fw-bold text-danger mb-3">{{ $entityName }}</p>@endif
            <p class="mb-3">Tapez <strong>"{{ $confirmWord }}"</strong> pour confirmer :</p>
            <input type="text" id="deleteConfirmInput_{{ $functionName }}" class="form-control" placeholder="{{ $placeholder }}">
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="ti ti-trash me-1"></i>{{ $confirmText }}',
        cancelButtonText: '<i class="ti ti-x me-1"></i>{{ $cancelText }}',
        reverseButtons: true,
        focusCancel: true,
        customClass: {
            confirmButton: 'btn btn-success me-2',
            cancelButton: 'btn btn-secondary ms-2',
            actions: 'gap-2'
        },
        buttonsStyling: false,
        preConfirm: () => {
            const input = document.getElementById('deleteConfirmInput_{{ $functionName }}');
            if (input.value.toLowerCase().trim() !== '{{ strtolower($confirmWord) }}') {
                Swal.showValidationMessage('Vous devez taper "{{ $confirmWord }}" pour confirmer la suppression');
                return false;
            }
            return true;
        },
        didOpen: () => {
            const input = document.getElementById('deleteConfirmInput_{{ $functionName }}');
            const confirmButton = Swal.getConfirmButton();
            
            confirmButton.disabled = true;
            confirmButton.style.opacity = '0.5';
            
            input.addEventListener('input', function() {
                const isValid = this.value.toLowerCase().trim() === '{{ strtolower($confirmWord) }}';
                confirmButton.disabled = !isValid;
                confirmButton.style.opacity = isValid ? '1' : '0.5';
            });
            
            input.focus();
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Suppression en cours...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            @this.call('{{ $method }}', {!! json_encode($parameters) !!});
        }
    });
}
</script>
