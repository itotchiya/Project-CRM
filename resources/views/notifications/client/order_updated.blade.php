<x-cards.notification :notification="$notification"  :link="route('orders.show', $notification->data['id'])" :image="company()->logo_url"
    :title="__('email.order.updateSubject')" :text="$notification->data['order_number']"
    :time="$notification->created_at" />
