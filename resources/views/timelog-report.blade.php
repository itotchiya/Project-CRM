<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title>@lang('app.menu.timeLogReport')</title>
    <meta name="author" content="Andrés Herrera García">
    <meta name="description" content="PDF de una fotomulta">
    <meta name="keywords" content="fotomulta, comparendo">
    <style>
        table.header {
            width: 100%;
            padding: 0px;
            margin: 0px;
            font-family: 'noto-sans, DejaVu Sans , sans-serif';
            font-size: 12px;
            line-height: 1.4;
            color: #28313c;
            margin-bottom: 20px;
        }

        table.content {
            width: 100%;
            border-spacing: 0;
            padding: 0px;
            margin: 0px;
            font-family: 'noto-sans, DejaVu Sans , sans-serif';
            font-size: 10px;
            line-height: 1.4;
            color: #28313c;
            margin-bottom: 20px;
        }

        .content th, .content td {
            border: 1px solid #cccccc;
            padding: 1px 3px;
            text-align: center;
        }

        .content .row {
            border: 1px solid #DBDBDB;
        }

        #logo {
            height: 50px;
        }

    </style>
</head>

<body>
<table class="header">
    <tr>
        <td><img src="{{ $company->logo_url }}" alt="{{ $company->company_name }}"
                 id="logo"/></td>
        <td align="right">@lang('email.dailyTimelogReport.subject') {{ \Carbon\Carbon::parse($date)->translatedFormat('Y-m-d') }}</td>
    </tr>
</table>

<table class="content">
    <thead>
    <tr>
        <th style="vertical-align: middle; text-align: left; max-width: 150px;">@lang('app.employee')</th>
        <th>@lang('modules.timeLogs.totalHours')</th>
    </tr>
    </thead>

    <tbody>

    @foreach ($employees as $key => $employee)
        @php
            $totalMinute = $employee['timelog'];
            $breakMinute = $employee['timelogBreaks'];

            $totalMinutes = $totalMinute - $breakMinute;

            $timeLog = \Carbon\CarbonInterval::formatHuman($totalMinutes);
            if($timeLog == '1s'){
                $timeLog = 0;
            }
        @endphp
        <tr>
            <td style="text-align: left;"> {!! ($key) !!} </td>
            <td>{{$timeLog}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
