<x-cards.notification :notification="$notification"
                      :link="route('deals.show', $notification->data['id']).'?tab=follow-up'"
                      :image="company()->logo_url"
                      :title="__('email.followUpReminder.subject') . ' #' . $notification->data['id']"
                      :time="$notification->created_at"/>
