@include('import.process-form', [
    'headingTitle' => __('app.importExcel') . ' ' . __('app.menu.deal'),
    'processRoute' => route('deals.import.process'),
    'backRoute' => route('deals.index'),
    'backButtonText' => __('app.backToDeal'),
])
