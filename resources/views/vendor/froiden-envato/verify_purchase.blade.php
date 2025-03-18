<html>
<head>
    <title></title>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    <link rel="stylesheet" href="//envato.froid.works/plugins/froiden-helper/helper.css">
    <style>
        .invalid-feedback {
            color: darkred;
        }

        .alert {
            padding: 8px;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>
<!-- Page Content -->
<div class="container">
    <div class="card">

        <h2 class="mt-5">Verify Your Purchase</h2>
        <div class="row">
            <div class="col-md-6">
                <p class="alert alert-info">For Domain:- <b>{{ \request()->getHost() }}</b></p>
            </div>
            <div class="col-md-6">
                <p class="alert alert-info">Product Link:-
                    <a href="{{config('froiden_envato.envato_product_url')}}"
                       target="_blank">{{ config('froiden_envato.envato_product_url') }}</a>
                </p>
            </div>
        </div>

        <p>
            <span class="badge badge-info">Note</span>
            <a href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-"
               class="f-w-500" target="_blank"><u>Click this link to find your purchase code.</u></a>
        </p>
        <p style="margin: 20px 0">
            <span class="label label-warning">ALERT</span>
            Contact your admin if you are not the admin to verify the purchase.

        </p>

        <div id="response-message"></div>

        <form action="" id="verify-form" onsubmit="validateCode();return false;">
            <div class="row">
                <div class="col-md-12">
                    <div id="alert"></div>
                </div>
            </div>

            <div class="form-body">
                {{ csrf_field() }}

                <div class="row">

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">Enter your Purchase code / License Code</label>
                            <input type="text" id="purchase_code" name="purchase_code" class="form-control"
                                   placeholder="088abxxx-2831-47e7-xxxx-9d8dd3c7xxx8"/>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">Email</label>
                            <input type="text" id="email" name="email" class="form-control"/>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <input type="checkbox" class="form-check-input" name="consent" id="consent" checked/>
                            <small class="form-check-label">I agree to receive important emails related to the new
                                updates, and security updates. I also consent to receiving promotional emails, including
                                offers, discounts, and updates about new features or services</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <!--/span-->
                <div class="col-md-6">
                    <div class="form-group">
                        <button class="btn btn-success" type="button" id="verify-purchase"
                                onclick="validateCode();return false;">Verify
                        </button>
                    </div>
                </div>
                <!--/span-->
            </div>
        </form>
    </div>
</div>

<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
<script src="//envato.froid.works/plugins/froiden-helper/helper.js"></script>

<script>

    function validateCode() {
        $.easyAjax({
            type: 'POST',
            url: "{{ route('purchase-verified') }}",
            data: $("#verify-form").serialize(),
            container: "#verify-form",
            messagePosition: 'inline',
            success: function (response) {
                if (response.status == 'success') {
                    $('.form-group').hide();
                }
            }
        });
        return false;
    }

</script>

</body>
</html>
