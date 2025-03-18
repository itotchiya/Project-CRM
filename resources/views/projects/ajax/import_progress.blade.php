@include('import.process-form', [
    'headingTitle' => __('app.importExcel') . ' ' . __('app.menu.projects'),
    'processRoute' => route('projects.import.process'),
    'backRoute' => route('projects.index'),
    'backButtonText' => __('app.backToProject'),
])
