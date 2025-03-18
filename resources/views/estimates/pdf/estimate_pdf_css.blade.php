<style>
    @font-face {
        font-family: 'THSarabunNew';
        font-style: normal;
        font-weight: normal;
        src: url("{{ storage_path('fonts/THSarabunNew.ttf') }}") format('truetype');
    }

    @font-face {
        font-family: 'THSarabunNew';
        font-style: normal;
        font-weight: bold;
        src: url("{{ storage_path('fonts/THSarabunNew_Bold.ttf') }}") format('truetype');
    }

    @font-face {
        font-family: 'THSarabunNew';
        font-style: italic;
        font-weight: bold;
        src: url("{{ storage_path('fonts/THSarabunNew_Bold_Italic.ttf') }}") format('truetype');
    }

    @font-face {
        font-family: 'THSarabunNew';
        font-style: italic;
        font-weight: bold;
        src: url("{{ storage_path('fonts/THSarabunNew_Italic.ttf') }}") format('truetype');
    }

    @if($invoiceSetting->locale == 'vi')
    @font-face {
        font-family: 'BeVietnamPro';
        font-style: normal;
        font-weight: normal;
        src: url("{{ storage_path('fonts/BeVietnamPro-Black.ttf') }}") format('truetype');
    }

    @font-face {
        font-family: 'BeVietnamPro';
        font-style: italic;
        font-weight: normal;
        src: url("{{ storage_path('fonts/BeVietnamPro-BlackItalic.ttf') }}") format('truetype');
    }

    @font-face {
        font-family: 'BeVietnamPro';
        font-style: italic;
        font-weight: bold;
        src: url("{{ storage_path('fonts/BeVietnamPro-bold.ttf') }}") format('truetype');
    }

    @endif

@if ($invoiceSetting->is_chinese_lang)
@font-face {
        font-family: SimHei;
        /*font-style: normal;*/
        /*font-weight: bold;*/
        src: url('{{ asset('fonts/simhei.ttf') }}') format('truetype');
    }

    @endif

    @php $font='';

    if($invoiceSetting->locale=='ja') {
        $font='ipag';
    }

    else if($invoiceSetting->locale=='hi') {
        $font='hindi';
    }

    else if($invoiceSetting->locale=='th') {
        $font='THSarabunNew';
    }

    else if($invoiceSetting->is_chinese_lang) {
        $font='SimHei';
    }
    else if($invoiceSetting->locale == 'vi') {
        $font = 'BeVietnamPro';
    }

    else {
        $font='Verdana';
    }

    @endphp
    @if ($invoiceSetting->is_chinese_lang)
        body {
        font-weight: normal !important;
    }

    @endif
    * {
        font-family: {{$font}}, DejaVu Sans, sans-serif;
    }
</style>
