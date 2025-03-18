@if (in_array('projects', $activeWidgets) && $sidebarUserPermissions['view_projects'] != 5 && $sidebarUserPermissions['view_projects'] != 'none' && in_array('projects', user_modules()))
    <div class="col-md-6 mb-3">
        <div
            class="bg-white p-20 rounded b-shadow-4 d-flex justify-content-between align-items-center mt-3 mt-lg-0 mt-md-0">
            <div class="d-block ">
                <h5 class="f-15 f-w-500 mb-20 text-darkest-grey"> @lang('app.menu.projects') </h5>
                <div class="d-flex">
                    <a href="{{ route('projects.index') . '?assignee=me&status=in progress' }}">
                        <p class="mb-0 f-21 font-weight-bold text-blue d-grid mr-5">
                            {{ $totalProjects }}<span
                                class="f-12 font-weight-normal text-lightest">@lang('app.inProgress')</span>
                        </p>
                    </a>

                    <a href="{{ route('projects.index') . '?assignee=me&status=overdue' }}">
                        <p class="mb-0 f-21 font-weight-bold text-red d-grid">
                            {{ $dueProjects }}<span
                                class="f-12 font-weight-normal text-lightest">@lang('app.overdue')</span>
                        </p>
                    </a>
                </div>
            </div>
            <div class="d-block">
                <i class="fa fa-layer-group text-lightest f-27"></i>
            </div>
        </div>
    </div>
@endif
