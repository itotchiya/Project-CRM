<style>
    .customSequence .btn {
        border: none;
    }

    .customSequence .filter-option {
        font-size: 11px;
    }

    .desktop-description {
        resize: none;
    }

    .customSequence .dropdown-toggle::after {
        visibility: hidden;
    }

</style>

<!-- CREATE INVOICE START -->
<div class="bg-white rounded b-shadow-4 create-inv">
    <!-- HEADING START -->
    <div class="d-block d-lg-flex d-md-flex justify-content-between action-bar">
        <div class="px-lg-4 px-md-4 px-3 py-3">
            <h4 class="mb-0 f-21 font-weight-normal "><i class="bi bi-cart3"></i> @lang('app.cart')</h4>
        </div>
    </div>

    <!-- HEADING END -->
    <hr class="m-0 border-top-grey">
    <!-- FORM START -->
    <x-form class="c-inv-form" id="saveInvoiceForm">
            <div class="row px-lg-4 px-md-4 px-3 py-5">
                <div class="col-sm-12">
                    <x-alert type="success">@lang('messages.emptyCartMessage')</x-alert>
                </div>
            </div>
             <!-- CANCEL SAVE SEND START -->
             <x-form-actions class="c-inv-btns d-block d-lg-flex d-md-flex">
                <div class="d-flex mb-3 mb-lg-0 mb-md-0">

                    <x-forms.button-cancel :link="route('products.index')" class="border-0 mr-3">@lang('app.viewProducts')
                    </x-forms.button-cancel>

                </div>
            </x-form-actions>
    </x-form>
    <!-- FORM END -->

</div>
<!-- CREATE INVOICE END -->

<script>
    $(document).ready(function() {

        $('body').on('click', '#show-shipping-field', function() {
            $('#add-shipping-field, #client_shipping_address').toggleClass('d-none');
        });

        $('#add-products').on('changed.bs.select', function(e, clickedIndex, isSelected, previousValue) {
            e.stopImmediatePropagation()
            var id = $(this).val();
            if (previousValue != id && id != '') {
                addProduct(id);
            }
        });

        $('#saveInvoiceForm').on('click', '.remove-item', function() {
            var id = $(this).data('item-id');
            var url = "{{ route('products.remove_cart_item', ':id') }}";
            url = url.replace(':id', id);
            var $this = $(this);

            $.easyAjax({
                url: url,
                container: '#saveInvoiceForm',
                type: "POST",
                blockUI: true,
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    $this.closest('.item-row').fadeOut(300, function() {
                        $this.remove();
                        $('select.customSequence').each(function(index) {
                            $this.attr('name', 'taxes[' + index + '][]');
                            $this.attr('id', 'multiselect' + index + '');
                        });
                        calculateTotal();
                    });
                }
            });
        });


        $('.save-form').click(function() {
            $('.type').prop('disabled', false);

            if (KTUtil.isMobileDevice()) {
                $('.desktop-description').remove();
            } else {
                $('.mobile-description').remove();
            }

            calculateTotal();

            var discount = $('#discount_amount').html();
            var total = $('.sub-total-field').val();

            if (parseFloat(discount) > parseFloat(total)) {
                Swal.fire({
                    icon: 'error',
                    text: "{{ __('messages.discountExceed') }}",

                    customClass: {
                        confirmButton: 'btn btn-primary',
                    },
                    showClass: {
                        popup: 'swal2-noanimation',
                        backdrop: 'swal2-noanimation'
                    },
                    buttonsStyling: false
                });
                return false;
            }

            $.easyAjax({
                url: "{{ route('orders.store') }}",
                container: '#saveInvoiceForm',
                type: "POST",
                blockUI: true,
                redirect: true,
                disableButton: true,
                buttonSelector: ".save-form",
                data: $('#saveInvoiceForm').serialize() + "&type=send"
            })

        });

        $('#saveInvoiceForm').on('click', '.remove-item', function() {
            $(this).closest('.item-row').fadeOut(100, function() {
                $(this).remove();
                $('select.customSequence').each(function(index) {
                    $(this).attr('name', 'taxes[' + index + '][]');
                    $(this).attr('id', 'multiselect' + index + '');
                });
                calculateTotal();
            });
        });

        $('#saveInvoiceForm').on('keyup', '.quantity,.cost_per_item,.item_name, .discount_value', function() {
            var quantity = $(this).closest('.item-row').find('.quantity').val();
            var perItemCost = $(this).closest('.item-row').find('.cost_per_item').val();
            var amount = (quantity * perItemCost);

            $(this).closest('.item-row').find('.amount').val(decimalupto2(amount));
            $(this).closest('.item-row').find('.amount-html').html(decimalupto2(amount));

            var productID = $(this).closest('.item-row').find('.product_id').val();

            $.easyAjax({
                url: "{{ route('products.add_cart_item') }}",
                container: '#saveInvoiceForm',
                type: "POST",
                blockUI: true,
                redirect: true,
                buttonSelector: ".save-form",
                data: {
                    _token: '{{ csrf_token() }}',
                    productID: productID,
                    quantity: quantity,
                    cartType: "1",
                },
            })

            calculateTotal();
        });

        $('#saveInvoiceForm').on('change', '.type, #discount_type','.quantity', function() {
            var quantity = $(this).closest('.item-row').find('.quantity').val();
            var perItemCost = $(this).closest('.item-row').find('.cost_per_item').val();
            var amount = (quantity * perItemCost);

            $(this).closest('.item-row').find('.amount').val(decimalupto2(amount));
            $(this).closest('.item-row').find('.amount-html').html(decimalupto2(amount));

            calculateTotal();
        });

        $('#saveInvoiceForm').on('input', '.quantity', function() {
            var quantity = $(this).closest('.item-row').find('.quantity').val();
            var perItemCost = $(this).closest('.item-row').find('.cost_per_item').val();
            var amount = (quantity * perItemCost);

            $(this).closest('.item-row').find('.amount').val(decimalupto2(amount));
            $(this).closest('.item-row').find('.amount-html').html(decimalupto2(amount));

            calculateTotal();
        });

        $('body').on('click', '.empty-cart', function() {
            let id = $('.userId').val();
            var url = "{{ route('products.remove_cart_item', ':id') }}";
            url = url.replace(':id', id);
            $.easyAjax({
                url: url,
                container: '#saveInvoiceForm',
                type: "POST",
                blockUI: true,
                data: {
                    _token: "{{ csrf_token() }}",
                    type: "all_data",
                },
                success: function(response) {
                   if(response.productItems == 0){
                        $('.cart_empty').hide();
                    }

                }
            });
        });

        calculateTotal();

        init(RIGHT_MODAL);
    });
</script>
