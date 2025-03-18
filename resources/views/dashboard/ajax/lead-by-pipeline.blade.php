@if (array_sum($leadStatusChart['values']) > 0)
    <a href="javascript:;" class="text-darkest-grey f-w-500 piechart-full-screen" data-chart-id="task-chart3" data-chart-data="{{ json_encode($leadStatusChart) }}"><i class="fas fa-expand float-right mr-3"></i></a>
@endif
<x-pie-chart id="task-chart3" :labels="$leadStatusChart['labels']" :values="$leadStatusChart['values']"
:colors="$leadStatusChart['colors']" height="300" width="300" />
