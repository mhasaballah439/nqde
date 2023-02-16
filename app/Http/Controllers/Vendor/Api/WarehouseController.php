<?php

namespace App\Http\Controllers\Vendor\Api;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use App\Models\StockCategory;
use App\Models\StockInventoryTemplate;
use App\Models\StockProduction;
use App\Models\StockPurchase;
use App\Models\StockPurchaseOrderMaterial;
use App\Models\StockPurchaseOrders;
use App\Models\StockSupplier;
use App\Models\StockTag;
use App\Models\StoreHouse;
use App\Models\Supplier;
use App\Models\SupplierTag;
use App\Models\Tag;
use App\Traits\ApiTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WarehouseController extends Controller
{
    use ApiTrait;

    var $lang_code;

    public function __construct()
    {
        $this->lang_code = \request()->get('lang') ? \request()->get('lang') : get_default_languages();
    }

    public function stockCategories(Request $request)
    {
        $name = $request->get('name');
        $is_delete = $request->get('is_delete');
        $add_by = $request->get('add_by');
        $created_at = $request->get('created_at');

        $categories = StockCategory::where('vendor_id', vendor()->id)->Active();
        if ($name)
            $categories = $categories->where('name_ar', 'LIKE', '%' . $name . '%')
                ->where('name_en', 'LIKE', '%' . $name . '%');
        if ($is_delete == 1)
            $categories = $categories->withTrashed();
        if ($add_by)
            $categories = $categories->where('add_by', 'LIKE', '%' . $name . '%');
        if ($created_at)
            $categories = $categories->whereDate('created_at', $created_at);

        $categories = $categories->orderBy('id', 'DESC')->get();
        $data = $categories->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name($this->lang_code),
                'number' => $item->number,
                'status' => $item->status,
            ];
        });

        return $this->dataResponse(__('msg.cat_get_success', [], $this->lang_code), $data, 200);
    }

    public function stockTrashedCategories(Request $request)
    {
        $name = $request->get('name');
        $add_by = $request->get('add_by');
        $created_at = $request->get('created_at');

        $categories = StockCategory::where('vendor_id', vendor()->id)->onlyTrashed();
        if ($name)
            $categories = $categories->where('name_ar', 'LIKE', '%' . $name . '%')
                ->where('name_en', 'LIKE', '%' . $name . '%');
        if ($add_by)
            $categories = $categories->where('add_by', 'LIKE', '%' . $name . '%');
        if ($created_at)
            $categories = $categories->whereDate('created_at', $created_at);

        $categories = $categories->orderBy('id', 'DESC')->get();
        $data = $categories->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name($this->lang_code),
                'number' => $item->number,
                'status' => $item->status,
            ];
        });
        $msg = __('msg.cat_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $data, 200);
    }

    public function generateStokCategoryCode()
    {
        $last_item_id = 0;
        $last_item = StockCategory::where('vendor_id', vendor()->id)->withTrashed()->orderBy('id', 'DESC')->first();
        if($last_item){
            $num = explode('-',$last_item->number);
            $last_item_id = $num[1];
        }
        $data = [
            'operation_number' => 'SC-' . ($last_item_id + 1)
        ];
        return $this->dataResponse('generated successfully', $data);
    }

    public function addStokCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $last_item_id = 0;
        $last_item = StockCategory::where('vendor_id', vendor()->id)->withTrashed()->orderBy('id', 'DESC')->first();

        if($last_item){
            $num = explode('-',$last_item->number);
            $last_item_id = $num[1];
        }
        $cat = StockCategory::where('vendor_id', vendor()->id)
            ->where('id', $request->category_id)->first();
        if (!$cat)
            $cat = new StockCategory();
        $cat->vendor_id = vendor()->id;
        $cat->add_by = vendor()->name;
        if ($request->get('name_ar'))
            $cat->name_ar = $request->get('name_ar');
        if ($request->get('name_en'))
            $cat->name_en = $request->get('name_en');
        if (!$request->category_id)
            $cat->number = 'SC-' . ($last_item_id + 1);
        $cat->status = $request->get('status');
        $cat->save();
        $data = [
            'id' => $cat->id,
            'name' => $cat->name($this->lang_code),
            'number' => $cat->number,
            'status' => $cat->status,
        ];

        $msg = __('msg.cat_add_success', [], $this->lang_code);

        return $this->dataResponse($msg, $data, 200);
    }

    public function deleteStokCategory(Request $request)
    {

        $cat = StockCategory::where('vendor_id', vendor()->id)
            ->where('id', $request->get('category_id'))->first();
        if (!$cat)
            return $this->errorResponse('Category not found', 400);

        $cat->delete();

        $msg = __('msg.cat_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    ################## store houses########################
    public function storeHouse(Request $request)
    {
        $name = $request->get('name');
        $number = $request->get('number');
        $branches = $request->get('branches');
        $created_at = $request->get('created_at');
        $store_house = StoreHouse::where('vendor_id', vendor()->id);

        if ($name)
            $store_house = $store_house->where('name_ar', 'LIKE', '%' . $name . '%')
                ->where('name_en', 'LIKE', '%' . $name . '%');
        if ($number)
            $store_house = $store_house->where('number', 'LIKE', '%' . $number . '%');
        if ($branches)
            $store_house = $store_house->where('branches', $branches);
        if ($created_at)
            $store_house = $store_house->whereDate('created_at', $created_at);

        $store_house = $store_house->orderBy('id', 'DESC')->get();

        $data = $store_house->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name($this->lang_code),
                'number' => $item->number,
                'branches' => $item->branches,
                'status' => $item->status,
            ];
        });
        $msg = __('msg.store_house_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $data, 200);
    }

    public function activeStoreHouse(Request $request)
    {
        $name = $request->get('name');
        $number = $request->get('number');
        $branches = $request->get('branches');
        $created_at = $request->get('created_at');
        $store_house = StoreHouse::where('vendor_id', vendor()->id)->Active();

        if ($name)
            $store_house = $store_house->where('name_ar', 'LIKE', '%' . $name . '%')
                ->where('name_en', 'LIKE', '%' . $name . '%');
        if ($number)
            $store_house = $store_house->where('number', 'LIKE', '%' . $number . '%');
        if ($branches)
            $store_house = $store_house->where('branches', $branches);
        if ($created_at)
            $store_house = $store_house->whereDate('created_at', $created_at);

        $store_house = $store_house->orderBy('id', 'DESC')->get();

        $data = $store_house->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name($this->lang_code),
                'number' => $item->number,
                'branches' => $item->branches,
                'status' => $item->status,
            ];
        });
        $msg = __('msg.store_house_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $data, 200);
    }

    public function deactiveStoreHouse(Request $request)
    {
        $name = $request->get('name');
        $number = $request->get('number');
        $branches = $request->get('branches');
        $created_at = $request->get('created_at');
        $store_house = StoreHouse::where('vendor_id', vendor()->id)->where('status', 0);

        if ($name)
            $store_house = $store_house->where('name_ar', 'LIKE', '%' . $name . '%')
                ->where('name_en', 'LIKE', '%' . $name . '%');
        if ($number)
            $store_house = $store_house->where('number', 'LIKE', '%' . $number . '%');
        if ($branches)
            $store_house = $store_house->where('branches', $branches);
        if ($created_at)
            $store_house = $store_house->whereDate('created_at', $created_at);

        $store_house = $store_house->orderBy('id', 'DESC')->get();

        $data = $store_house->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name($this->lang_code),
                'number' => $item->number,
                'branches' => $item->branches,
                'status' => $item->status,
            ];
        });
        $msg = __('msg.store_house_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $data, 200);
    }

    public function generateStoreHouseCode()
    {
        $last_item_id = 0;
        $last_item = StoreHouse::where('vendor_id', vendor()->id)->withTrashed()->orderBy('id', 'DESC')->first();
        if($last_item){
            $num = explode('-',$last_item->number);
            $last_item_id = $num[1];
        }
        $data = [
            'operation_number' => 'SH-' . ($last_item_id + 1)
        ];
        return $this->dataResponse('generated successfully', $data);
    }

    public function addStoreHouse(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $last_item_id = 0;
        $last_item = StoreHouse::where('vendor_id', vendor()->id)->withTrashed()->orderBy('id', 'DESC')->first();
        if($last_item){
            $num = explode('-',$last_item->number);
            $last_item_id = $num[1];
        }
        $store = StoreHouse::where('vendor_id', vendor()->id)
            ->where('id', $request->get('store_house_id'))->first();
        if (!$store)
            $store = new StoreHouse();
        $store->vendor_id = vendor()->id;
        if ($request->get('name_ar'))
            $store->name_ar = $request->get('name_ar');
        if ($request->get('name_en'))
            $store->name_en = $request->get('name_en');
        if (!$store)
            $store->number = 'SH-' . ($last_item_id + 1);
        if ($request->get('branches'))
            $store->branches = $request->get('branches');
        $store->status = $request->get('status');
        $store->save();

        $data = [
            'id' => $store->id,
            'name' => $store->name($this->lang_code),
            'number' => $store->number,
            'branches' => $store->branches,
            'status' => $store->status,
        ];

        $msg = __('msg.store_house_add_success', [], $this->lang_code);

        return $this->dataResponse($msg, $data, 200);
    }

    public function deleteStoreHouse(Request $request)
    {

        $store = StoreHouse::where('vendor_id', vendor()->id)
            ->where('id', $request->get('store_house_id'))->first();
        if (!$store)
            return $this->errorResponse('Store house not found', 400);

        $store->delete();

        $msg = __('msg.store_house_delete_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    ########################### stoks ###################
    public function tagsList()
    {
        $data = [
            'customers' => $this->tage_type(1),
            'branches' => $this->tage_type(2),
            'stoks' => $this->tage_type(3),
            'orders' => $this->tage_type(4),
            'suppliers' => $this->tage_type(5),
            'users' => $this->tage_type(6),
            'products' => $this->tage_type(7),
            'devices' => $this->tage_type(8),
            'storehouses' => $this->tage_type(9),
        ];

        $msg = __('msg.tag_get_success', [], $this->lang_code);
        return $this->dataResponse($msg, $data);
    }

    public function generateTagsCode()
    {
        $last_item_id = 0;
        $last_item = Tag::where('vendor_id', vendor()->id)->orderBy('id', 'DESC')->first();
        if($last_item){
            $num = explode('-',$last_item->number);
            $last_item_id = $num[1];
        }
        $data = [
            'operation_number' => 'T-' . ($last_item_id + 1)
        ];
        return $this->dataResponse('generated successfully', $data);
    }

    public function tage_type($type)
    {
        return Tag::where('vendor_id', vendor()->id)->where('type', $type)->get();
    }

    public function createTag(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required',
            'type' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $last_item_id = 0;
        $last_item = Tag::where('vendor_id', vendor()->id)->orderBy('id', 'DESC')->first();
        if($last_item){
            $num = explode('-',$last_item->number);
            $last_item_id = $num[1];
        }
        $tag = Tag::where('vendor_id', vendor()->id)
            ->where('number', $request->get('number'))
            ->where('type', $request->get('type'))->first();
        if ($tag)
            return $this->errorResponse(__('msg.tag_anumber_is_already_used', [], $this->lang_code), 400);
        $tag = new Tag();

        $tag->vendor_id = vendor()->id;
        $tag->name_ar = $request->get('name_ar');
        $tag->name_en = $request->get('name_en');
        $tag->type = $request->get('type');
        $tag->number = 'T-' . ($last_item_id + 1);
        $tag->save();

        $msg = __('msg.tag_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function updateTag(Request $request)
    {
        $tag = Tag::where('vendor_id', vendor()->id)
            ->where('id', $request->get('tag_id'))->first();
        if (!$tag)
            return $this->errorResponse(__('msg.tag_not_found', [], $this->lang_code), 400);

        $tag->vendor_id = vendor()->id;
        if ($request->get('name_ar'))
            $tag->name_ar = $request->get('name_ar');
        if ($request->get('name_en'))
            $tag->name_en = $request->get('name_en');
        if ($request->get('number'))
            $tag->number = $request->get('number');
        $tag->save();

        $msg = __('msg.tag_updated_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteTag(Request $request)
    {
        $tag = Tag::where('vendor_id', vendor()->id)
            ->where('id', $request->get('tag_id'))->first();
        if (!$tag)
            return $this->errorResponse(__('msg.tag_not_found', [], $this->lang_code), 400);
        $tag->delete();

        $msg = __('msg.tag_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    ################## stocks ##################
    public function allStoks(Request $request)
    {
        $name = $request->get('name');
        $code = $request->get('code');
        $barcode = $request->get('barcode');
        $tag = $request->get('tag');
        $category = $request->get('category');
        $supplier = $request->get('supplier_id');
        $is_delete = $request->get('is_delete');
        $created_at = $request->get('created_at');
        $cost_calculation_method = $request->get('cost_calculation_method');
        $stocks = Stock::where('vendor_id', vendor()->id);
        if ($name)
            $stocks = $stocks->where('name', 'LIKE', '%' . $name . '%');
        if ($code)
            $stocks = $stocks->where('code', 'LIKE', '%' . $code . '%');
        if ($barcode)
            $stocks = $stocks->where('barcode', 'LIKE', '%' . $barcode . '%');
        if ($tag)
            $stocks = $stocks->whereHas('tags', function ($q) use ($tag) {
                $q->where('type', 3)->where('name_ar', 'LIKE', '%' . $tag . '%')
                    ->orWhere('name_en', 'LIKE', '%' . $tag . '%');
            });
        if ($category)
            $stocks = $stocks->whereHas('category', function ($q) use ($category) {
                $q->where('name_ar', 'LIKE', '%' . $category . '%')
                    ->orWhere('name_en', 'LIKE', '%' . $category . '%');
            });
        if ($supplier)
            $stocks = $stocks->whereHas('suppliers', function ($q) use ($category) {
                $q->where('name', 'LIKE', '%' . $category . '%')
                    ->orWhere('company_name', 'LIKE', '%' . $category . '%');
            });
        if ($created_at)
            $stocks = $stocks->whereDate('created_at', $created_at);
        if ($cost_calculation_method)
            $stocks = $stocks->whereDate('cost_calculation_method', $cost_calculation_method);
        elseif ($is_delete == 0)
            $stocks = $stocks->whereNull('deleted_at');
        else
            $stocks = $stocks->withTrashed();

        $stocks = $stocks->orderBy('id', 'DESC')->get();

        $data = $stocks->map(function ($stock) {
            return [
                'id' => $stock->id,
                'name' => $stock->name,
                'code' => $stock->code,
                'category' => isset($stock->category) ? $stock->category->name($this->lang_code) : '',
                'created_at' => date('d/m/Y', strtotime($stock->created_at)),
            ];
        });

        $msg = __('msg.stocks_get_success', [], $this->lang_code);
        return $this->dataResponse($msg, $data);
    }

    public function stoksMaterieal(Request $request)
    {
        $name = $request->get('name');
        $code = $request->get('code');
        $barcode = $request->get('barcode');
        $tag = $request->get('tag');
        $category = $request->get('category');
        $supplier = $request->get('supplier_id');
        $is_delete = $request->get('is_delete');
        $created_at = $request->get('created_at');
        $cost_calculation_method = $request->get('cost_calculation_method');
        $stocks = Stock::where('vendor_id', vendor()->id);
        if ($name)
            $stocks = $stocks->where('name', 'LIKE', '%' . $name . '%');
        if ($code)
            $stocks = $stocks->where('code', 'LIKE', '%' . $code . '%');
        if ($barcode)
            $stocks = $stocks->where('barcode', 'LIKE', '%' . $barcode . '%');
        if ($tag)
            $stocks = $stocks->whereHas('tags', function ($q) use ($tag) {
                $q->where('type', 3)->where('name_ar', 'LIKE', '%' . $tag . '%')
                    ->orWhere('name_en', 'LIKE', '%' . $tag . '%');
            });
        if ($category)
            $stocks = $stocks->whereHas('category', function ($q) use ($category) {
                $q->where('name_ar', 'LIKE', '%' . $category . '%')
                    ->orWhere('name_en', 'LIKE', '%' . $category . '%');
            });
        if ($supplier)
            $stocks = $stocks->whereHas('suppliers', function ($q) use ($category) {
                $q->where('name', 'LIKE', '%' . $category . '%')
                    ->orWhere('company_name', 'LIKE', '%' . $category . '%');
            });
        if ($created_at)
            $stocks = $stocks->whereDate('created_at', $created_at);
        if ($cost_calculation_method)
            $stocks = $stocks->whereDate('cost_calculation_method', $cost_calculation_method);

        $stocks = $stocks->orderBy('id', 'DESC')->get();

        $data = $stocks->map(function ($stock) {
            return [
                'id' => $stock->id,
                'name' => $stock->name,
                'code' => $stock->code,
                'category' => isset($stock->category) ? $stock->category->name($this->lang_code) : '',
                'created_at' => date('d/m/Y', strtotime($stock->created_at)),
            ];
        });

        $msg = __('msg.stocks_get_success', [], $this->lang_code);
        return $this->dataResponse($msg, $data);
    }

    public function generateStoksMateriealCode()
    {
        $last_item_id = 0;
        $last_item = Stock::withTrashed()->where('vendor_id', vendor()->id)->orderBy('id', 'DESC')->first();
        if($last_item){
            $num = explode('-',$last_item->code);
            $last_item_id = $num[1];
        }
        $data = [
            'operation_number' => 'SK-' . str_pad($last_item_id + 1, 5, "0", STR_PAD_LEFT)
        ];
        return $this->dataResponse('generated successfully', $data);
    }

    public function trashedStoks(Request $request)
    {
        $name = $request->get('name');
        $code = $request->get('code');
        $barcode = $request->get('barcode');
        $tag = $request->get('tag');
        $category = $request->get('category');
        $supplier = $request->get('supplier_id');
        $created_at = $request->get('created_at');
        $cost_calculation_method = $request->get('cost_calculation_method');
        $stocks = Stock::where('vendor_id', vendor()->id);
        if ($name)
            $stocks = $stocks->where('name', 'LIKE', '%' . $name . '%');
        if ($code)
            $stocks = $stocks->where('code', 'LIKE', '%' . $code . '%');
        if ($barcode)
            $stocks = $stocks->where('barcode', 'LIKE', '%' . $barcode . '%');
        if ($tag)
            $stocks = $stocks->whereHas('tags', function ($q) use ($tag) {
                $q->where('type', 3)->where('name_ar', 'LIKE', '%' . $tag . '%')
                    ->orWhere('name_en', 'LIKE', '%' . $tag . '%');
            });
        if ($category)
            $stocks = $stocks->whereHas('category', function ($q) use ($category) {
                $q->where('name_ar', 'LIKE', '%' . $category . '%')
                    ->orWhere('name_en', 'LIKE', '%' . $category . '%');
            });
        if ($supplier)
            $stocks = $stocks->whereHas('suppliers', function ($q) use ($category) {
                $q->where('name', 'LIKE', '%' . $category . '%')
                    ->orWhere('company_name', 'LIKE', '%' . $category . '%');
            });
        if ($created_at)
            $stocks = $stocks->whereDate('created_at', $created_at);
        if ($cost_calculation_method)
            $stocks = $stocks->whereDate('cost_calculation_method', $cost_calculation_method);

        $stocks = $stocks->onlyTrashed()->orderBy('id', 'DESC')->get();

        $data = $stocks->map(function ($stock) {
            return [
                'id' => $stock->id,
                'name' => $stock->name,
                'code' => $stock->code,
                'category' => isset($stock->category) ? $stock->category->name($this->lang_code) : '',
                'created_at' => date('d/m/Y', strtotime($stock->created_at)),
            ];
        });

        $msg = __('msg.stocks_get_success', [], $this->lang_code);
        return $this->dataResponse($msg, $data);
    }

    public function createStock(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'storage_unit' => 'required',
            'recipe_unit' => 'required',
            'recipe_unit_quantity' => 'required',
            'cost_calculation_method' => 'required',
            'amount' => 'required',
            'initial_quantity_to_create_an_order' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $last_item_id = 0;
        $last_item = Stock::withTrashed()->where('vendor_id', vendor()->id)->orderBy('id', 'DESC')->first();
        if($last_item){
            $num = explode('-',$last_item->code);
            $last_item_id = $num[1];
        }

        $stock = Stock::where('vendor_id', vendor()->id)->where('id', $request->stock_id)->first();
        if (!$stock)
            $stock = new Stock();
        $stock->vendor_id = vendor()->id;
        if ($request->get('store_house_id'))
            $stock->store_house_id = $request->get('store_house_id');
        if ($request->get('name'))
            $stock->name = $request->get('name');
        $stock->code = 'SK-' . str_pad($last_item_id + 1, 5, "0", STR_PAD_LEFT);
        if ($request->get('category_id'))
            $stock->category_id = $request->get('category_id');
        if ($request->get('storage_unit'))
            $stock->storage_unit = $request->get('storage_unit');
        if ($request->get('recipe_unit'))
            $stock->recipe_unit = $request->get('recipe_unit');
        if ($request->get('recipe_unit_quantity'))
            $stock->recipe_unit_quantity = $request->get('recipe_unit_quantity');
        if ($request->get('cost_calculation_method'))
            $stock->cost_calculation_method = $request->get('cost_calculation_method');
        if ($request->get('amount'))
            $stock->amount = $request->get('amount');
        if ($request->get('initial_quantity_to_create_an_order'))
            $stock->initial_quantity_to_create_an_order = $request->get('initial_quantity_to_create_an_order');
        if ($request->get('barcode'))
            $stock->barcode = $request->get('barcode');
        if ($request->low_level)
            $stock->low_level = $request->low_level;
        if ($request->high_level)
            $stock->high_level = $request->high_level;
        $stock->save();

        $msg = __('msg.stocks_created_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function stockDetails(Request $request)
    {
        $stock = Stock::where('vendor_id', vendor()->id)->where('id', $request->get('stock_id'))->first();
        if (!$stock)
            return $this->errorResponse(__('msg.stock_not_found', [], $this->lang_code), 400);
        $data = [
            'id' => $stock->id,
            'name' => $stock->name,
            'code' => $stock->code,
            'storage_unit' => $stock->storage_unit,
            'recipe_unit' => $stock->recipe_unit,
            'recipe_unit_quantity' => $stock->recipe_unit_quantity,
            'cost_calculation_method' => $stock->cost_calculation_method,
            'amount' => $stock->amount,
            'initial_quantity_to_create_an_order' => $stock->initial_quantity_to_create_an_order,
            'barcode' => $stock->barcode,
            'low_level' => $stock->low_level,
            'high_level' => $stock->high_level,
            'store_house_id' => $stock->store_house_id,
            'category_id' => $stock->category_id,
            'category' => isset($stock->category) ? $stock->category->name($this->lang_code) : '',
            'store_house' => isset($stock->store_house) ? $stock->store_house->name($this->lang_code) : '',
            'created_at' => date('d/m/Y', strtotime($stock->created_at)),
            'tags' => isset($stock->tags) && count($stock->tags) > 0 ? $stock->tags : [],
            'suppliers' => isset($stock->suppliers) && count($stock->suppliers) > 0 ? $stock->suppliers : [],
        ];

        $msg = __('msg.stocks_get_success', [], $this->lang_code);
        return $this->dataResponse($msg, $data);
    }

    public function deleteStock(Request $request)
    {
        $stock = Stock::where('vendor_id', vendor()->id)->where('id', $request->get('stock_id'))->first();
        if (!$stock)
            return $this->errorResponse(__('msg.stock_not_found', [], $this->lang_code), 400);
        $stock->delete();
        $msg = __('msg.stocks_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function addTagStock(Request $request)
    {
        $stock_id = $request->get('stock_id');
        $tag_id = $request->get('tag_id');
        $stok_tag = StockTag::where('stock_id', $stock_id)->where('tag_id', $tag_id)->first();
        if (!$stok_tag)
            $stok_tag = new StockTag();
        $stok_tag->stock_id = $stock_id;
        $stok_tag->tag_id = $tag_id;
        $stok_tag->save();

        $msg = __('msg.tag_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteTagStock(Request $request)
    {

        $stock_id = $request->get('stock_id');
        $tag_id = $request->get('tag_id');
        $stok_tag = StockTag::where('stock_id', $stock_id)->where('tag_id', $tag_id)->first();
        if (!$stok_tag)
            return $this->errorResponse(__('msg.tag_not_found', [], $this->lang_code), 400);
        $stok_tag->delete();
        $msg = __('msg.tag_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    ############################ suppliers ################
    public function suppliers(Request $request)
    {
        $company_name = $request->get('company_name');
        $name = $request->get('name');
        $mobile = $request->get('mobile');
        $tag = $request->get('tag');
        $email = $request->get('email');
        $is_delete = $request->get('is_delete');
        $created_at = $request->get('created_at');

        $suppliers = Supplier::where('vendor_id', vendor()->id);
        if ($company_name)
            $suppliers = $suppliers->where('company_name', 'LIKE', '%' . $company_name . '%');
        if ($name)
            $suppliers = $suppliers->where('name', 'LIKE', '%' . $name . '%');
        if ($mobile)
            $suppliers = $suppliers->where('mobile', 'LIKE', '%' . $mobile . '%');
        if ($email)
            $suppliers = $suppliers->where('email', 'LIKE', '%' . $email . '%');
        if ($tag)
            $suppliers = $suppliers->whereHas('tags', function ($q) use ($tag) {
                $q->where('type', 5)->where('name_ar', 'LIKE', '%' . $tag . '%')
                    ->orWhere('name_en', 'LIKE', '%' . $tag . '%');
            });
        if ($created_at)
            $suppliers = $suppliers->whereDate('created_at', $created_at);

        if ($is_delete == 0)
            $suppliers = $suppliers->whereNull('deleted_at');
        else
            $suppliers = $suppliers->withTrashed();

        $data = $suppliers->orderBy('id', 'DESC')->get();
        $msg = __('msg.suppliers_get_success', [], $this->lang_code);
        return $this->dataResponse($msg, $data);
    }

    public function trashedSuppliers(Request $request)
    {
        $company_name = $request->get('company_name');
        $name = $request->get('name');
        $mobile = $request->get('mobile');
        $tag = $request->get('tag');
        $email = $request->get('email');
        $created_at = $request->get('created_at');

        $suppliers = Supplier::where('vendor_id', vendor()->id)->onlyTrashed();
        if ($company_name)
            $suppliers = $suppliers->where('company_name', 'LIKE', '%' . $company_name . '%');
        if ($name)
            $suppliers = $suppliers->where('name', 'LIKE', '%' . $name . '%');
        if ($mobile)
            $suppliers = $suppliers->where('mobile', 'LIKE', '%' . $mobile . '%');
        if ($email)
            $suppliers = $suppliers->where('email', 'LIKE', '%' . $email . '%');
        if ($tag)
            $suppliers = $suppliers->whereHas('tags', function ($q) use ($tag) {
                $q->where('type', 5)->where('name_ar', 'LIKE', '%' . $tag . '%')
                    ->orWhere('name_en', 'LIKE', '%' . $tag . '%');
            });
        if ($created_at)
            $suppliers = $suppliers->whereDate('created_at', $created_at);

        $data = $suppliers->orderBy('id', 'DESC')->get();
        $msg = __('msg.suppliers_get_success', [], $this->lang_code);
        return $this->dataResponse($msg, $data);
    }

    public function generateSupplierCode()
    {
        $last_item_id = 0;
        $last_item = Supplier::withTrashed()->where('vendor_id', vendor()->id)->orderBy('id', 'DESC')->first();
        if($last_item){
            $num = explode('-',$last_item->code);
            $last_item_id = $num[1];
        }
        $data = [
            'operation_number' => 'SP-' . str_pad($last_item_id+1, 5, "0", STR_PAD_LEFT)
        ];
        return $this->dataResponse('generated successfully', $data);
    }

    public function createSupplier(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required',
            'name' => 'required',
            'mobile' => 'required|unique:suppliers',
            'email' => 'required|unique:suppliers',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $last_item_id = 0;
        $last_item = Supplier::withTrashed()->where('vendor_id', vendor()->id)->orderBy('id', 'DESC')->first();
        if($last_item){
            $num = explode('-',$last_item->code);
            $last_item_id = $num[1];
        }

        $supplier = new Supplier();
        $supplier->vendor_id = vendor()->id;
        $supplier->company_name = $request->get('company_name');
        $supplier->name = $request->get('name');
        $supplier->mobile = $request->get('mobile');
        $supplier->email = $request->get('email');
        $supplier->code = 'SP-' . str_pad($last_item_id + 1, 5, "0", STR_PAD_LEFT);
        $supplier->save();

        $msg = __('msg.supplier_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function updateSupplier(Request $request)
    {
        $supplier = Supplier::where('vendor_id', vendor()->id)->where('id', $request->get('supplier_id'))->first();
        if (!$supplier)
            return $this->errorResponse(__('msg.supplier_not_found', [], $this->lang_code), 400);
        if ($request->get('company_name'))
            $supplier->company_name = $request->get('company_name');
        if ($request->get('name'))
            $supplier->name = $request->get('name');
        if ($request->get('mobile'))
            $supplier->mobile = $request->get('mobile');
        if ($request->get('email'))
            $supplier->email = $request->get('email');
        $supplier->save();

        $msg = __('msg.supplier_updated_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function supplierDetails(Request $request)
    {
        $supplier = Supplier::where('vendor_id', vendor()->id)->where('id', $request->get('supplier_id'))->first();
        if (!$supplier)
            return $this->errorResponse(__('msg.supplier_not_found', [], $this->lang_code), 400);

        $data = [
            'company_name' => $supplier->company_name,
            'name' => $supplier->name,
            'mobile' => $supplier->mobile,
            'email' => $supplier->email,
            'code' => $supplier->code,
            'created_at' => $supplier->created_at,
            'updated_at' => $supplier->updated_at,
            'deleted_at' => $supplier->deleted_at,
            'tags' => isset($supplier->tags) && count($supplier->tags) > 0 ? $supplier->tags : [],
            'stocks' => isset($supplier->stocks) && count($supplier->stocks) > 0 ? $supplier->stocks : [],
        ];

        $msg = __('msg.supplier_get_success', [], $this->lang_code);
        return $this->dataResponse($msg, $data);
    }

    public function deleteSupplier(Request $request)
    {
        $supplier = Supplier::where('vendor_id', vendor()->id)->where('id', $request->get('supplier_id'))->first();
        if (!$supplier)
            return $this->errorResponse(__('msg.supplier_not_found', [], $this->lang_code), 400);

        $supplier->delete();

        $msg = __('msg.supplier_updated_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function addSupplierTag(Request $request)
    {
        $tag_id = $request->get('tag_id');
        $supplier_id = $request->get('supplier_id');
        $supplier_tag = SupplierTag::where('tag_id', $tag_id)->where('supplier_id', $supplier_id)->first();
        if (!$supplier_tag)
            $supplier_tag = new SupplierTag();
        $supplier_tag->tag_id = $tag_id;
        $supplier_tag->supplier_id = $supplier_id;
        $supplier_tag->save();

        $msg = __('msg.tag_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteSupplierTag(Request $request)
    {

        $tag_id = $request->get('tag_id');
        $supplier_id = $request->get('supplier_id');
        $supplier_tag = SupplierTag::where('tag_id', $tag_id)->where('supplier_id', $supplier_id)->first();
        if (!$supplier_tag)
            return $this->errorResponse(__('msg.tag_not_found', [], $this->lang_code), 400);
        $supplier_tag->delete();
        $msg = __('msg.tag_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function addSupplierStock(Request $request)
    {
        $stock_id = $request->get('stock_id');
        $supplier_id = $request->get('supplier_id');
        $stok_supplier = StockSupplier::where('stock_id', $stock_id)->where('supplier_id', $supplier_id)->first();
        if (!$stok_supplier)
            $stok_supplier = new StockSupplier();
        $stok_supplier->stock_id = $stock_id;
        $stok_supplier->supplier_id = $supplier_id;
        $stok_supplier->save();

        $msg = __('msg.tag_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteSupplierStock(Request $request)
    {

        $stock_id = $request->get('stock_id');
        $supplier_id = $request->get('supplier_id');
        $stok_supplier = StockSupplier::where('stock_id', $stock_id)->where('supplier_id', $supplier_id)->first();
        if (!$stok_supplier)
            return $this->errorResponse(__('msg.supplier_not_found', [], $this->lang_code), 400);
        $stok_supplier->delete();
        $msg = __('msg.tag_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    ################# inventory template ##########3

    public function getInvintoryTemplate(Request $request)
    {
        $name = $request->get('name');
        $secondary_name = $request->get('secondary_name');
        $code = $request->get('code');
        $inventory = StockInventoryTemplate::where('vendor_id', vendor()->id);
        if ($name)
            $inventory = $inventory->where('name', 'LIKE', '%' . $name . '%');
        if ($secondary_name)
            $inventory = $inventory->where('secondary_name', 'LIKE', '%' . $secondary_name . '%');
        if ($code)
            $inventory = $inventory->where('code', 'LIKE', '%' . $code . '%');

        $inventory = $inventory->orderBy('id', 'DESC')->get();

        $msg = __('msg.inventory_template_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $inventory, 200);
    }

    public function generateInvintoryTemplateCode()
    {
        $last_item_id = 0;
        $last_item = StockInventoryTemplate::where('vendor_id', vendor()->id)->orderBy('id', 'DESC')->first();
        if($last_item){
            $num = explode('-',$last_item->code);
            $last_item_id = $num[1];
        }
        $data = [
            'operation_number' => 'SIT-' . str_pad($last_item_id + 1, 5, "0", STR_PAD_LEFT)
        ];
        return $this->dataResponse('generated successfully', $data);
    }

    public function addStockInventoryTemplate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $last_item_id = 0;
        $last_item = StockInventoryTemplate::where('vendor_id', vendor()->id)->orderBy('id', 'DESC')->first();
        if($last_item){
            $num = explode('-',$last_item->code);
            $last_item_id = $num[1];
        }
        $inventory = new StockInventoryTemplate();
        $inventory->vendor_id = vendor()->id;
        $inventory->name = $request->get('name');
        $inventory->secondary_name = $request->get('secondary_name');
        $inventory->code = 'SIT-' . str_pad($last_item_id + 1, 5, "0", STR_PAD_LEFT);
        $inventory->save();
        $msg = __('msg.inventory_template_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function editStockInventoryTemplate(Request $request)
    {
        $inventory = StockInventoryTemplate::where('vendor_id', vendor()->id)->where('id', $request->get('id'))->first();
        if (!$inventory)
            return $this->errorResponse(__('msg.inventory_template_not_found', [], $this->lang_code), 400);
        if ($request->get('name'))
            $inventory->name = $request->get('name');
        if ($request->get('secondary_name'))
            $inventory->secondary_name = $request->get('secondary_name');
        $inventory->save();
        $msg = __('msg.inventory_template_update_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteStockInventoryTemplate(Request $request)
    {
        $inventory = StockInventoryTemplate::where('vendor_id', vendor()->id)->where('id', $request->get('id'))->first();
        if (!$inventory)
            return $this->errorResponse(__('msg.inventory_template_not_found', [], $this->lang_code), 400);

        $inventory->delete();
        $msg = __('msg.inventory_template_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);

    }

    ###################### stock order purchase ################
    public function generateStokPurchaseOrdersCode()
    {
        $last_item_id = 0;
        $last_item = StockPurchaseOrders::where('vendor_id', vendor()->id)->orderBy('id', 'DESC')->first();
        if($last_item){
            $num = explode('-',$last_item->code);
            $last_item_id = $num[1];
        }
        $data = [
            'operation_number' => 'buy-' . str_pad($last_item_id + 1, 3, "0", STR_PAD_LEFT)
        ];
        return $this->dataResponse('generated successfully', $data);
    }

    public function stockPurchaseOrders(Request $request)
    {
        $code = $request->get('code');
        $work_date = $request->get('work_date');
        $status_id = $request->get('status_id');
        $supplier_id = $request->get('supplier_id');
        $branch_tag = $request->get('branch_tag');
        $created_by_name = $request->get('created_by_name');
        $created_at = $request->get('created_at');
        $delivery_date = $request->get('delivery_date');
        if ($request->get('type') == 0) // all status
            $purchase_orders = StockPurchaseOrders::where('vendor_id', vendor()->id);
        elseif ($request->get('type') == 1) //draft
            $purchase_orders = StockPurchaseOrders::where('vendor_id', vendor()->id)->where('status_id', 0);
        elseif ($request->get('type') == 2) // sent
            $purchase_orders = StockPurchaseOrders::where('vendor_id', vendor()->id)->where('status_id', 1);
        elseif ($request->get('type') == 3) // canceled
            $purchase_orders = StockPurchaseOrders::where('vendor_id', vendor()->id)->where('status_id', 3);
        else
            $purchase_orders = StockPurchaseOrders::where('vendor_id', vendor()->id);

        if ($code)
            $purchase_orders = $purchase_orders->where('code', 'LIKE', '%' . $code . '%');
        if ($created_by_name)
            $purchase_orders = $purchase_orders->where('created_by_name', 'LIKE', '%' . $created_by_name . '%');
        if ($work_date)
            $purchase_orders = $purchase_orders->whereDate('work_date', $work_date);
        if ($created_at)
            $purchase_orders = $purchase_orders->whereDate('created_at', $created_at);
        if ($delivery_date)
            $purchase_orders = $purchase_orders->whereDate('delivery_date', $delivery_date);
        if ($status_id)
            $purchase_orders = $purchase_orders->whereDate('status_id', $status_id);
        if ($supplier_id)
            $purchase_orders = $purchase_orders->whereDate('supplier_id', $supplier_id);
        if ($branch_tag)
            $purchase_orders = $purchase_orders->whereHas('branch', function ($q) use ($branch_tag) {
                $q->whereHas('tags', function ($tag) use ($branch_tag) {
                    $tag->where('name', 'LIKE', '%' . $branch_tag . '%');
                });
            });
        $purchase_orders = $purchase_orders->where('type_id', 0)->orderBy('id', 'DESC')->paginate(10);

        $data = [
            'count' => $purchase_orders->count(),
            'currentPage' => $purchase_orders->currentPage(),
            'firstItem' => $purchase_orders->firstItem(),
            'getOptions' => $purchase_orders->getOptions(),
            'hasPages' => $purchase_orders->hasPages(),
            'items' => $purchase_orders->items(),
            'lastItem' => $purchase_orders->lastItem(),
            'lastPage' => $purchase_orders->lastPage(),
            'nextPageUrl' => $purchase_orders->nextPageUrl(),
            'perPage' => $purchase_orders->perPage(),
            'total' => $purchase_orders->total(),
            'getPageName' => $purchase_orders->getPageName(),
            'data' => $purchase_orders->map(function ($purchase) {
                return [
                    'supplier_name' => isset($purchase->supplier) && $purchase->supplier->name ? $purchase->supplier->name : '',
                    'supplier_company_name' => isset($purchase->supplier) && $purchase->supplier->name ? $purchase->supplier->name : '',
                    'branch_name' => isset($purchase->branch) && $purchase->branch->name($this->lang_code) ? $purchase->branch->name($this->lang_code) : '',
                    'code' => $purchase->code,
                    'status_name' => $purchase->status_name($this->lang_code),
                    'work_date' => $purchase->work_date ? date('d/m/Y H:i', strtotime($purchase->work_date)) : '',
                ];
            })
        ];
        $msg = __('msg.stock_purchase_orders_get_success', [], $this->lang_code);
        return $this->dataResponse($msg, $data);
    }

    public function stockPurchaseWarehouseOrders(Request $request)
    {
        $code = $request->get('code');
        $work_date = $request->get('work_date');
        $status_id = $request->get('status_id');
        $supplier_id = $request->get('supplier_id');
        $branch_tag = $request->get('branch_tag');
        $created_by_name = $request->get('created_by_name');
        $created_at = $request->get('created_at');
        $delivery_date = $request->get('delivery_date');
        if ($request->get('type') == 0) // all status
            $purchase_orders = StockPurchaseOrders::where('vendor_id', vendor()->id);
        elseif ($request->get('type') == 1) //draft
            $purchase_orders = StockPurchaseOrders::where('vendor_id', vendor()->id)->where('status_id', 0);
        elseif ($request->get('type') == 2) // sent
            $purchase_orders = StockPurchaseOrders::where('vendor_id', vendor()->id)->where('status_id', 1);
        elseif ($request->get('type') == 3) // canceled
            $purchase_orders = StockPurchaseOrders::where('vendor_id', vendor()->id)->where('status_id', 3);
        else
            $purchase_orders = StockPurchaseOrders::where('vendor_id', vendor()->id);

        if ($code)
            $purchase_orders = $purchase_orders->where('code', 'LIKE', '%' . $code . '%');
        if ($created_by_name)
            $purchase_orders = $purchase_orders->where('created_by_name', 'LIKE', '%' . $created_by_name . '%');
        if ($work_date)
            $purchase_orders = $purchase_orders->whereDate('work_date', $work_date);
        if ($created_at)
            $purchase_orders = $purchase_orders->whereDate('created_at', $created_at);
        if ($delivery_date)
            $purchase_orders = $purchase_orders->whereDate('delivery_date', $delivery_date);
        if ($status_id)
            $purchase_orders = $purchase_orders->whereDate('status_id', $status_id);
        if ($supplier_id)
            $purchase_orders = $purchase_orders->whereDate('supplier_id', $supplier_id);
        if ($branch_tag)
            $purchase_orders = $purchase_orders->whereHas('branch', function ($q) use ($branch_tag) {
                $q->whereHas('tags', function ($tag) use ($branch_tag) {
                    $tag->where('name', 'LIKE', '%' . $branch_tag . '%');
                });
            });
        $purchase_orders = $purchase_orders->where('type_id', 1)->orderBy('id', 'DESC')->paginate(10);

        $data = [
            'count' => $purchase_orders->count(),
            'currentPage' => $purchase_orders->currentPage(),
            'firstItem' => $purchase_orders->firstItem(),
            'getOptions' => $purchase_orders->getOptions(),
            'hasPages' => $purchase_orders->hasPages(),
            'items' => $purchase_orders->items(),
            'lastItem' => $purchase_orders->lastItem(),
            'lastPage' => $purchase_orders->lastPage(),
            'nextPageUrl' => $purchase_orders->nextPageUrl(),
            'perPage' => $purchase_orders->perPage(),
            'total' => $purchase_orders->total(),
            'getPageName' => $purchase_orders->getPageName(),
            'data' => $purchase_orders->map(function ($purchase) {
                return [
                    'supplier_name' => isset($purchase->supplier) && $purchase->supplier->name ? $purchase->supplier->name : '',
                    'supplier_company_name' => isset($purchase->supplier) && $purchase->supplier->name ? $purchase->supplier->name : '',
                    'branch_name' => isset($purchase->branch) && $purchase->branch->name($this->lang_code) ? $purchase->branch->name($this->lang_code) : '',
                    'code' => $purchase->code,
                    'status_name' => $purchase->status_name($this->lang_code),
                    'work_date' => $purchase->work_date ? date('d/m/Y H:i', strtotime($purchase->work_date)) : '',
                ];
            })
        ];
        $msg = __('msg.stock_purchase_orders_get_success', [], $this->lang_code);
        return $this->dataResponse($msg, $data);
    }

    public function createStockPurchaseOrders(Request $request)
    {
        $last_item_id = 0;
        $last_item = StockPurchaseOrders::where('vendor_id', vendor()->id)->orderBy('id', 'DESC')->first();
        if($last_item){
            $num = explode('-',$last_item->code);
            $last_item_id = $num[1];
        }
        $sender_name = '';
        $sender_id = 0;
        if (auth()->guard('vendor')->check()) {
            $sender_name = vendor()->name;
            $sender_id = vendor()->id;
        }elseif (auth()->guard('vendor_employee')->check()){
            $sender_name = vendor_employee()->name;
            $sender_id = vendor_employee()->vendor->id;
        }
        $purchase_order = new StockPurchaseOrders();
        $purchase_order->vendor_id = $sender_id;
        $purchase_order->code = 'buy-' . str_pad($last_item_id + 1, 3, "0", STR_PAD_LEFT);
        $purchase_order->supplier_id = $request->get('supplier_id');
        $purchase_order->branch_id = $request->get('branch_id');
        $purchase_order->extra_price = $request->get('extra_price') > 0 ? $request->get('extra_price') : 0;
        $purchase_order->delivery_date = $request->get('delivery_date');
        $purchase_order->notes = $request->get('notes');
        $purchase_order->created_by_name = $sender_name;
        $purchase_order->type_id = 0;
        $purchase_order->save();

        $msg = __('msg.stock_purchase_orders_created_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function updateStockPurchaseOrders(Request $request)
    {
        $vendor_id = 0;
        if (auth()->guard('vendor')->check())
            $vendor_id = vendor()->id;
            elseif (auth()->guard('vendor_employee')->check())
        $vendor_id = vendor_employee()->vendor->id;

        $purchase_order = StockPurchaseOrders::where('vendor_id', $vendor_id)->where('id', $request->get('purchase_order_id'))->first();
        if (!$purchase_order)
            return $this->errorResponse(__('msg.stock_purchase_order_not_found', [], $this->lang_code), 400);
        if ($request->get('supplier_id'))
            $purchase_order->supplier_id = $request->get('supplier_id');
        if ($request->get('branch_id'))
            $purchase_order->branch_id = $request->get('branch_id');
        if ($request->get('extra_price'))
            $purchase_order->extra_price = $request->get('extra_price');
        if ($request->get('delivery_date'))
            $purchase_order->delivery_date = $request->get('delivery_date');
        if ($request->get('work_date'))
            $purchase_order->work_date = $request->get('work_date');
        if ($request->get('notes'))
            $purchase_order->notes = $request->get('notes');
        $purchase_order->save();

        $msg = __('msg.stock_purchase_orders_updated_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function StockPurchaseOrderDetails(Request $request)
    {
        $vendor_id = 0;
        if (auth()->guard('vendor')->check())
            $vendor_id = vendor()->id;
        elseif (auth()->guard('vendor_employee')->check())
            $vendor_id = vendor_employee()->vendor->id;

        $purchase = StockPurchaseOrders::where('vendor_id',$vendor_id)->where('id', $request->get('purchase_order_id'))->first();
        if (!$purchase)
            return $this->errorResponse(__('msg.stock_purchase_order_not_found', [], $this->lang_code), 400);

        $data = [
            'supplier_name' => isset($purchase->supplier) && $purchase->supplier->name ? $purchase->supplier->name : '',
            'supplier_company_name' => isset($purchase->supplier) && $purchase->supplier->name ? $purchase->supplier->name : '',
            'branch_name' => isset($purchase->branch) && $purchase->branch->name($this->lang_code) ? $purchase->branch->name($this->lang_code) : '',
            'code' => $purchase->code,
            'sender_name' => $purchase->sender_name,
            'approve_by_name' => $purchase->approve_by_name,
            'notes' => $purchase->notes,
            'created_by_name' => $purchase->created_by_name,
            'extra_price' => $purchase->extra_price,
            'status_name' => $purchase->status_name($this->lang_code),
            'work_date' => $purchase->work_date ? date('d/m/Y H:i', strtotime($purchase->work_date)) : '',
            'delivery_date' => $purchase->delivery_date ? date('d/m/Y H:i', strtotime($purchase->delivery_date)) : '',
            'invoice_date' => isset($purchase->stock_purchase) && $purchase->stock_purchase->invoice_date ? date('d/m/Y H:i', strtotime($purchase->stock_purchase->invoice_date)) : '',
            'invoice_number' => isset($purchase->stock_purchase) && $purchase->stock_purchase->invoice_number ? $purchase->stock_purchase->invoice_number : '',
            'stock_purchase_order_materials' => []
        ];

        $msg = __('msg.stock_purchase_orders_get_success', [], $this->lang_code);
        return $this->dataResponse($msg, $data);
    }

    public function generateStokPurchaseCode()
    {
        $vendor_id = 0;
        if (auth()->guard('vendor')->check())
            $vendor_id = vendor()->id;
        elseif (auth()->guard('vendor_employee')->check())
            $vendor_id = vendor_employee()->vendor->id;

        $last_item_id = 0;
        $last_item = StockPurchase::where('vendor_id', $vendor_id)->orderBy('id', 'DESC')->first();
        if($last_item){
            $num = explode('-',$last_item->code);
            $last_item_id = $num[1];
        }
        $data = [
            'operation_number' => 'PUR-' . str_pad($last_item_id + 1, 3, "0", STR_PAD_LEFT)
        ];
        return $this->dataResponse('generated successfully', $data);
    }

    public function StockPurchaseOrderChangeStatusSent(Request $request)
    {
        $vendor_id = 0;
        if (auth()->guard('vendor')->check())
            $vendor_id = vendor()->id;
        elseif (auth()->guard('vendor_employee')->check())
            $vendor_id = vendor_employee()->vendor->id;

        $purchase = StockPurchaseOrders::where('vendor_id', $vendor_id)
            ->where('id', $request->get('purchase_order_id'))->where('status_id', '!=', 1)->first();
        if (!$purchase)
            return $this->errorResponse(__('msg.stock_purchase_order_not_found', [], $this->lang_code), 400);
        $purchase->status_id = 1;
        $purchase->save();

        $msg = __('msg.stock_purchase_orders_updated_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function StockPurchaseOrderChangeStatusCanel(Request $request)
    {
        $vendor_id = 0;
        if (auth()->guard('vendor')->check())
            $vendor_id = vendor()->id;
        elseif (auth()->guard('vendor_employee')->check())
            $vendor_id = vendor_employee()->vendor->id;
        $purchase = StockPurchaseOrders::where('vendor_id', $vendor_id)
            ->where('id', $request->get('purchase_order_id'))->where('status_id', '!=', 3)->first();
        if (!$purchase)
            return $this->errorResponse(__('msg.stock_purchase_order_not_found', [], $this->lang_code), 400);
        $purchase->status_id = 3;
        $purchase->save();

        $msg = __('msg.stock_purchase_orders_updated_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function StockPurchaseOrderChangeStatusClose(Request $request)
    {
        $vendor_id = 0;
        if (auth()->guard('vendor')->check())
            $vendor_id = vendor()->id;
        elseif (auth()->guard('vendor_employee')->check())
            $vendor_id = vendor_employee()->vendor->id;
        $purchase = StockPurchaseOrders::where('vendor_id', $vendor_id)
            ->where('id', $request->get('purchase_order_id'))->where('status_id', '!=', 2)->first();
        if (!$purchase)
            return $this->errorResponse(__('msg.stock_purchase_order_not_found', [], $this->lang_code), 400);
        $purchase->status_id = 2;
        $purchase->save();

        $last_item_id = 0;
        $last_item = StockPurchase::where('vendor_id', vendor()->id)->orderBy('id', 'DESC')->first();
        if($last_item){
            $num = explode('-',$last_item->code);
            $last_item_id = $num[1];
        }
        $stock_purch = new StockPurchase();
        $stock_purch->vendor_id = $vendor_id;
        $stock_purch->created_by_name = vendor()->name;
        $stock_purch->sender_by_name = vendor()->name;
        $stock_purch->code = 'PUR-' . str_pad($last_item_id + 1, 3, "0", STR_PAD_LEFT);
        $stock_purch->supplier_id = $purchase->supplier_id;
        $stock_purch->branch_id = $purchase->branch_id;
        $stock_purch->extra_price = $purchase->extra_price;
        $stock_purch->save();

        $msg = __('msg.stock_purchase_orders_updated_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function addStockPurchaseOrderStockMaterial(Request $request){
        $vendor_id = 0;
        if (auth()->guard('vendor')->check())
            $vendor_id = vendor()->id;
        elseif (auth()->guard('vendor_employee')->check())
            $vendor_id = vendor_employee()->vendor->id;
        if ($request->type = 1 && $request->stock_materials){
            $purchase = StockPurchaseOrders::where('vendor_id', $vendor_id)
                ->where('id', $request->get('purchase_order_id'))->first();
            if (!$purchase)
                return $this->errorResponse(__('msg.stock_purchase_order_not_found', [], $this->lang_code), 400);
            $stock_materials = json_decode($request->stock_materials);
            foreach ($stock_materials as $material){
                $stock_pur_or_material = StockPurchaseOrderMaterial::where('stock_purchase_order_id',$purchase->id)
                    ->where('stock_material_id',$material)->first();
                if (!$stock_pur_or_material)
                    $stock_pur_or_material = new StockPurchaseOrderMaterial();
                $stock_pur_or_material->stock_purchase_order_id = $purchase->id;
                $stock_pur_or_material->stock_material_id = $material;
                $stock_pur_or_material->type = 1;
                $stock_pur_or_material->save();
            }
        }elseif ($request->type = 2 && $request->stock_materials_tags){
            $purchase = StockPurchaseOrders::where('vendor_id', $vendor_id)
                ->where('id', $request->get('purchase_order_id'))->first();
            if (!$purchase)
                return $this->errorResponse(__('msg.stock_purchase_order_not_found', [], $this->lang_code), 400);
            $stock_materials_tags = json_decode($request->stock_materials_tags);
            foreach ($stock_materials_tags as $tag){
                $stock_material = Stock::where('vendor_id',$vendor_id)->whereHas('tags',function ($q)use($tag){
                    $q->where('name_ar','LIKE','%'.$tag.'%')->orWhere('name_en','LIKE','%'.$tag.'%');
                })->first();
                if ($stock_material) {
                    $stock_pur_or_material = StockPurchaseOrderMaterial::where('stock_purchase_order_id', $purchase->id)
                        ->where('stock_material_id', $stock_material->id)->first();
                    if (!$stock_pur_or_material)
                        $stock_pur_or_material = new StockPurchaseOrderMaterial();
                    $stock_pur_or_material->stock_purchase_order_id = $purchase->id;
                    $stock_pur_or_material->stock_material_id = $stock_material->id;
                    $stock_pur_or_material->type = 2;
                    $stock_pur_or_material->save();
                }
            }
        }elseif ($request->type = 3 && $request->stock_materials_suppliers){
            $purchase = StockPurchaseOrders::where('vendor_id', $vendor_id)
                ->where('id', $request->get('purchase_order_id'))->first();
            if (!$purchase)
                return $this->errorResponse(__('msg.stock_purchase_order_not_found', [], $this->lang_code), 400);
            $stock_materials_suppliers = json_decode($request->stock_materials_suppliers);
            foreach ($stock_materials_suppliers as $supplier){
                $stock_material = Stock::where('vendor_id',$vendor_id)->whereHas('tags',function ($q)use($supplier){
                    $q->where('name_ar','LIKE','%'.$supplier.'%')->orWhere('name_en','LIKE','%'.$supplier.'%');
                })->first();
                if ($stock_material) {
                    $stock_pur_or_material = StockPurchaseOrderMaterial::where('stock_purchase_order_id', $purchase->id)
                        ->where('stock_material_id', $stock_material->id)->first();
                    if (!$stock_pur_or_material)
                        $stock_pur_or_material = new StockPurchaseOrderMaterial();
                    $stock_pur_or_material->stock_purchase_order_id = $purchase->id;
                    $stock_pur_or_material->stock_material_id = $stock_material->id;
                    $stock_pur_or_material->type = 3;
                    $stock_pur_or_material->save();
                }
            }
        }
    }

    ###################### stock production ##################33
    public function stockProductions(Request $request)
    {
        $stock_production = StockProduction::where('vendor_id', vendor()->id);
        $code = $request->get('code');
        $work_date = $request->get('work_date');
        $sender_date = $request->get('sender_date');
        $status_id = $request->get('status_id');
        $tag = $request->get('tag');
        $store_house_id = $request->get('store_house_id');
        $created_at = $request->get('created_at');
        $sender = $request->get('sender');
        $creator = $request->get('creator');
//        if ()
    }
}
