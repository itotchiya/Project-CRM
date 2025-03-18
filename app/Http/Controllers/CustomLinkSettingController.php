<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\CustomLink\StoreCustomLink;
use App\Http\Requests\CustomLink\UpdateCustomLink;
use App\Models\CustomLinkSetting;
use App\Models\Role;
use Illuminate\Http\Request;

class CustomLinkSettingController extends AccountBaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.customLinkSetting';
        $this->activeSettingMenu = 'custom_link_settings';
        $this->middleware(function ($request, $next) {
            abort_403(user()->permission('manage_custom_link_setting') !== 'all');

            return $next($request);
        });
    }

    public function index()
    {
        $this->custom_links = CustomLinkSetting::all();

        $this->roles = Role::where('name', '<>', 'admin')->get();

        $this->view = 'custom-link-settings.ajax.custom-link-setting';

        $this->activeTab = 'custom-link-setting';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle, 'activeTab' => $this->activeTab]);
        }


        return view('custom-link-settings.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->roles = Role::where('name', '<>', 'admin')->get();

        return view('custom-link-settings.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreCustomLink  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCustomLink $request)
    {
        $custom_link = new CustomLinkSetting();
        $custom_link->link_title = $request->link_title;
        $custom_link->url = $request->url;
        $custom_link->can_be_viewed_by = json_encode($request->can_be_viewed_by);
        $custom_link->status = $request->status;
        $custom_link->save();

        session()->forget('custom_link_setting');

        return Reply::success(__('messages.recordSaved'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return redirect(route('custom-link-settings.edit', $id));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->custom_link = CustomLinkSetting::findOrFail($id);

        $this->roles = Role::where('name', '<>', 'admin')->get();

        return view('custom-link-settings.edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateCustomLink  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCustomLink $request, $id)
    {
        $custom_link = CustomLinkSetting::findOrFail($id);
        $custom_link->link_title = $request->link_title;
        $custom_link->url = $request->url;
        $custom_link->can_be_viewed_by = json_encode($request->can_be_viewed_by);
        $custom_link->status = $request->status;
        $custom_link->save();

        session()->forget('custom_link_setting');

        return Reply::success(__('messages.updateSuccess'));

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        CustomLinkSetting::destroy($id);

        session()->forget('custom_link_setting');

        return Reply::success(__('messages.deleteSuccess'));

    }

}
