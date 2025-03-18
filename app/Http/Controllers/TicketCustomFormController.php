<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\TicketCustomForm;
use Illuminate\Http\Request;

class TicketCustomFormController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'modules.ticketForm';
        $this->middleware(function ($request, $next) {
            if (!in_array('tickets', $this->user->modules)) {
                abort(403);
            }

            return $next($request);
        });
    }

    public function index()
    {
        $this->ticketFormFields = TicketCustomForm::get();

        return view('tickets.ticket-form.index', $this->data);
    }

    /**
     * update record
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
            TicketCustomForm::where('id', $id)->update([
                'status' => $request->status
            ]);

        return Reply::dataOnly([]);
    }

    /**
     * sort fields order
     *
     * @return \Illuminate\Http\Response
     */
    public function sortFields()
    {
        $sortedValues = request('sortedValues');

        foreach ($sortedValues as $key => $value) {
            TicketCustomForm::where('id', $value)->update(['field_order' => $key + 1]);
        }

        return Reply::dataOnly([]);
    }

}
