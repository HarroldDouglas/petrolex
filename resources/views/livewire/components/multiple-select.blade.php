<div>
    <div wire:ignore>
    <select 
        class="form-control select2 {{ $hasErrors ? 'is-invalid' : '' }}"
        multiple
        x-init="
            $nextTick(() => {
                // Use $el to reference the current element instead of document.querySelector
                let select2 = $($el).select2({
                    placeholder: 'Sélectionnez...',
                    allowClear: true,
                    width: '100%'
                });

                select2.on('change', function (e) {
                    let data = $(this).val();
                    @this.call('updateSelection', data);
                });

                // Initialize with any existing values
                if (@js($selectedOptions).length > 0) {
                    select2.val(@js($selectedOptions)).trigger('change');
                }
            });
        "
    >
        @foreach($options as $key => $value)
            <option value="{{ $key }}" wire:key="option-{{ $key }}"
                    @selected(in_array($key, $selectedOptions))>
                {{ $value }}
            </option>
        @endforeach
    </select>
    </div>
     @error('selectedOptions')
        <div class="invalid-feedback d-block">
            {{ $message }}
        </div>
    @enderror
</div>
