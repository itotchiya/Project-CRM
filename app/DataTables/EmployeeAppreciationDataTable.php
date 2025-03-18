<?php

namespace App\DataTables;

use App\Models\Award;
use App\Scopes\ActiveScope;

class EmployeeAppreciationDataTable extends BaseDataTable
{

    private $deleteDiscussionPermission;

    public function __construct()
    {
        parent::__construct();
        $this->deleteDiscussionPermission = user()->permission('delete_appreciation');
    }

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */

    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->editColumn('title', function ($row) {
                $lastReply = '';

                if (!is_null($row->last_reply_by_id)) {
                    $lastReply = '<a class="text-darkest-grey" href="' . route('employees.show', $row->last_reply_by_id) . '">' . $row->lastReplyBy->name . '</a> ';
                }

                $title = '<div class="row">';

                $title .= '<div class="col-sm-9">';
                $title .= '<div class="media align-items-center">
                        <img src="' . $row->user->image_url . '" height="40" width="40" class="mr-3 rounded">
                        <div class="media-body">
                        <h5 class="mb-1 f-15 text-darkest-grey"><a href="' . route('discussion.show', [$row->id]) . '" class="openRightModal">' . $row->title . '</a></h5>
                        <p class="mb-1">' . $lastReply . '</p>';

                $title .= '<span class="f-12 text-lightest">';

                if (count($row->replies) > 1) {
                    $title .= __('modules.discussions.replied');
                }
                else {
                    $title .= __('modules.discussions.posted');
                }

                $title .= ' ' . $row->last_reply_at->timezone(company()->timezone)->translatedFormat(company()->date_format . ' ' . company()->time_format) . '</span>';

                $title .= '</div>
                    </div>';

                $title .= '</div>';

                $title .= '<div class="col-sm-1">';
                $title .= '<div class="media align-items-center"><div class="media-body">';
                $title .= '<p class="f-15"><i class="icon-bubbles"></i> ' . count($row->replies) . '</p>';
                $title .= '</div></div>';
                $title .= '</div>';

                $title .= '<div class="col-sm-2 text-right">';
                $title .= '<div class="media align-items-center"><div class="media-body">';

                if (isset($row->category)) {
                    $title .= '<p class="mb-1"><i class="fa fa-circle" style="color: ' . $row->category->color . '"></i> ' . $row->category->name . '</p>';
                }

                if (
                    $this->deleteDiscussionPermission == 'all'
                    || ($this->deleteDiscussionPermission == 'added' && $row->added_by == user()->id)
                ) {
                    $title .= '<div class="dropdown ml-auto message-action">
                            <button class="btn btn-lg f-14 p-0 text-lightest text-capitalize rounded  dropdown-toggle"
                                type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-ellipsis-h"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                aria-labelledby="dropdownMenuLink" tabindex="0">
                                        <a class="cursor-pointer d-block text-dark-grey f-13 py-3 px-3 delete-discussion"
                                        data-discussion-id="' . $row->id . '" href="javascript:;">' . __('app.delete') . '</a>
                            </div>
                        </div>';
                    $title .= '</div>';
                }


                $title .= '</div></div>';
                $title .= '</div>';

                return $title;
            })
            ->rawColumns(['title']);
    }

    /**
     * Get query source of dataTable.
     * @param Award $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Award $model)
    {
        $model = $model->withoutGlobalScope(ActiveScope::class)->with('awardIcon')->select('id', 'award_icon_id', 'title', 'color_code', 'status');
        return $model;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $dataTable = $this->setBuilder('employee-appreciation-table', 0)
            ->parameters([
                'fnDrawCallback' => 'function( oSettings ) {
                    $("body").tooltip({
                        selector: \'[data-toggle="tooltip"]\'
                    })
                }',
            ]);

        return $dataTable;
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            __('app.title') => ['data' => 'title', 'name' => 'title', 'title' => __('app.title')]
        ];
    }

}
