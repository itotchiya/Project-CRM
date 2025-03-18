@php
    $iconCode = '<span class="align-items-center d-inline-flex height-40 justify-content-center rounded width-40" style="background-color: '.$notification->data['color_code'].'20;">
        <i class="bi bi-'.$notification->data['icon'].' f-15 text-white appreciation-icon" style="color: '.$notification->data['color_code'].'  !important"></i>
    </span>';
    $type = 'icon';
@endphp
<x-cards.notification :notification="$notification"
                      :link="route('appreciations.show', $notification->data['id'])"
                      :image="$iconCode"
                      :title="__('messages.congratulationNewAward', ['award' => $notification->data['heading']]) "
                      :time="$notification->created_at"
                      :type="$type"
/>
