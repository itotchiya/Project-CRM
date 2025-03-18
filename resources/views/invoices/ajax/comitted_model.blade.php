<x-form>
    <div class="modal-header">
        <h5 class="modal-title" id="modelHeading">@lang('app.quantityExceed')</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                aria-hidden="true">×</span></button>
    </div>
    <div class="modal-body">
        <b>@lang('app.SorryTheQuantityExceedsFor')
            @foreach ($products as $product )
                {{ $product->name }} 
            @endforeach
        </b>
    </div>
    <div class="modal-footer">
        <x-forms.button-cancel id="cancel-modal" class="border-0 mr-3">@lang('app.doItLater')</x-forms.button-cancel>

        <x-forms.link-primary :link="route('purchase-order.create')" class="mr-3 float-left" icon="plus">
                        @lang('app.add') @lang('app.order')
        </x-forms.link-primary>

    </div>
</x-form>

<script>

$(document).ready(function() {
    type = "{{request()->type}}";
    $('#cancel-modal').click(function() {
        saveForm(type, 'exceed');
    });
});

</script>
