<?php

namespace App\Http\Controllers;

use App\Models\Tax;
use App\Helper\Files;
use App\Helper\Reply;
use App\Models\Order;
use App\Models\Product;
use App\Models\UnitType;
use App\Models\OrderCart;
use App\Models\ProductFiles;
use Illuminate\Http\Request;
use App\Imports\ProductImport;
use App\Jobs\ImportProductJob;
use App\Models\ProductCategory;
use App\Models\ProductSubCategory;
use App\DataTables\ProductsDataTable;
use App\Http\Controllers\AccountBaseController;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Admin\Employee\ImportRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Requests\Admin\Employee\ImportProcessRequest;
use App\Traits\ImportExcel;

class ProductController extends AccountBaseController
{
    use ImportExcel;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.products';
        $this->middleware(
            function ($request, $next) {
                in_array('client', user_roles()) ? abort_403(!(in_array('orders', $this->user->modules) && user()->permission('add_order') == 'all')) : abort_403(!in_array('products', $this->user->modules));

                return $next($request);
            }
        );
    }

    /**
     * @param  ProductsDataTable $dataTable
     * @return mixed|void
     */
    public function index(ProductsDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_product');
        abort_403(!in_array($viewPermission, ['all', 'added']));

        $productDetails = [];
        $productDetails = OrderCart::all();
        $this->productDetails = $productDetails;

        $this->totalProducts = Product::count();
        $this->cartProductCount = OrderCart::where('client_id', user()->id)->count();

        $this->categories = ProductCategory::all();
        $this->subCategories = ProductSubCategory::all();
        $this->unitTypes = UnitType::all();

        return $dataTable->render('products.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->pageTitle = __('app.menu.addProducts');

        $this->addPermission = user()->permission('add_product');
        abort_403(!in_array($this->addPermission, ['all', 'added']));
        $this->taxes = Tax::all();
        $this->categories = ProductCategory::all();

        $product = new Product();

        $this->unit_types = UnitType::all();

        $getCustomFieldGroupsWithFields = $product->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }
        $this->view = 'products.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('products.create', $this->data);
    }

    /**
     *
     * @param  StoreProductRequest $request
     * @return void
     */
    public function store(StoreProductRequest $request)
    {
        $this->addPermission = user()->permission('add_product');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $product = new Product();
        $product->name = $request->name;
        $product->price = $request->price;
        $product->taxes = $request->tax ? json_encode($request->tax) : null;
        $product->description = trim_editor($request->description);
        $product->hsn_sac_code = $request->hsn_sac_code;
        $product->sku = $request->sku;
        $product->unit_id = $request->unit_type;
        $product->allow_purchase = $request->purchase_allow == 'no';
        $product->downloadable = $request->downloadable == 'true';
        $product->category_id = ($request->category_id) ?: null;
        $product->sub_category_id = ($request->sub_category_id) ?: null;

        if (request()->hasFile('downloadable_file') && request()->downloadable == 'true') {
            Files::deleteFile($product->downloadable_file, ProductFiles::FILE_PATH);
            $product->downloadable_file = Files::uploadLocalOrS3(request()->downloadable_file, ProductFiles::FILE_PATH);
        }

        $product->save();

        // To add custom fields data
        if ($request->custom_fields_data) {
            $product->updateCustomFieldData($request->custom_fields_data);
        }


        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('products.index');
        }

        if($request->add_more == 'true') {
            $html = $this->create();

            return Reply::successWithData(__('messages.recordSaved'), ['html' => $html, 'add_more' => true, 'productID' => $product->id, 'defaultImage' => $request->default_image ?? 0]);
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl, 'productID' => $product->id, 'defaultImage' => $request->default_image ?? 0]);

    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->product = Product::with('category', 'subCategory')->findOrFail($id)->withCustomFields();
        $this->viewPermission = user()->permission('view_product');
        abort_403(!($this->viewPermission == 'all' || ($this->viewPermission == 'added' && $this->product->added_by == user()->id)));

        $this->taxes = Tax::withTrashed()->get();
        $this->pageTitle = $this->product->name;

        $this->taxValue = '';
        $taxes = [];

        foreach ($this->taxes as $tax) {
            if ($this->product && isset($this->product->taxes) && array_search($tax->id, json_decode($this->product->taxes)) !== false) {
                $taxes[] = $tax->tax_name . ' : ' . $tax->rate_percent . '%';
            }
        }

        $this->taxValue = implode(', ', $taxes);

        $getCustomFieldGroupsWithFields = $this->product->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->view = 'products.ajax.show';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('products.create', $this->data);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->product = Product::findOrFail($id)->withCustomFields();

        $this->editPermission = user()->permission('edit_product');
        abort_403(!($this->editPermission == 'all' || ($this->editPermission == 'added' && $this->product->added_by == user()->id)));

        $this->taxes = Tax::all();
        $this->categories = ProductCategory::all();
        $this->unit_types = UnitType::all();
        $this->subCategories = !is_null($this->product->sub_category_id) ? ProductSubCategory::where('category_id', $this->product->category_id)->get() : [];
        $this->pageTitle = __('app.update') . ' ' . __('app.menu.products');


        $images = [];

        if (isset($this->product) && isset($this->product->files)) {
            foreach ($this->product->files as $file) {
                $image['id'] = $file->id;
                $image['name'] = $file->filename;
                $image['hashname'] = $file->hashname;
                $image['file_url'] = $file->file_url;
                $image['size'] = $file->size;
                $images[] = $image;
            }
        }

        $this->images = json_encode($images);

        $getCustomFieldGroupsWithFields = $this->product->getCustomFieldGroupsWithFields();

        if ($getCustomFieldGroupsWithFields) {
            $this->fields = $getCustomFieldGroupsWithFields->fields;
        }

        $this->view = 'products.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('products.create', $this->data);

    }

    /**
     * @param  UpdateProductRequest $request
     * @param  int                  $id
     * @return array|void
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(UpdateProductRequest $request, $id)
    {
        $product = Product::findOrFail($id)->withCustomFields();
        $this->editPermission = user()->permission('edit_product');

        abort_403(!($this->editPermission == 'all' || ($this->editPermission == 'added' && $product->added_by == user()->id)));

        $product->name = $request->name;
        $product->price = $request->price;
        $product->taxes = $request->tax ? json_encode($request->tax) : null;
        $product->hsn_sac_code = $request->hsn_sac_code;
        $product->sku = $request->sku;
        $product->unit_id = $request->unit_type;
        $product->description = trim_editor($request->description);
        $product->allow_purchase = ($request->purchase_allow == 'no') ? true : false;
        $product->downloadable = ($request->downloadable == 'true') ? true : false;
        $product->category_id = ($request->category_id) ? $request->category_id : null;
        $product->sub_category_id = ($request->sub_category_id) ? $request->sub_category_id : null;

        if (request()->hasFile('downloadable_file') && request()->downloadable == 'true') {
            Files::deleteFile($product->downloadable_file, ProductFiles::FILE_PATH);
            $product->downloadable_file = Files::uploadLocalOrS3(request()->downloadable_file, ProductFiles::FILE_PATH);
        }
        elseif (request()->downloadable == 'true' && $product->downloadable_file == null) {
            $product->downloadable = false;
        }

        // change default image
        if (!request()->hasFile('file')) {
            $product->default_image = request()->default_image;
        }

        $product->save();

        // To add custom fields data
        if ($request->custom_fields_data) {
            $product->updateCustomFieldData($request->custom_fields_data);
        }

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('products.index'), 'defaultImage' => $request->default_image ?? 0]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $products = Product::findOrFail($id);
        $this->deletePermission = user()->permission('delete_product');
        abort_403(!($this->deletePermission == 'all' || ($this->deletePermission == 'added' && $products->added_by == user()->id)));

        $products->delete();

        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('products.index')]);
    }

    /**
     * XXXXXXXXXXX
     *
     * @return array
     */
    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
        case 'delete':
            $this->deleteRecords($request);

            return Reply::success(__('messages.deleteSuccess'));
        case 'change-purchase':
            $this->allowPurchase($request);

            return Reply::success(__('messages.updateSuccess'));
        default:
            return Reply::error(__('messages.selectAction'));
        }
    }

    protected function deleteRecords($request)
    {
        abort_403(user()->permission('delete_product') != 'all');

        Product::whereIn('id', explode(',', $request->row_ids))->forceDelete();
    }

    protected function allowPurchase($request)
    {
        abort_403(user()->permission('edit_product') != 'all');

        Product::whereIn('id', explode(',', $request->row_ids))->update(['allow_purchase' => $request->status]);
    }

    public function addCartItem(Request $request)
    {
        $newItem = $request->productID;

           $orderExist = OrderCart::where('product_id', '=', $newItem)->where('client_id', user()->id)->exists();
           $quantity = 1;

        if ($orderExist == true) {
                $orderCartUpdate = OrderCart::where('product_id', '=', $newItem)->where('client_id', user()->id)->first();

                $quantity = ($request->has('quantity')) ? $request->quantity : $orderCartUpdate->quantity + 1;

                OrderCart::where('product_id', '=', $newItem)->where('client_id', user()->id)->update(
                    [
                        'quantity' => $quantity,
                        'amount' => $orderCartUpdate->unit_price * $quantity,
                    ]
                );
            $productDetails[] = OrderCart::where('product_id', '=', $newItem)->first();

        } else {
            $productDetails = Product::where('id', $newItem)->first();

            $product = new OrderCart();
            $product->product_id = $productDetails->id;
            $product->client_id = user()->id;
            $product->item_name = $productDetails->name;
            $product->description = $productDetails->description;
            $product->type = 'item';
            $product->quantity = $quantity;
            $product->unit_price = $productDetails->price;
            $product->amount = $productDetails->price;
            $product->taxes = $productDetails->taxes;
            $product->unit_id = $productDetails->unit_id;
            $product->hsn_sac_code = $productDetails->hsn_sac_code;
            $product->save();
            $productDetails = $product;

        }

        $cartProduct = OrderCart::where('client_id', user()->id)->count();

        if (!$request->has('cartType')) {

            return response(Reply::successWithData(__('messages.recordSaved'), ['status' => 'success', 'cartProduct' => $cartProduct, 'productItems' => $productDetails]));

        }

    }

    public function removeCartItem(Request $request, $id)
    {
        if ($request->type == 'all_data') {

            $products = OrderCart::where('client_id', $id)->delete();
            $productDetails = OrderCart::where('client_id', $id)->count();

        } else {

            $products = OrderCart::findOrFail($id);
            $products->delete();

            $productDetails = OrderCart::where('id', $id)->where('client_id', user()->id)->get();
        }


        return response(Reply::successWithData(__('messages.deleteSuccess'), ['status' => 'success', 'productItems' => $productDetails ]))->cookie('productDetails', json_encode($productDetails));

    }

    public function emptyCart()
    {

        $this->view = 'products.ajax.empty_cart';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('products.create', $this->data);

    }

    public function cart(Request $request)
    {
        abort_403(!in_array('client', user_roles()));

        $this->lastOrder = Order::lastOrderNumber() + 1;
        $this->invoiceSetting = invoice_setting();
        $this->taxes = Tax::all();

        $this->products = OrderCart::where('client_id', '=', user()->id)->get();

        $this->view = 'products.ajax.cart';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('products.create', $this->data);
    }

    public function allProductOption()
    {
        $products = Product::all();

        $option = '';

        foreach ($products as $item) {
            $option .= '<option data-content="' . $item->name . '" value="' . $item->id . '"> ' . $item->name . '</option>';
        }

        return Reply::dataOnly(['products' => $option]);
    }

    public function importProduct()
    {
        $this->pageTitle = __('app.importExcel') . ' ' . __('app.menu.product');

        $this->addPermission = user()->permission('add_product');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->view = 'products.ajax.import';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('products.create', $this->data);
    }

    public function importStore(ImportRequest $request)
    {
        $rvalue = $this->importFileProcess($request, ProductImport::class);

        if($rvalue == 'abort'){
            return Reply::error(__('messages.abortAction'));
        }
        
        $view = view('products.ajax.import_progress', $this->data)->render();

        return Reply::successWithData(__('messages.importUploadSuccess'), ['view' => $view]);
    }

    public function importProcess(ImportProcessRequest $request)
    {
        $batch = $this->importJobProcess($request, ProductImport::class, ImportProductJob::class);

        return Reply::successWithData(__('messages.importProcessStart'), ['batch' => $batch]);
    }

}
