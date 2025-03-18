<!-- ROW START -->
<div class="row pb-5">
    <div class="col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4">
        <!-- Add Task Export Buttons Start -->
        <div class="d-flex" id="table-actions">

        </div>
        <!-- Add Task Export Buttons End -->
        <!-- Task Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white">

            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}

        </div>
        <!-- Task Box End -->
    </div>
</div>

<script type="text/javascript">
    function getDate() {
        var start = moment().clone().startOf('month');
        var end = moment();

        $('#datatableRange2').daterangepicker({
            locale: daterangeLocale,
            linkedCalendars: false,
            startDate: start,
            endDate: end,
            ranges: daterangeConfig
        }, cb);
    }
    $(function() {
        getDate()
        $('#datatableRange2').on('apply.daterangepicker', function(ev, picker) {
            showTable();
        });

    });

    $('#lead-report-table').on('preXhr.dt', function(e, settings, data) {

        var dateRangePicker = $('#datatableRange2').data('daterangepicker');
        var startDate = $('#datatableRange2').val();

        if (startDate == '') {
            startDate = null;
            endDate = null;
        } else {
            startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
            endDate = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
        }

        var agent = $('#agent').val();

        data['startDate'] = startDate;
        data['endDate'] = endDate;
        data['agent'] = agent;

    });
    const showTable = () => {
        window.LaravelDataTables["lead-report-table"].draw(true);
    }

    $('#agent').on('change keyup',
        function() {
            if ($('#agent').val() != "all") {
                $('#reset-filters').removeClass('d-none');
                showTable();
            } else {
                $('#reset-filters').addClass('d-none');
                showTable();
            }
        });

    $('#reset-filters').click(function() {
        $('#filter-form')[0].reset();
        // getDate()

        $('.filter-box .select-picker').selectpicker("refresh");
        $('#reset-filters').addClass('d-none');
        showTable();
    });

    $('#reset-filters-2').click(function() {
        $('#filter-form')[0].reset();

        $('.filter-box .select-picker').selectpicker("refresh");
        $('#reset-filters').addClass('d-none');
        showTable();
    });
</script>
