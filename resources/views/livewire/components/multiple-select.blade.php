<div>
    <div wire:ignore>
    <select 
        class="form-control select2"
        multiple
        wire:key="select-key-{{ uniqid() }}"
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
            });
        "
    >
        @foreach($items as $item)
            <option value="{{ $item['id'] }}" wire:key="option-{{ $item['id'] }}"
                    @selected(in_array($item['id'], $selected))>
                {{ $item['name'] }}
            </option>
        @endforeach
    </select>
    </div>
</div>