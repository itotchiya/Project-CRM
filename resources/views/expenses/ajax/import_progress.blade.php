@include('import.process-form', [
    'headingTitle' => __('app.importExpense'),
    'processRoute' => route('expenses.import.process'),
    'backRoute' => route('expenses.index'),
    'backButtonText' => __('app.backToExpense'),
])
