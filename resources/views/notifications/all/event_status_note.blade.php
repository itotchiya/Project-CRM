<x-cards.notification :notification="$notification" :link="route('events.show', $notification->data['id'])"
    :image="user()->image_url" :title="__('email.newEvent.statusSubject')"
    :text="$notification->data['event_name']" :time="$notification->created_at"/>

