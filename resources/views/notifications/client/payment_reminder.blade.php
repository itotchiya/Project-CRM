@php
$notificationUser = \App\Models\Invoice::find($notification->data['id']);
@endphp
@if ($notificationUser)
    <x-cards.notification :notification="$notification" :link="route('invoices.show', $notification->data['id'])"
        :image="$notificationUser->client->image_url" :title="__('email.invoices.paymentReminder')"
        :text="$notification->data['heading']" :time="$notification->created_at" />
@endif
