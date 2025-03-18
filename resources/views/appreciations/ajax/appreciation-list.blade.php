@forelse ($appreciations as $appreciation)
    <tr>
        <td class="pl-20">
            <x-employee :user="$appreciation->awardTo" />
        </td>
        <td class="pr-20">

            {{ $appreciation->appreciationType->title }}  <i class="bi bi-{{ $appreciation->award->awardIcon->icon }}"></i>
        </td>
        <td class="pr-20">
            {{ $appreciation->award_date->translatedFormat($company->date_format) }}
        </td>

    </tr>
@empty
    <tr>
        <td colspan="3" class="shadow-none">
            <x-cards.no-record icon="trophy" :message="__('messages.noRecordFound')" />
        </td>
    </tr>
@endforelse
