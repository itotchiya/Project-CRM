@include('import.process-form', [
    'headingTitle' => __('app.importExcel') . ' ' . __('app.menu.attendance'),
    'processRoute' => route('attendances.import.process'),
    'backRoute' => route('attendances.index'),
    'backButtonText' => __('app.backToAttendance'),
])
