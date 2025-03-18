<style>
    /* Set the size of the div element that contains the map */
    #map {
        height: 400px;
        /* The height is 400 pixels */
        width: 100%;
        /* The width is the width of the web page */
    }

    #description {
        font-family: Roboto;
        font-size: 15px;
        font-weight: 300;
    }

    #infowindow-content .title {
        font-weight: bold;
    }

    #infowindow-content {
        display: none;
    }

    #map #infowindow-content {
        display: inline;
    }

    .pac-card {
        background-color: #fff;
        border: 0;
        border-radius: 2px;
        box-shadow: 0 1px 4px -1px rgba(0, 0, 0, 0.3);
        margin: 10px;
        padding: 0 0.5em;
        font: 400 18px Roboto, Arial, sans-serif;
        overflow: hidden;
        font-family: Roboto;
        padding: 0;
    }

    #pac-container {
        padding-bottom: 12px;
        margin-right: 12px;
    }

    .pac-controls {
        display: inline-block;
        padding: 5px 11px;
    }

    .pac-controls label {
        font-family: Roboto;
        font-size: 13px;
        font-weight: 300;
    }

    #pac-input {
        background-color: #fff;
        font-size: 15px;
        font-weight: 300;
        margin-left: 12px;
        padding: 0 11px 0 13px;
        text-overflow: ellipsis;
        width: 400px;
    }

    #pac-input:focus {
        border-color: #4d90fe;
    }

    #title {
        font-size: 18px;
        font-weight: 500;
        padding: 10px 12px;
    }

</style>
<div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 ml-3 ">
    <div class="row">
        <div class="col-lg-12 mt-2 mb-0">
            @php
                $link = route('business-address.index');
                $link =  '<a href="'.$link.'">'.__('app.menu.businessAddresses').'</a>';
            @endphp
            <x-alert type="secondary">
                {!!   __('messages.googleMapTooltip',['route' => $link])  !!}
            </x-alert>
        </div>
        <div class="col-lg-8 mb-0">
            <x-forms.text :fieldLabel="__('modules.accountSettings.google_map_key')"
                          :fieldPlaceholder="__('placeholders.googleMapKey')"
                          fieldName="google_map_key" fieldId="google_map_key"
                          :fieldValue="global_setting()->google_map_key"/>

            <small class="form-text text-muted my-0">@lang('messages.googleMapRemove')</small>
            <small class="form-text text-muted my-2">Visit <a
                    href='https://console.cloud.google.com/project/_/google/maps-apis/overview' target="_blank"> Google
                    Cloud Console</a> to get the keys</small>
        </div>
    </div>
</div>
<div class="w-100 border-top-grey set-btns ml-2">
    <x-setting-form-actions>
        <x-forms.button-primary id="save-google-map-setting-form" class="mr-3" icon="check">@lang('app.save')
        </x-forms.button-primary>
    </x-setting-form-actions>
</div>

<script>
    $('body').on('click', '#save-google-map-setting-form', function () {
        const url = "{{ route('app-settings.update', [company()->id]) }}?page=google-map-setting";

        $.easyAjax({
            url: url,
            container: '#editSettings',
            type: "POST",
            disableButton: true,
            buttonSelector: "#save-google-map-setting-form",
            data: $('#editSettings').serialize(),
        })
    });

</script>
