<div class='media align-items-center mw-250 @if($user->status != 'active') inactive @endif'>
    <div class='position-relative'><img src='{{ $user->image_url }}' class='mr-2 taskEmployeeImg rounded-circle'>
    </div>
    <div class='media-body text-truncate'>
        <h5 class='mb-0 f-13'>{{ $user->name_salutation }}@if($user->status!='active') <i data-toggle='tooltip' data-original-title='{{__('app.inactive')}}' class='fa fa-circle mr-1 text-red f-10'></i> @endif</h5>
        <p class='my-0 f-11 text-dark-grey'>{{ $user->email }}</p>
        <p class='my-0 f-11 text-dark-grey'>
            {{ !is_null($user->clientDetails) ? $user->clientDetails->company_name : ' ' }}</p>
    </div>
</div>
