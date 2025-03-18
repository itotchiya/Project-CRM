@if (in_array('appreciation', $activeWidgets) && isset($sidebarUserPermissions['view_appreciation']) && $sidebarUserPermissions['view_appreciation'] != 5 && in_array('employees', user_modules()))
    <!-- EMP DASHBOARD APPRECIATION START -->
    <div class="col-sm-12">
        <x-cards.data class="e-d-info mb-2" :title="__('app.employee').' '.__('modules.dashboard.appreciation')" padding="false" otherClasses="h-200">
            <x-table class="appreciation-table">
                @forelse ($appreciations as $appreciation)
                    <tr>
                        <td>
                            <x-employee :user="$appreciation->awardTo" />
                        </td>
                        <td class="text-right pr-20">
                            <div class="d-flex justify-content-end" data-toggle="tooltip" data-original-title="">
                                @if(isset($appreciation->award))
                                    <div class="ml-1 f-12 mr-3">
                                        <span class="font-weight-semibold">{{ $appreciation->award->title }}</span><br>
                                        {{ $appreciation->award_date->translatedFormat($company->date_format) }}
                                    </div>
                                @endif
                                <x-award-icon :award="$appreciation->award" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="shadow-none">
                            <x-cards.no-record icon="award" :message="__('messages.noRecordFound')" />
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </x-cards.data>
    </div>
    <!-- EMP DASHBOARD APPRECIATION END -->
@endif
