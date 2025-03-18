@props([
    'checkedDays'=>[],
    'fieldName'=>'',
    'selectedWeek'=>'',
    'fieldLabel'=>'',
    'fieldRequired'=>false,
    'type'=>'checkbox'
])

@if($type =='checkbox')

    @foreach(range(0,\Carbon\Carbon::DAYS_PER_WEEK-1) as $day)
        @php $index = ($day + attendance_setting()?->week_start_from)%7; @endphp
        <div {{ $attributes->merge(['class' => '']) }} >
            <x-forms.checkbox :fieldLabel="now()->startOfWeek($index)->translatedFormat('l')"
                              :fieldName="$fieldName"
                              :checked="in_array($index, $checkedDays)"
                              :fieldId="'open_'.$index" :fieldValue="$index"
            />
        </div>
    @endforeach
@else

    <x-forms.select :fieldLabel="$fieldLabel"
                    :fieldName="$fieldName"
                    :fieldId="$fieldName"
                    :fieldRequired="$fieldRequired">

        @foreach(range(0,\Carbon\Carbon::DAYS_PER_WEEK-1) as $day)
            @php $index = ($day + attendance_setting()?->week_start_from)%7; @endphp
            <option value="{{$day}}" @selected ($selectedWeek == $day)>
                {{now()->startOfWeek($day)->translatedFormat('l')}}
            </option>
        @endforeach

    </x-forms.select>
@endif
