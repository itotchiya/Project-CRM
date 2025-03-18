@include('import.process-form', [
    'headingTitle' => __('app.importExcel') . ' ' . __('app.client'),
    'processRoute' => route('clients.import.process'),
    'backRoute' => route('clients.index'),
    'backButtonText' => __('app.backToClient'),
])
