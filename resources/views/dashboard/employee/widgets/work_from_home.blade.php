@if (in_array('work_from_home', $activeWidgets) && $sidebarUserPermissions['view_attendance'] != 5 && $sidebarUserPermissions['view_attendance'] != 'none' && in_array('attendance', user_modules()))
    <!-- ON WORK FROM HOME START -->
    <div class="col-sm-12">
        <x-cards.data class="e-d-info mb-2" :title="__('modules.dashboard.workFromHome')" padding="false">
            <div class="row pr-20 ml-2">
                @forelse ($workFromHome as $totalWorkFromHome)
                    <div class="col-md-6 mb-2">
                        <x-employee :user="$totalWorkFromHome->user"/>
                    </div>
                @empty
                    <p class="shadow-none">
                        <x-cards.no-record icon="home" :message="__('messages.noRecordFound')"/>
                    </p>
                @endforelse

            </div>
        </x-cards.data>
    </div>
    <!-- ON WORK FROM HOME  END -->
@endif
