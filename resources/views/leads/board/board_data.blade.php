@php
$addLeadPermission = user()->permission('add_deal');
$changeStatusPermission = user()->permission('change_deal_stages');
@endphp

@foreach ($result['boardColumns'] as $key => $column)
     @if ($column->userSetting && $column->userSetting->collapsed)
         <!-- MINIMIZED BOARD PANEL START -->
         <div class="minimized rounded bg-additional-grey border-grey mr-3">
             <!-- TASK BOARD HEADER START -->
             <div class="d-flex mt-4 mx-1 b-p-header align-items-center">
                 <a href="javascript:;" class="d-grid f-8 mb-3 text-lightest collapse-column"
                     data-column-id="{{ $column->id }}" data-type="maximize" data-toggle="tooltip" data-original-title=@lang('app.expand')>
                     <i class="fa fa-chevron-right ml-1"></i>
                     <i class="fa fa-chevron-left"></i>
                 </a>

                 <p class="mb-3 mx-0 f-15 text-dark-grey font-weight-bold"><i class="fa fa-circle mb-2 text-red"
                         style="color: {{ $column->label_color }}"></i>{{ $column->name }}
                </p>

                 <span class="b-p-badge bg-grey f-13 px-2 py-2 text-lightest font-weight-bold rounded d-inline-block" id="lead-column-count-{{ $column->id }}">{{ $column->deals_count }}</span>

             </div>
             <!-- TASK BOARD HEADER END -->

         </div>
         <!-- MINIMIZED BOARD PANEL END -->
     @else
         <!-- BOARD PANEL 2 START -->
         <div class="board-panel rounded bg-additional-grey border-grey mr-3">
             <!-- TASK BOARD HEADER START -->
             <div class="mx-3 mt-3 mb-1 b-p-header">
                <div class="d-flex">
                 <p class="mb-0 f-15 mr-3 text-dark-grey font-weight-bold text-truncate"><i class="fa fa-circle mr-2 text-yellow"
                         style="color: {{ $column->label_color }}"></i>{{ $column->name }}
                 </p>

                 <span
                     class="b-p-badge bg-grey f-13 px-2 text-lightest font-weight-bold rounded d-inline-block ml-1" id="lead-column-count-{{ $column->id }}">{{ $column->deals_count }}</span>

                 <span class="ml-auto d-flex align-items-center">

                     <a href="javascript:;" class="d-flex f-8 text-lightest mr-3 collapse-column"
                         data-column-id="{{ $column->id }}" data-type="minimize" data-toggle="tooltip" data-original-title=@lang('app.collapse')>
                         <i class="fa fa-chevron-right mr-1"></i>
                         <i class="fa fa-chevron-left"></i>
                     </a>
                    @if ($addLeadPermission != 'none' )

                        <div class="dropdown">
                            <button
                                class="btn bg-white btn-lg f-10 px-2 py-1 text-dark-grey  rounded  dropdown-toggle"
                                type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                                <i class="fa fa-ellipsis-h"></i>
                            </button>

                            <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                aria-labelledby="dropdownMenuLink" tabindex="0">
                                @if ($addLeadPermission != 'none')
                                    <a class="dropdown-item openRightModal"
                                        href="{{ route('deals.create') }}?column_id={{ $column->id }}">@lang('modules.deal.addDeal')
                                        </a>
                                @endif
                                    <hr class="my-1">
                                     <a class="dropdown-item edit-column"
                                    data-column-id="{{ $column->id }}" href="javascript:;">@lang('app.edit')</a>


                                @if (!$column->default  && $column->slug != 'generated' &&  $column->slug != 'win' && $column->slug != 'lost' )
                                    <a class="dropdown-item delete-column"
                                        data-column-id="{{ $column->id }}" href="javascript:;">@lang('app.delete')</a>
                                @endif
                            </div>
                        </div>
                    @endif

                 </span>
                </div>

                <div class="mr-3 ml-4 f-11 text-dark-grey">{{ currency_format($column->total_value, company()->currency_id) }}</div>
             </div>

             <!-- TASK BOARD HEADER END -->

             <!-- TASK BOARD BODY START -->
             <div class="b-p-body">
                 <!-- MAIN TASKS START -->
                 <div class="b-p-tasks" id="drag-container-{{ $column->id }}" data-column-id="{{ $column->id }}">
                    <div class="card rounded bg-white border-grey b-shadow-4 m-1 mb-3 no-task-card move-disable {{ (count($column['deals']) > 0) ? 'd-none' : '' }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-center py-3">
                                <p class="mb-0">
                                    <a href="{{ route('deals.create') }}?column_id={{ $column->id }}"
                                        class="text-dark-grey openRightModal"><i
                                            class="fa fa-plus mr-2"></i>@lang('modules.deal.addDeal')</a>
                                </p>
                            </div>
                        </div>
                    </div><!-- div end -->

                    @foreach ($column['deals'] as $lead)

                         <x-cards.lead-card :draggable="($changeStatusPermission == 'all') ? 'true' : 'false'" :lead="$lead" />
                    @endforeach
                </div>
     <!-- MAIN TASKS END -->
     @if ($column->deals_count > count($column['deals']))
         <!-- TASK BOARD FOOTER START -->
         <div class="d-flex m-3 justify-content-center">
             <a class="f-13 text-dark-grey f-w-500 load-more-tasks" data-column-id="{{ $column->id }}"
                 data-total-tasks="{{ $column->deals_count }}"
                 href="javascript:;">@lang('modules.tasks.loadMore')</a>
         </div>
         <!-- TASK BOARD FOOTER END -->
     @endif
     </div>
     <!-- TASK BOARD BODY END -->
     </div>
     <!-- BOARD PANEL 2 END -->
 @endif

 @endforeach

 <!-- Drag and Drop Plugin -->
 <script>
     var arraylike = document.getElementsByClassName('b-p-tasks');
     var containers = Array.prototype.slice.call(arraylike);
     var drake = dragula({
             containers: containers,
             moves: function(el, source, handle, sibling) {
                 if (el.classList.contains('move-disable') || !KTUtil.isDesktopDevice()) {
                     return false;
                 }

                 return true; // elements are always draggable by default
             },
         })
         .on('drag', function(el) {
             el.className = el.className.replace('ex-moved', '');
         }).on('drop', function(el) {
             el.className += ' ex-moved';
         }).on('over', function(el, container) {
             container.className += ' ex-over';
         }).on('out', function(el, container) {
             container.className = container.className.replace('ex-over', '');
         });

 </script>

 <script>
     drake.on('drop', function(element, target, source, sibling) {
         var elementId = element.id;
         $children = $('#' + target.id).children();
         var boardColumnId = $('#' + target.id).data('column-id');
         var movingTaskId = $('#' + element.id).data('task-id');
         var sourceBoardColumnId = $('#' + source.id).data('column-id');
         var sourceColumnCount = parseInt($('#lead-column-count-' + sourceBoardColumnId).text());
         var targetColumnCount = parseInt($('#lead-column-count-' + boardColumnId).text());

         var taskIds = [];
         var prioritys = [];

         $children.each(function(ind, el) {
             taskIds.push($(el).data('task-id'));
             prioritys.push($(el).index());
         });

         // update values for all tasks
         $.easyAjax({
             url: "{{ route('leadboards.update_index') }}",
             type: 'POST',
             container: '#taskboard-columns',
             blockUI: true,
             data: {
                 boardColumnId: boardColumnId,
                 movingTaskId: movingTaskId,
                 taskIds: taskIds,
                 prioritys: prioritys,
                 '_token': '{{ csrf_token() }}'
             },
             success: function() {
                let leadID = movingTaskId;
                let statusID = boardColumnId;

                if ($('#' + source.id + ' .task-card').length == 0) {
                    $('#' + source.id + ' .no-task-card').removeClass('d-none');
                }
                if ($('#' + target.id + ' .task-card').length > 0) {
                    $('#' + target.id + ' .no-task-card').addClass('d-none');
                }

                $('#lead-column-count-' + sourceBoardColumnId).text(sourceColumnCount - 1);
                $('#lead-column-count-' + boardColumnId).text(targetColumnCount + 1);

                $.easyAjax({
                    url: "{{ route('leadboards.get_stage_slug') }}",
                    type: 'Post',
                    data: {
                        statusID: statusID,
                        '_token': '{{ csrf_token() }}'
                    },
                    success:function(response) {
                        if (response.slug === 'win' || response.slug === 'lost') {
                            var modalUrl = "{{ route('deals.stage_change', ':id')}}?via=deal&leadID=" + leadID + "&statusID=" + statusID;
                            modalUrl = modalUrl.replace(':id', leadID);
                            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
                            $.ajaxModal(MODAL_LG, modalUrl);
                            return;
                        }
                    }
                });
             }
         });

     });

 </script>
