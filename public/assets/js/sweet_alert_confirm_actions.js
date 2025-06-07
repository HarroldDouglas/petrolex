function confirmAction(params) {
    const methodName = params.method || "";
    const methodParams = params.parameters || [];
    const titleValue = params.title || "Confirmation";
    const textValue =
        params.text || "Voulez-vous vraiment effectuer cette action ?";
    const iconValue = params.icon || "question";
    const confirmTextValue = params.confirmText || "Oui, confirmer";
    const cancelTextValue = params.cancelText || "Annuler";

    Swal.fire({
        title: titleValue,
        text: textValue,
        icon: iconValue,
        showCancelButton: true,
        confirmButtonColor: "#198754",
        cancelButtonColor: "#6c757d",
        confirmButtonText:
            '<i class="ti ti-check me-1"></i>' + confirmTextValue,
        cancelButtonText: '<i class="ti ti-x me-1"></i>' + cancelTextValue,
        reverseButtons: true,
        focusCancel: true,
        customClass: {
            confirmButton: "btn btn-success me-2",
            cancelButton: "btn btn-secondary ms-2",
            actions: "gap-2",
        },
        buttonsStyling: false,
    }).then((result) => {
        if (result.isConfirmed) {
            if (typeof Livewire !== "undefined") {
                const componentId = document
                    .querySelector("[wire\\:id]")
                    .getAttribute("wire:id");
                Livewire.find(componentId).call(methodName, ...methodParams);
            }
        }
    });
}

function confirmDelete(params) {
    const deleteId = params.id || "";
    const methodName = params.method || "";
    const methodParams = params.parameters || [];
    const confirmWordValue = params.confirmWord || "supprimer";
    const titleValue = params.title || "Supprimer l'élément";
    const textValue =
        params.text ||
        "Vous êtes sur le point de supprimer définitivement cet élément.";
    const entityNameValue = params.entityName || "";
    const confirmTextValue = params.confirmText || "Supprimer définitivement";
    const cancelTextValue = params.cancelText || "Annuler";

    const uniqueInputId = "deleteConfirmInput_" + deleteId;

    Swal.fire({
        title: titleValue,
        html: `
            <p class="mb-3">${textValue}</p>
            ${entityNameValue ? `<p class="fw-bold text-danger mb-3">${entityNameValue}</p>` : ""}
            <p class="mb-3">Tapez <strong>"${confirmWordValue}"</strong> pour confirmer :</p>
            <input type="text" id="${uniqueInputId}" class="form-control" placeholder="Tapez "${confirmWordValue}" pour confirmer">
        `,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#198754",
        cancelButtonColor: "#6c757d",
        confirmButtonText:
            '<i class="ti ti-trash me-1"></i>' + confirmTextValue,
        cancelButtonText: '<i class="ti ti-x me-1"></i>' + cancelTextValue,
        reverseButtons: true,
        focusCancel: true,
        customClass: {
            confirmButton: "btn btn-success me-2",
            cancelButton: "btn btn-secondary ms-2",
            actions: "gap-2",
        },
        buttonsStyling: false,
        preConfirm: () => {
            const input = document.getElementById(uniqueInputId);
            if (
                input.value.toLowerCase().trim() !==
                confirmWordValue.toLowerCase()
            ) {
                Swal.showValidationMessage(
                    `Vous devez taper "${confirmWordValue}" pour confirmer la suppression`,
                );
                return false;
            }
            return true;
        },
        didOpen: () => {
            const input = document.getElementById(uniqueInputId);
            const confirmButton = Swal.getConfirmButton();

            confirmButton.disabled = true;
            confirmButton.style.opacity = "0.5";

            input.addEventListener("input", function () {
                const isValid =
                    this.value.toLowerCase().trim() ===
                    confirmWordValue.toLowerCase();
                confirmButton.disabled = !isValid;
                confirmButton.style.opacity = isValid ? "1" : "0.5";
            });

            input.focus();
        },
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: "Suppression en cours...",
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            if (typeof Livewire !== "undefined") {
                const componentId = document
                    .querySelector("[wire\\:id]")
                    .getAttribute("wire:id");
                Livewire.find(componentId).call(methodName, ...methodParams);
            }
        }
    });
}
