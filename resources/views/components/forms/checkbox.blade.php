<div class="form-check">
    <input {{ $attributes->merge(['class' => 'form-check-input']) }}
           type="checkbox"
           name="{{ $fieldName }}"
           id="{{ $fieldId }}"
           @isset($fieldValue)
               value="{{ $fieldValue }}"
           @endisset
           @checked($checked)
           @disabled($fieldPermission)
    >
    @if ($fieldLabel != '')
        <label
            class="form-check-label form_custom_label text-dark-grey pl-2 mr-4 justify-content-start cursor-pointer checkmark-20 pt-1 text-wrap text-break"
            for="{{ $fieldId }}">
            {{ $fieldLabel }}
            @if (!is_null($popover))
                &nbsp;<i class="fa fa-question-circle" data-toggle="popover" data-placement="top" data-html="true"
                         data-content="{{ $popover }}" data-trigger="hover"></i>
            @endif
        </label>
    @endif
</div>
