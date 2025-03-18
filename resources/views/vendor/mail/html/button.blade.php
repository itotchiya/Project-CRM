<table class="action" align="center" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center">
            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td align="center">
                        <table border="0" cellpadding="0" cellspacing="0">
                            <tr>
                                <td>
                                    @if (!empty($themeColor))
                                        <a href="{{ $url }}" style="background-color: {{ $themeColor}};
                                        border-bottom: 8px solid {{ $themeColor}};
                                        border-left: 18px solid {{ $themeColor}};
                                        border-right: 18px solid {{ $themeColor}};
                                        border-top: 8px solid {{ $themeColor}};" class="button" target="_blank">{{ $slot }}</a>
                                    @else
                                        <a href="{{ $url }}" class="button button-blue" target="_blank">{{ $slot }}</a>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
