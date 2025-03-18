<div class="modal-body text-center">
    <x-pie-chart id="pie-chart-fullscreen-{{ $chartId }}" :labels="$chartData['labels']" :values="$chartData['values']"
        :colors="$chartData['colors']" height="800" width="800" fullscreen="true"/>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal">@lang('app.close')</x-forms.button-cancel>
</div>
