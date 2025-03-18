@include('import.process-form', [
    'headingTitle' => __('app.importExcel') . ' ' . __('app.menu.product'),
    'processRoute' => route('products.import.process'),
    'backRoute' => route('products.index'),
    'backButtonText' => __('app.backToProducts'),
])
