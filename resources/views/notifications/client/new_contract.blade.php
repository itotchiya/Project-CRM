<x-cards.notification :notification="$notification"  :link="route('contracts.show', $notification->data['id'])" :image="company()->logo_url"
    :title="__('email.newContract.subject')" :time="$notification->created_at" />
