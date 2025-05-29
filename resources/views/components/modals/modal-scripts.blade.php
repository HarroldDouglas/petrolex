<script>
    $(document).ready(function() {
        // Delete confirmation
        $('.delete-confirm-input').on('input', function() {
            var confirmText = $(this).val();
            $('.confirm-delete-btn').prop('disabled', confirmText !== 'supprimer');
        });

        // Generic handler for any delete modal
        $('[id$="deleteModal"]').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var itemId = button.data('item-id');
            var itemName = button.data('item-name');
            var modalId = $(this).attr('id');
            
            $('#' + modalId + 'Id').val(itemId);
            $('#' + modalId + 'Name').text(itemName);
            $(this).find('.delete-confirm-input').val('');
            $(this).find('.confirm-delete-btn').prop('disabled', true);
        });

        // Generic handler for any deactivate modal
        $('[id$="deactivateModal"]').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var itemId = button.data('item-id');
            var modalId = $(this).attr('id');
            
            $('#' + modalId + 'Id').val(itemId);
        });

        // Generic handler for any activate modal
        $('[id$="activateModal"]').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var itemId = button.data('item-id');
            var modalId = $(this).attr('id');
            
            $('#' + modalId + 'Id').val(itemId);
        });

        // These can be overridden or extended in the page-specific scripts
        $('.confirm-delete-btn').click(function() {
            window.location.reload();
        });
        
        $('.confirm-deactivate-btn').click(function() {
            window.location.reload();
        });
        
        $('.confirm-activate-btn').click(function() {
            window.location.reload();
        });
    });
</script>
