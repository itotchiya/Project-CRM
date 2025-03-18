<x-cards.notification :notification="$notification"  :link="route('invoice-recurring.show', $notification->data['id'])" :image="company()->logo_url"
    :title="__('email.invoiceRecurringStatus.subject')" :time="$notification->created_at" />
