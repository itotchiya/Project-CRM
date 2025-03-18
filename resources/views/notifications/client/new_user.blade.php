<x-cards.notification :notification="$notification"  link="{{ route('dashboard') }}" :image="company()->logo_url"
    :title="__('app.welcome') . ' ' . __('app.to') . ' ' . $companyName . ' !'" :time="$notification->created_at" />
