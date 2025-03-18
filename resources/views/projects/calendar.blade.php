@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/full-calendar/main.min.css') }}">
    <style>
        .filter-box {
            z-index: 1;
        }
    </style>
@endpush

@section('filter-section')

    <x-filters.filter-box>
        <div class="select-box d-flex py-2  pr-2 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.clientName')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="client_id" id="client_id" data-live-search="true"
                    data-size="8">
                    @if (!in_array('client', user_roles()))
                        <option value="all">@lang('app.all')</option>
                    @endif
                    @foreach ($clients as $client)
                            <x-user-option :user="$client" />
                    @endforeach
                </select>
            </div>
        </div>

        <div class="select-box d-flex py-2 {{ !in_array('client', user_roles()) ? 'px-lg-2 px-md-2 px-0' : '' }}  border-right-grey border-right-grey-sm-0 pr-2 pl-2">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.status')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="status" id="status" data-live-search="true" data-size="8">
                    <option {{ request('status') == 'all' ? 'selected' : '' }} value="all">@lang('app.all')</option>
                    <option {{ request('status') == 'overdue' ? 'selected' : '' }} value="overdue">@lang('app.overdue')
                    </option>
                    @foreach ($projectStatus as $status)
                        <option value="{{$status->status_name}}">{{ $status->status_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="select-box d-flex py-2 {{ !in_array('client', user_roles()) ? 'px-lg-2 px-md-2 px-0' : '' }}  border-right-grey border-right-grey-sm-0 pr-2 pl-2">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.progress')</p>
            <div class="select-status mw-120">
                <x-forms.input-group>
                    <select class="select-picker form-control" multiple name="progress[]" id="progress" data-live-search="true" data-size="8">
                        <option value="0-20" selected>0% - 20%</option>
                        <option value="21-40" selected>21% - 40%</option>
                        <option value="41-60" selected>41% - 60%</option>
                        <option value="61-80" selected>61% - 80%</option>
                        <option value="81-99" selected>81% - 99%</option>
                        <option value="100-100" {{ request()->projects == 'all' ? 'selected' : ''}}>100%</option>
                    </select>
                </x-forms.input-group>
            </div>
        </div>

        <!-- SEARCH BY TASK START -->
        <div class="task-search d-flex  py-1 px-lg-3 px-0 border-right-grey align-items-center">
            <form class="w-100 mr-1 mr-lg-0 mr-md-1 ml-md-1 ml-0 ml-lg-0">
                <div class="input-group bg-grey rounded">
                    <div class="input-group-prepend">
                        <span class="input-group-text border-0 bg-additional-grey">
                            <i class="fa fa-search f-13 text-dark-grey"></i>
                        </span>
                    </div>
                    <input type="text" class="form-control f-14 p-1 border-additional-grey" id="search-text-field"
                        placeholder="@lang('app.startTyping')">
                </div>
            </form>
        </div>
        <!-- SEARCH BY TASK END -->

        <!-- RESET START -->
        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-secondary class="btn-xs d-none" id="reset-filters" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>
        <!-- RESET END -->

        <!-- MORE FILTERS START -->
        <x-filters.more-filter-box>
            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 "
                    for="usr">@lang('modules.projects.projectCategory')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="category_id" id="category_id"
                            data-live-search="true" data-container="body" data-size="8">
                            <option selected value="all">@lang('app.all')</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>


            @if (!in_array('client', user_roles()))
                <div class="more-filter-items">
                    <label class="f-14 text-dark-grey mb-12 " for="usr">@lang('app.projectMember')</label>
                    <div class="select-filter mb-4">
                        <div class="select-others">
                            <select class="form-control select-picker" name="employee_id" id="employee_id"
                                data-live-search="true" data-container="body" data-size="8">
                                @if ($allEmployees->count() > 1)
                                    <option value="all">@lang('app.all')</option>
                                @endif
                                @foreach ($allEmployees as $employee)
                                        <x-user-option :user="$employee" :selected="request('assignee') == 'me' && $employee->id == user()->id"/>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            @endif

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 " for="usr">@lang('app.department')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="team_id" id="team_id" data-live-search="true"
                            data-container="body" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->team_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 " for="usr">@lang('app.pinned')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="pinned" id="pinned" data-container="body"
                            data-size="8">
                            <option value="all">@lang('app.all')</option>
                            <option value="pinned">@lang('app.pinned')</option>
                        </select>
                    </div>
                </div>
            </div>
        </x-filters.more-filter-box>
        <!-- MORE FILTERS END -->

    </x-filters.filter-box>
@endsection

    @php
    $addProjectPermission = user()->permission('add_projects');
    $manageProjectTemplatePermission = user()->permission('manage_project_template');
    $viewProjectTemplatePermission = user()->permission('view_project_template');
    $deleteProjectPermission = user()->permission('delete_projects');
    @endphp
@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        <!-- Add Task Export Buttons Start -->
        <div class="d-grid d-lg-flex d-md-flex action-bar mb-2">
            <div id="table-actions" class="flex-grow-1 align-items-center mb-2 mb-lg-0 mb-md-0">
                @if ($addProjectPermission == 'all' || $addProjectPermission == 'added' || $addProjectPermission == 'both')
                    <x-forms.link-primary :link="route('projects.create')"
                        class="mr-3 openRightModal float-left mb-2 mb-lg-0 mb-md-0" icon="plus">
                        @lang('app.addProject')
                    </x-forms.link-primary>
                @endif
                @if ($viewProjectTemplatePermission == 'all' || in_array($manageProjectTemplatePermission , ['added', 'all']))
                    <x-forms.link-secondary :link="route('project-template.index')"
                        class="mr-3 mb-2 mb-lg-0 mb-md-0 float-left" icon="layer-group">
                        @lang('app.menu.projectTemplate')
                    </x-forms.link-secondary>
                @endif


                @if ($addProjectPermission == 'all' || $addProjectPermission == 'added' || $addProjectPermission == 'both')
                    <x-forms.link-secondary :link="route('projects.import')" class="mr-3 float-left mb-2 mb-lg-0 mb-md-0 d-none d-lg-block" icon="file-upload">
                        @lang('app.importExcel')
                    </x-forms.link-secondary>
                @endif

            </div>

            @if (!in_array('client', user_roles()))
                <x-datatable.actions>
                    <div class="select-status mr-3 pl-3">
                        <select name="action_type" class="form-control select-picker" id="quick-action-type" disabled>
                            <option value="">@lang('app.selectAction')</option>
                            <option value="change-status">@lang('modules.tasks.changeStatus')</option>
                            <option value="archive">@lang('app.archive')</option>
                            <option value="delete">@lang('app.delete')</option>
                        </select>
                    </div>
                    <div class="select-status mr-3 d-none quick-action-field" id="change-status-action">
                        <select name="status" class="form-control select-picker">
                            <option value="not started">@lang('app.notStarted')</option>
                            <option value="in progress">@lang('app.inProgress')</option>
                            <option value="on hold">@lang('app.onHold')</option>
                            <option value="canceled">@lang('app.canceled')</option>
                            <option value="finished">@lang('app.finished')</option>
                        </select>
                    </div>
                </x-datatable.actions>
            @endif

            <div class="btn-group mt-2 mt-lg-0 mt-md-0 ml-0 ml-lg-3 ml-md-3" role="group">
                <a href="{{ route('projects.index') }}" class="btn btn-secondary f-14 projects" data-toggle="tooltip"
                    data-original-title="@lang('app.menu.projects')"><i class="side-icon bi bi-list-ul"></i></a>

                    @if ($deleteProjectPermission != 'none')
                        <a href="{{ route('projects.archive') }}" class="btn btn-secondary f-14" data-toggle="tooltip"
                            data-original-title="@lang('app.archive')"><i class="side-icon bi bi-archive"></i></a>
                    @endif
                    <a href="{{ route('project-calendar.index') }}" class="btn btn-secondary f-14 btn-active" data-toggle="tooltip"
                    data-original-title="@lang('app.menu.calendar')"><i class="side-icon bi bi-calendar"></i></a>
            </div>
        </div>
        <x-cards.data>
            <div id="calendar"></div>
        </x-cards.data>
    </div>
    <!-- CONTENT WRAPPER END -->

@endsection

@push('scripts')
    <script src="{{ asset('vendor/full-calendar/main.min.js') }}"></script>
    <script src="{{ asset('vendor/full-calendar/locales-all.min.js') }}"></script>

    <script>

        $('#client_id, #status, #employee_id, #team_id, #category_id, #pinned, #progress').on('change keyup',
            function() {
                if ($('#status').val() != "not finished") {
                    $('#reset-filters').removeClass('d-none');
                    loadData();
                } else if ($('#employee_id').val() != "all") {
                    $('#reset-filters').removeClass('d-none');
                    loadData();
                } else if ($('#team_id').val() != "all") {
                    $('#reset-filters').removeClass('d-none');
                    loadData();
                } else if ($('#category_id').val() != "all") {
                    $('#reset-filters').removeClass('d-none');
                    loadData();
                } else if ($('#client_id').val() != "all") {
                    $('#reset-filters').removeClass('d-none');
                    loadData();
                } else if ($('#pinned').val() != "all") {
                    $('#reset-filters').removeClass('d-none');
                    loadData();
                } else if ($('#progress').val() != "all") {
                    $('#reset-filters').removeClass('d-none');
                    loadData();
                } else {
                    $('#reset-filters').addClass('d-none');
                    loadData();
                }
            });

        $('#search-text-field').on('keyup', function() {
            if ($('#search-text-field').val() != "") {
                $('#reset-filters').removeClass('d-none');
                loadData();
            }
        });

        $('#reset-filters,#reset-filters-2').click(function() {
            $('#filter-form')[0].reset();
            $('.filter-box .select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');

            loadData();
        });

        $('.show-pinned').click(function() {
            $('.projects').removeClass('btn-active');
            if ($(this).hasClass('btn-active')) {
                $('#pinned').val('all');
            } else {
                $('#pinned').val('pinned');
            }

            $('#pinned').selectpicker('refresh');
            $(this).toggleClass('btn-active')
            $('#reset-filters').removeClass('d-none');
        });


        var initialLocaleCode = '{{ user()->locale }}';
        var calendarEl = document.getElementById('calendar');

        var calendar = new FullCalendar.Calendar(calendarEl, {
            locale: initialLocaleCode,
            initialView: 'listWeek',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            // initialDate: '2020-09-12',
            navLinks: true, // can click day/week names to navigate views
            selectable: false,
            selectMirror: true,
            select: function(arg) {
                calendar.unselect()
            },
            eventClick: function(arg) {
                var url = "{{ route('projects.show', ':id') }}";
                url = url.replace(':id', arg.event.id);
                window.location.href = url;
            },
            editable: false,
            dayMaxEvents: true, // allow "more" link when too many events
            // events: "{{ route('task-calendar.index') }}"
            events: {
                url: "{{ route('project-calendar.index') }}",
                extraParams: function() {
                    var clientID = $('#client_id').val();
                    var categoryId = $('#category_id').val();
                    var progress = $('#progress').val();
                    var searchText = $('#search-text-field').val();
                    var status = $('#status').val();
                    var employeeId = $('#employee_id').val();
                    var teamId = $('#team_id').val();
                    var pinned = $('#pinned').val();

                    return {
                        clientID: clientID,
                        progress: progress,
                        categoryId: categoryId,
                        employeeId: employeeId,
                        status: status,
                        teamId: teamId,
                        searchText: searchText,
                        pinned: pinned
                    };
                }
            },
            eventTimeFormat: {
                hour: company.time_format == 'H:i' ? '2-digit' : 'numeric',
                minute: '2-digit',
                meridiem: company.time_format == 'H:i' ? false : true
            }
        });

        calendar.render();

        function loadData() {
            calendar.refetchEvents();
            calendar.destroy();
            calendar.render();
        }

    </script>
@endpush
