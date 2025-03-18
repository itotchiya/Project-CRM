@if(count($updateArray) > 0 && in_array('admin', user_roles()))
    <div class="row pt-2 d-none d-md-block">
        <div class="col-md-12">
            <x-alert type="danger">
                <div class="d-flex justify-content-between">
                    <div class="align-self-center">
                        <b>Note:-</b> Please update and reactivate  <b>{{implode(', ', $updateArray)}}</b>
                        @if(count($updateArray)>1) modules @else module @endif
                        to make the
                        modules work properly
                    </div>
                    <x-forms.link-primary :link="route('custom-modules.index').'?tab=custom'">Update Custom Modules</x-forms.link-primary>
                </div>
            </x-alert>
        </div>
    </div>
@endif
