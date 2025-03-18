<x-cards.notification :notification="$notification"  :link="route('invoices.show', $notification->data['id'])" :image="company()->logo_url"
    :title="__('email.invoice.subject')" :text="$notification->data['invoice_number']"
    :time="$notification->created_at" />
