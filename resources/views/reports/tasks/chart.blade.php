@if (array_sum($chartData['values']) > 0)
    <a href="javascript:;" class="text-darkest-grey f-w-500 piechart-full-screen" data-chart-id="task-chart" data-chart-data="{{ json_encode($chartData) }}"><i class="fas fa-expand float-right mr-3"></i></a>
@endif
<x-pie-chart id="task-chart" :labels="$chartData['labels']" :values="$chartData['values']"
    :colors="$chartData['colors']" height="250" width="250" />
