@include('import.process-form', [
    'headingTitle' => __('app.importExcel') . ' ' . __('app.menu.lead'),
    'processRoute' => route('lead-contact.import.process'),
    'backRoute' => route('lead-contact.index'),
    'backButtonText' => __('app.backToLead'),
])
