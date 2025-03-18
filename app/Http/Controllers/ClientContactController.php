<?php

namespace App\Http\Controllers;

use App\Enums\Salutation;
use App\Helper\Reply;
use App\Http\Requests\ClientContacts\StoreContact;
use App\Http\Requests\ClientContacts\UpdateContact;
use App\Models\ClientCategory;
use App\Models\ClientContact;
use App\Models\LanguageSetting;
use App\Models\Lead;
use App\Models\UniversalSearch;
use App\Models\User;
use App\Scopes\ActiveScope;
use Illuminate\Http\Request;
use App\Models\Notification;

class ClientContactController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.clients';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('clients', $this->user->modules));

            return $next($request);
        });
    }

    public function create()
    {
        $this->pageTitle = __('app.addContact');
        $this->addClientPermission = user()->permission('add_client_contacts');

        abort_403(!in_array($this->addClientPermission, ['all', 'added']));

        $this->clientId = request('client');
        $this->countries = countries();
        $this->categories = ClientCategory::all();
        $this->salutations = Salutation::cases();
        $this->languages = LanguageSetting::where('status', 'enabled')->get();
        $this->view = 'clients.contacts.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('clients.create', $this->data);
    }

    public function store(StoreContact $request)
    {
        $contact = ClientContact::create($request->all());

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => route('clients.show', $contact->user_id) . '?tab=contacts']);
    }

    public function show($id)
    {

        $this->contact = ClientContact::findOrFail($id);
        $this->pageTitle = __('app.showContact');

        $this->viewPermission = user()->permission('view_client_contacts');
        $this->editClientPermission = user()->permission('edit_client_contacts');
        $this->deleteClientPermission = user()->permission('delete_client_contacts');

        abort_403(!($this->viewPermission == 'all'
            || ($this->viewPermission == 'added' && $this->contact->client->clientDetails->added_by == user()->id)
            || ($this->viewPermission == 'both' && $this->contact->client->clientDetails->added_by == user()->id)));

        $this->client = User::withoutGlobalScope(ActiveScope::class)->findOrFail($this->contact->client_id);
        $this->view = 'clients.contacts.show';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('clients.create', $this->data);
        // return redirect(route('clients.show', $this->contact->user_id) . '?tab=contacts');

    }

    public function edit($id)
    {
        $this->pageTitle = __('app.editContact');
        $this->contact = ClientContact::findOrFail($id);
        $this->client = User::withoutGlobalScope(ActiveScope::class)->with('clientDetails')->findOrFail($this->contact->client_id);

        $this->editPermission = user()->permission('edit_client_contacts');


        abort_403(!($this->editPermission == 'all'
            || ($this->editPermission == 'added' && $this->contact->client->clientDetails->added_by == user()->id)
            || ($this->editPermission == 'both' && $this->contact->client->clientDetails->added_by == user()->id)));

        $this->countries = countries();
        $this->categories = ClientCategory::all();
        $this->salutations = Salutation::cases();
        $this->languages = LanguageSetting::where('status', 'enabled')->get();

        $this->clientId = $this->contact->user_id;


        $this->view = 'clients.contacts.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('clients.create', $this->data);

    }

    public function update(UpdateContact $request, $id)
    {
        $contact = ClientContact::findOrFail($id);
        $contact->update($request->all());

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('clients.show', $contact->user_id) . '?tab=contacts']);
    }

    public function destroy($id)
    {
        $this->contact = ClientContact::findOrFail($id);
        $userID = $this->contact->user_id;
        $this->deletePermission = user()->permission('delete_client_contacts');

        if (
            $this->deletePermission == 'all'
            || ($this->deletePermission == 'added' && $this->contact->client->clientDetails->added_by == user()->id)
            || ($this->deletePermission == 'both' && $this->contact->client->clientDetails->added_by == user()->id)
        ) {

            if(!is_null($this->contact->client_id)){

                $client = User::withoutGlobalScope(ActiveScope::class)->with('clientDetails')->findOrFail($this->contact->client_id);
                $universalSearches = UniversalSearch::where('searchable_id', $client->id)->where('module_type', 'client')->get();
                
                if ($universalSearches) {
                    foreach ($universalSearches as $universalSearch) {
                        UniversalSearch::destroy($universalSearch->id);
                    }
                }
                
                Notification::whereNull('read_at')
                ->where(function ($q) use ($client) {
                    $q->where('data', 'like', '{"id":' . $client->id . ',%');
                    $q->orWhere('data', 'like', '%,"name":' . $client->name . ',%');
                    $q->orWhere('data', 'like', '%,"user_one":' . $client->id . ',%');
                    $q->orWhere('data', 'like', '%,"client_id":' . $client->id . '%');
                })->delete();
                
                $client->delete();
                
                Lead::where('client_id', $client->id)->update(['client_id' => null]);
            }

            $this->contact->delete();
        }

        $redirectUrl = route('clients.show', $userID) . '?tab=contacts';
        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => $redirectUrl]);
    }

    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
        case 'delete':
            $this->deleteRecords($request);

            return Reply::success(__('messages.deleteSuccess'));
        default:
            return Reply::error(__('messages.selectAction'));
        }
    }

    protected function deleteRecords($request)
    {
        abort_403(user()->permission('delete_clients') !== 'all');
        ClientContact::whereIn('id', explode(',', $request->row_ids))->delete();

        return true;
    }

}
