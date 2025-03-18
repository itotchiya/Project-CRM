<x-cards.notification :notification="$notification" :link="route('dashboard')"
    :image="user()->image_url"
    :title="__('email.newUser.subject') . ' ' . $notification->data['name']" :text="__('email.newUser.message')"
    :time="$notification->created_at"/>
