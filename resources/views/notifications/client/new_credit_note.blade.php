<x-cards.notification :notification="$notification"  :link="route('creditnotes.show', $notification->data['id'])" :image="company()->logo_url"
    :title="__('email.creditNote.subject')" :text="$notification->data['cn_number']"
    :time="$notification->created_at" />
