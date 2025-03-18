<div {{ $attributes->merge(['class' => 'w-100 form-group mb-0']) }}>
    <x-forms.select :fieldId="$fieldId" :fieldLabel="$fieldLabel" :fieldName="$fieldName"></x-forms.select>
</div>

<script>
    $(document).ready(function () {
        $("#{{$fieldId}}").select2({
            @if($format)
            templateResult: formatState,
            @endif
            ajax: {
                url: "{{ $route }}",
                dataType: 'json',
                type: "GET",
                delay: 250,
                data: function (params) {
                    return {
                        search: params.term,
                    };
                },
                processResults: function (response) {
                    return {
                        results: response
                    }
                },
                cache: true
            },
            placeholder: '{{$placeholder}}',
            minimumInputLength: 2,
            language: {
                inputTooShort: function () {
                    return "@lang('placeholders.select2Min')";
                }
            },
            allowClear: true
        });

        function formatState(data) {
            if (!data.id) {
                return data.text;
            }
            return $(`<span><img class="mr-2 taskEmployeeImg rounded" src="${data.logo_url}" />${data.text}</span>`);
        }
    })
</script>
