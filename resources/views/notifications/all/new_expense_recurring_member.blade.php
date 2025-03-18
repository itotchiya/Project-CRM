@php
    use App\Models\User;$notificationUser = User::find($notification->data['user_id']);
@endphp

@if ($notificationUser)
    <x-cards.notification :notification="$notification" :link="route('expenses.show', $notification->data['id'])"
                          :image="$notificationUser->image_url"
                          :title="__('email.newExpenseRecurring.subject')" :text="$notification->data['item_name']"
                          :time="$notification->created_at"/>
@endif
