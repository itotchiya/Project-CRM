<x-cards.notification :notification="$notification" :link="route('tasks.index')" :image="user()->image_url"
                      :title="__('email.trackerReminder.subject') . ' #' . $notification->data['id']"
                      :time="$notification->created_at"/>
