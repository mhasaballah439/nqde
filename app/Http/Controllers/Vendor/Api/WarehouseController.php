<?php

namespace App\Http\Controllers\Vendor\Api;

use App\Http\Controllers\Controller;
use App\Models\ModifyCost;
use App\Models\ModifyCostStockMaterial;
use App\Models\ModifyQuantitie;
use App\Models\ModifyQuantityStockMaterial;
use App\Models\ProductionStockMaterial;
use App\Models\Stock;
use App\Models\StockCategory;
use App\Models\StockCategoryStockMaterial;
use App\Models\StockCheck;
use App\Models\StockCheckStock;
use App\Models\StockInventoryTemplate;
use App\Models\StockProduction;
use App\Models\StockPurchase;
use App\Models\StockPurchaseOrderMaterial;
use App\Models\StockPurchaseOrders;
use App\Models\StockSupplier;
use App\Models\StockTag;
use App\Models\StoreHouse;
use App\Models\StoreTransfare;
use App\Models\StoreTransfareStock;
use App\Models\Supplier;
use App\Models\SupplierTag;
use App\Models\Tag;
use App\Traits\ApiTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Spatie\SimpleExcel\SimpleExcelReader;

class WarehouseController extends Controller
{
    use ApiTrait;

    var $lang_code;
    var $vendor_id;
    var $vendor;
    var $vendor_name;

    public function __construct()
    {
        $this->lang_code = \request()->get('lang') ? \request()->get('lang') : get_default_languages();

        if (auth()->guard('vendor')->check()) {
            $this->vendor_id = vendor()->id;
            $this->vendor = vendor();
            $this->vendor_name = vendor()->first_name . ' ' . vendor()->family_name;
        } elseif (auth()->guard('vendor_employee')->check()) {
            $this->vendor_id = vendor_employee()->vendor->id;
            $this->vendor = vendor_employee();
            $this->vendor_name = vendor_employee()->name;
        }

    }

    public function stockCategories(Request $request)
    {
        $name = $request->get('name');
        $is_delete = $request->get('is_delete');
        $add_by = $request->get('add_by');
        $created_at = $request->get('created_at');

        $categories = StockCategory::where('vendor_id', $this->vendor_id);
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
                'created_at' => date('d/m/Y H:i', strtotime($item->created_at)),
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

        $categories = StockCategory::where('vendor_id', $this->vendor_id)->onlyTrashed();
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
                'created_at' => date('d/m/Y H:i', strtotime($item->created_at)),
                'status' => $item->status,
            ];
        });
        $msg = __('msg.cat_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $data, 200);
    }

    public function generateStokCategoryCode()
    {
        $last_item_id = 0;
        $last_item = StockCategory::where('vendor_id', $this->vendor_id)->withTrashed()->orderBy('id', 'DESC')->first();
        if ($last_item) {
            $num = explode('-', $last_item->number);
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

        if (!$request->category_id) {
            $last_item_id = 0;
            $last_item = StockCategory::where('vendor_id', $this->vendor_id)->withTrashed()->orderBy('id', 'DESC')->first();

            if ($last_item) {
                $num = explode('-', $last_item->number);
                $last_item_id = $num[1];
            }
        }

        $cat = StockCategory::where('id', $request->category_id)->where('vendor_id', $this->vendor_id)->first();
        if (!$cat)
            $cat = new StockCategory();
        $cat->vendor_id = $this->vendor_id;
        $cat->add_by = vendor()->first_name . ' ' . vendor()->family_name;
        if ($request->get('name_ar'))
            $cat->name_ar = $request->get('name_ar');
        if ($request->get('name_en'))
            $cat->name_en = $request->get('name_en');
        if (!$request->category_id)
            $cat->number = 'SC-' . ($last_item_id + 1);
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

    public function stockCategoryDetails(Request $request){
        $cat = StockCategory::where('vendor_id', $this->vendor_id)
            ->where('id', $request->category_id)->first();
        if (!$cat)
            return $this->errorResponse('Category not found', 400);
        $data = [
            'id' => $cat->id,
            'name_ar' => $cat->name_ar,
            'name_en' => $cat->name_en,
            'number' => $cat->number,
            'created_at' => date('d/m/Y H:i', strtotime($cat->created_at)),
            'status' => $cat->status,
        ];

        $msg = __('msg.cat_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $data, 200);
    }
    public function deleteStokCategory(Request $request)
    {

        $cat = StockCategory::where('vendor_id', $this->vendor_id)
            ->where('id', $request->category_id)->first();
        if (!$cat)
            return $this->errorResponse('Category not found', 400);

        $cat->delete();

        $msg = __('msg.cat_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function stockCategoryAddStocks(Request $request){
        $validator = Validator::make($request->all(), [
            'categories' => 'required',
            'stocks' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $categories = $request->categories;
        if (!is_array($categories))
            $categories = json_decode($categories);

        $stocks = $request->stocks;
        if (!is_array($stocks))
            $stocks = json_decode($stocks);

        if (count($categories) > 0 && count($stocks) > 0) {
            foreach ($categories as $cat) {
                foreach ($stocks as $stock) {
                    $cat_stock = StockCategoryStockMaterial::where('stock_category_id', $cat)->where('stock_id', $stock)->first();
                    if (!$cat_stock)
                        $cat_stock = new StockCategoryStockMaterial();
                    $cat_stock->stock_category_id = $cat;
                    $cat_stock->stock_id = $stock;
                    $cat_stock->save();
                }
            }
        }

        $msg = __('msg.add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function stockCategoryDeleteStocks(Request $request){
        $validator = Validator::make($request->all(), [
            'categories' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $categories = $request->categories;
        if (!is_array($categories))
            $categories = json_decode($categories);

        StockCategoryStockMaterial::whereIn('stock_category_id', $categories)->delete();


        $msg = __('msg.deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function stockCategoryDeleteList(Request $request){
        $validator = Validator::make($request->all(), [
            'categories' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $categories = $request->categories;
        if (!is_array($categories))
            $categories = json_decode($categories);

        StockCategory::whereIn('id',$categories)->delete();


        $msg = __('msg.user_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function stockCategoryRestoreList(Request $request){
        $validator = Validator::make($request->all(), [
            'categories' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $categories = $request->categories;
        if (!is_array($categories))
            $categories = json_decode($categories);

        StockCategory::withTrashed()->whereIn('id',$categories)->restore();


        $msg = __('msg.restore_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function stockCategoryRestoreSingleItem(Request $request){
        $validator = Validator::make($request->all(), [
            'stock_category_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        StockCategory::withTrashed()->where('id',$request->stock_category_id)->restore();


        $msg = __('msg.restore_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    ################## store houses########################
    public function storeHouse(Request $request)
    {
        $name = $request->get('name');
        $number = $request->get('number');
        $branches = $request->get('branches');
        $created_at = $request->get('created_at');
        $store_house = StoreHouse::where('vendor_id', $this->vendor_id)->where('is_payment', 1)->orWhere(function ($q) {
            $q->where('vendor_id', $this->vendor_id)->where('is_free', 1);
        });

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
        $store_house = StoreHouse::where('vendor_id', $this->vendor_id)->Active()->where('is_payment', 1)->orWhere(function ($q) {
            $q->where('vendor_id', $this->vendor_id)->where('is_free', 1);
        });

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
        $store_house = StoreHouse::where('vendor_id', $this->vendor_id)->where('status', 0)->where('is_payment', 1)->orWhere(function ($q) {
            $q->where('vendor_id', $this->vendor_id)->where('is_free', 1);
        });

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
        $last_item = StoreHouse::where('vendor_id', $this->vendor_id)->orderBy('id', 'DESC')->first();
        if ($last_item) {
            $num = explode('-', $last_item->number);
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
        $last_item = StoreHouse::where('vendor_id', $this->vendor_id)->orderBy('id', 'DESC')->first();
        if ($last_item) {
            $num = explode('-', $last_item->number);
            $last_item_id = $num[1];
        }
        $store = StoreHouse::where('vendor_id', $this->vendor_id)
            ->where('id', $request->get('store_house_id'))->first();
        if ($store) {
            if ($request->name_ar)
                $store->name_ar = $request->name_ar;
            if ($request->name_en)
                $store->name_en = $request->name_en;
            if ($request->branches)
                $store->branches = $request->branches;
            $store->status = $request->get('status');
            $store->save();
        } else {
            $directData = $this->myfatorah_payment($request, (float)vendor()->active_plan->warehouse_price);

            if (isset($directData->Status) && $directData->Status == 'SUCCESS') {
                $store = new StoreHouse();
                $store->number = 'SH-' . ($last_item_id + 1);
                $store->vendor_id = $this->vendor_id;
                $store->name_ar = 'مستودع 1';
                $store->name_en = 'Store warehouse 1';
                $store->status = 1;
                $store->is_payment = 1;
                $store->payment = json_encode($directData);
                $store->save();
            }else{
                $msg = isset($directData->ErrorMessage) && $directData->ErrorMessage ? $directData->ErrorMessage : 'Payment failed';
                return $this->errorResponse($msg, 400);
            }
        }

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

        $store = StoreHouse::where('vendor_id', $this->vendor_id)
            ->where('id', $request->get('store_house_id'))->first();
        if (!$store)
            return $this->errorResponse('Store house not found', 400);

        $store->delete();

        $msg = __('msg.store_house_delete_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function storeHouseAddBranches(Request $request){
        $validator = Validator::make($request->all(), [
            'store_houses' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $store_houses = $request->store_houses;

        StoreHouse::whereIn('id',$store_houses)->update([
            'branches' => $request->branches ? $request->branches : null
        ]);

        $msg = __('msg.add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function storeHouseAddActive(Request $request){
        $validator = Validator::make($request->all(), [
            'store_houses' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $store_houses = $request->store_houses;

        StoreHouse::whereIn('id',$store_houses)->update([
            'branches' => $request->$request->branches
        ]);

        $msg = __('msg.add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function storeHouseDeleteList(Request $request){
        $validator = Validator::make($request->all(), [
            'store_houses' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $store_houses = $request->store_houses;
        if (!is_array($store_houses))
            $store_houses = json_decode($store_houses);

        StoreHouse::whereIn('id',$store_houses)->delete();


        $msg = __('msg.deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    ########################### stoks ###################
    public function tagsList(Request $request)
    {
        if ($request->get('type')) {
            $data = $this->tage_type($request->get('type'));

        } else {
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
        }
        $msg = __('msg.tag_get_success', [], $this->lang_code);
        return $this->dataResponse($msg, $data);
    }

    public function generateTagsCode()
    {
        $last_item_id = 0;
        $last_item = Tag::where('vendor_id', $this->vendor_id)->orderBy('id', 'DESC')->first();
        if ($last_item) {
            $num = explode('-', $last_item->number);
            $last_item_id = $num[1];
        }
        $data = [
            'operation_number' => 'T-' . ($last_item_id + 1)
        ];
        return $this->dataResponse('generated successfully', $data);
    }

    public function tage_type($type)
    {
        return Tag::where('vendor_id', $this->vendor_id)->where('type', $type)->get();
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
        $last_item = Tag::where('vendor_id', $this->vendor_id)->orderBy('id', 'DESC')->first();
        if ($last_item) {
            $num = explode('-', $last_item->number);
            $last_item_id = $num[1];
        }
        $tag = Tag::where('vendor_id', $this->vendor_id)
            ->where('number', $request->get('number'))
            ->where('type', $request->get('type'))->first();
        if ($tag)
            return $this->errorResponse(__('msg.tag_anumber_is_already_used', [], $this->lang_code), 400);
        $tag = new Tag();

        $tag->vendor_id = $this->vendor_id;
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
        $tag = Tag::where('vendor_id', $this->vendor_id)
            ->where('id', $request->get('tag_id'))->first();
        if (!$tag)
            return $this->errorResponse(__('msg.tag_not_found', [], $this->lang_code), 400);

        $tag->vendor_id = $this->vendor_id;
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
        $tag = Tag::where('vendor_id', $this->vendor_id)
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
        $stocks = Stock::where('vendor_id', $this->vendor_id);
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
        $stocks = Stock::where('vendor_id', $this->vendor_id);
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
        $last_item = Stock::withTrashed()->where('vendor_id', $this->vendor_id)->orderBy('id', 'DESC')->first();
        if ($last_item) {
            $num = explode('-', $last_item->code);
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
        $stocks = Stock::where('vendor_id', $this->vendor_id);
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
        $last_item = Stock::withTrashed()->where('vendor_id', $this->vendor_id)->orderBy('id', 'DESC')->first();
        if ($last_item) {
            $num = explode('-', $last_item->code);
            $last_item_id = $num[1];
        }

        $stock = Stock::where('vendor_id', $this->vendor_id)->where('id', $request->stock_id)->first();
        if (!$stock)
            $stock = new Stock();
        $stock->vendor_id = $this->vendor_id;
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
        $stock = Stock::where('vendor_id', $this->vendor_id)->where('id', $request->get('stock_id'))->first();
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
        $stock = Stock::where('vendor_id', $this->vendor_id)->where('id', $request->get('stock_id'))->first();
        if (!$stock)
            return $this->errorResponse(__('msg.stock_not_found', [], $this->lang_code), 400);
        $stock->delete();
        $msg = __('msg.stocks_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function addTagStock(Request $request)
    {
        $stock_id = $request->stock_id;
        if ($request->tags) {
            $tags = json_decode($request->tags);
            if (count($tags) > 0) {
                foreach ($tags as $tag) {
                    $stok_tag = StockTag::where('stock_id', $stock_id)->where('tag_id', $tag)->first();
                    if (!$stok_tag)
                        $stok_tag = new StockTag();
                    $stok_tag->stock_id = $stock_id;
                    $stok_tag->tag_id = $tag;
                    $stok_tag->save();
                }
            }
            $msg = __('msg.tag_add_success', [], $this->lang_code);

            return $this->successResponse($msg, 200);
        }
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
    public function stocksAddTags(Request $request){
        $validator = Validator::make($request->all(), [
            'stocks' => 'required',
            'tags' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $stocks = $request->stocks;
        if (!is_array($stocks))
            $stocks = json_decode($stocks);

        $tags = $request->tags;
        if (!is_array($tags))
            $tags = json_decode($tags);
        if (count($stocks) > 0 && count($tags) > 0) {
            foreach ($stocks as $stock) {
                foreach ($tags as $tag) {
                    $stok_tag = StockTag::where('stock_id', $stock)->where('tag_id', $tag)->first();
                    if (!$stok_tag)
                        $stok_tag = new StockTag();
                    $stok_tag->stock_id = $stock;
                    $stok_tag->tag_id = $tag;
                    $stok_tag->save();
                }
            }
        }

        $msg = __('msg.tag_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function stocksDeleteTags(Request $request){
        $validator = Validator::make($request->all(), [
            'stocks' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $stocks = $request->stocks;
        if (!is_array($stocks))
            $stocks = json_decode($stocks);


                StockTag::whereIn('stock_id',$stocks)->delete();

        $msg = __('msg.tag_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function stocksListDelete(Request $request){
        $validator = Validator::make($request->all(), [
            'stocks' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $stocks = $request->stocks;
        if (!is_array($stocks))
            $stocks = json_decode($stocks);

            Stock::whereIn('id',$stocks)->delete();

        $msg = __('msg.stocks_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function stocksRestoreDeleted(Request $request){
        $validator = Validator::make($request->all(), [
            'stocks' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $stocks = $request->stocks;
        if (!is_array($stocks))
            $stocks = json_decode($stocks);

        Stock::withTrashed()->whereIn('id',$stocks)->restore();

        $msg = __('msg.stocks_restore_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function stocksRestoreSingleDeleted(Request $request){
        $validator = Validator::make($request->all(), [
            'stock_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        Stock::withTrashed()->where('id',$request->stock_id)->restore();

        $msg = __('msg.stocks_restore_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function stocksAddSuppliers(Request $request){
        $validator = Validator::make($request->all(), [
            'suppliers' => 'required',
            'stocks' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $stocks = $request->stocks;
        if (!is_array($stocks))
            $stocks = json_decode($stocks);

        $suppliers = $request->suppliers;
        if (!is_array($suppliers))
            $suppliers = json_decode($suppliers);
        if (count($stocks) > 0 && count($suppliers) > 0) {
            foreach ($stocks as $stock) {
                foreach ($suppliers as $supplier) {
                    $stok_supp = StockSupplier::where('stock_id', $stock)->where('supplier_id', $supplier)->first();
                    if (!$stok_supp)
                        $stok_supp = new StockSupplier();
                    $stok_supp->stock_id = $stock;
                    $stok_supp->supplier_id = $supplier;
                    $stok_supp->save();
                }
            }
        }

        $msg = __('msg.suppliers_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function stocksDeleteSuppliers(Request $request){
        $validator = Validator::make($request->all(), [
            'stocks' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $stocks = $request->stocks;
        if (!is_array($stocks))
            $stocks = json_decode($stocks);


        StockSupplier::whereIn('stock_id',$stocks)->delete();

        $msg = __('msg.tag_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function stocksAddStorehouse(Request $request){
        $validator = Validator::make($request->all(), [
            'stocks' => 'required',
            'store_house_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $stocks = $request->stocks;
        if (!is_array($stocks))
            $stocks = json_decode($stocks);
        Stock::whereIn('id',$stocks)->update([
            'store_house_id' => $request->store_house_id
        ]);

        $msg = __('msg.stocks_restore_success', [], $this->lang_code);

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

        $suppliers = Supplier::where('vendor_id', $this->vendor_id);
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

        $suppliers = Supplier::where('vendor_id', $this->vendor_id)->onlyTrashed();
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
        $last_item = Supplier::withTrashed()->where('vendor_id', $this->vendor_id)->orderBy('id', 'DESC')->first();
        if ($last_item) {
            $num = explode('-', $last_item->code);
            $last_item_id = $num[1];
        }
        $data = [
            'operation_number' => 'SP-' . str_pad($last_item_id + 1, 5, "0", STR_PAD_LEFT)
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
        $last_item = Supplier::withTrashed()->where('vendor_id', $this->vendor_id)->orderBy('id', 'DESC')->first();
        if ($last_item) {
            $num = explode('-', $last_item->code);
            $last_item_id = $num[1];
        }

        $supplier = new Supplier();
        $supplier->vendor_id = $this->vendor_id;
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
        $supplier = Supplier::where('vendor_id', $this->vendor_id)->where('id', $request->get('supplier_id'))->first();
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
        $supplier = Supplier::where('vendor_id', $this->vendor_id)->where('id', $request->get('supplier_id'))->first();
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
        $supplier = Supplier::where('vendor_id', $this->vendor_id)->where('id', $request->get('supplier_id'))->first();
        if (!$supplier)
            return $this->errorResponse(__('msg.supplier_not_found', [], $this->lang_code), 400);

        $supplier->delete();

        $msg = __('msg.supplier_updated_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function addSupplierTag(Request $request)
    {
        if ($request->tags) {
            if (is_array($request->tags)) {
                foreach ($request->tags as $tag) {
                    $supplier_tag = SupplierTag::where('tag_id', $tag)->where('supplier_id', $request->supplier_id)->first();
                    if (!$supplier_tag)
                        $supplier_tag = new SupplierTag();
                    $supplier_tag->tag_id = $tag;
                    $supplier_tag->supplier_id = $request->supplier_id;
                    $supplier_tag->save();
                }
                $msg = __('msg.tag_add_success', [], $this->lang_code);
            }
            return $this->successResponse($msg, 200);
        }
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

        $supplier_id = $request->supplier_id;
        $stocks = $request->stocks;
        if ($stocks) {
            foreach ($stocks as $stock) {
                $stok_supplier = StockSupplier::where('stock_id', $stock)->where('supplier_id', $supplier_id)->first();
                if (!$stok_supplier)
                    $stok_supplier = new StockSupplier();
                $stok_supplier->stock_id = $stock;
                $stok_supplier->supplier_id = $supplier_id;
                $stok_supplier->save();

            }
        }

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


    public function supplierAddTags(Request $request){
        $validator = Validator::make($request->all(), [
            'suppliers' => 'required',
            'tags' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $suppliers = $request->suppliers;
        if (!is_array($suppliers))
            $suppliers = json_decode($suppliers);

        $tags = $request->tags;
        if (!is_array($tags))
            $tags = json_decode($tags);
        if (count($suppliers) > 0 && count($tags) > 0) {
            foreach ($suppliers as $supplier) {
                foreach ($tags as $tag) {
                    $supplier_tag = SupplierTag::where('tag_id', $tag)->where('supplier_id',$supplier)->first();
                    if (!$supplier_tag)
                        $supplier_tag = new SupplierTag();
                    $supplier_tag->tag_id = $tag;
                    $supplier_tag->supplier_id = $supplier;
                    $supplier_tag->save();
                }
            }
        }

        $msg = __('msg.tag_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function supplierDeleteTags(Request $request){
        $validator = Validator::make($request->all(), [
            'suppliers' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $suppliers = $request->suppliers;
        if (!is_array($suppliers))
            $suppliers = json_decode($suppliers);

            SupplierTag::whereIn('supplier_id',$suppliers)->delete();

        $msg = __('msg.tag_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function supplierListDelete(Request $request){
        $validator = Validator::make($request->all(), [
            'suppliers' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $suppliers = $request->suppliers;
        if (!is_array($suppliers))
            $suppliers = json_decode($suppliers);

        Supplier::whereIn('id',$suppliers)->delete();

        $msg = __('msg.suppliers_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function supplierRestoreDeleted(Request $request){
        $validator = Validator::make($request->all(), [
            'suppliers' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $suppliers = $request->suppliers;
        if (!is_array($suppliers))
            $suppliers = json_decode($suppliers);

        Supplier::withTrashed()->whereIn('id',$suppliers)->restore();

        $msg = __('msg.supplier_restore_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function supplierRestoreSingleDeleted(Request $request){
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $suppliers = $request->suppliers;
        if (!is_array($suppliers))
            $suppliers = json_decode($suppliers);

        Supplier::where('id',$request->supplier_id)->restore();

        $msg = __('msg.supplier_restore_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function addSuppliersStocks(Request $request){
        $validator = Validator::make($request->all(), [
            'suppliers' => 'required',
            'stocks' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $stocks = $request->stocks;
        if (!is_array($stocks))
            $stocks = json_decode($stocks);

        $suppliers = $request->suppliers;
        if (!is_array($suppliers))
            $suppliers = json_decode($suppliers);
        if (count($stocks) > 0 && count($suppliers) > 0) {
            foreach ($stocks as $stock) {
                foreach ($suppliers as $supplier) {
                    $stok_supp = StockSupplier::where('stock_id', $stock)->where('supplier_id', $supplier)->first();
                    if (!$stok_supp)
                        $stok_supp = new StockSupplier();
                    $stok_supp->stock_id = $stock;
                    $stok_supp->supplier_id = $supplier;
                    $stok_supp->save();
                }
            }
        }

        $msg = __('msg.suppliers_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteSuppliersStocks(Request $request){
        $validator = Validator::make($request->all(), [
            'stocks' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $stocks = $request->stocks;
        if (!is_array($stocks))
            $stocks = json_decode($stocks);


        StockSupplier::whereIn('stock_id',$stocks)->delete();

        $msg = __('msg.tag_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    ################# inventory template ##########3

    public function getInvintoryTemplate(Request $request)
    {
        $name = $request->get('name');
        $secondary_name = $request->get('secondary_name');
        $code = $request->get('code');
        $inventory = StockInventoryTemplate::where('vendor_id', $this->vendor_id);
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
        $last_item = StockInventoryTemplate::where('vendor_id', $this->vendor_id)->orderBy('id', 'DESC')->first();
        if ($last_item) {
            $num = explode('-', $last_item->code);
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
        $last_item = StockInventoryTemplate::where('vendor_id', $this->vendor_id)->orderBy('id', 'DESC')->first();
        if ($last_item) {
            $num = explode('-', $last_item->code);
            $last_item_id = $num[1];
        }
        $inventory = new StockInventoryTemplate();
        $inventory->vendor_id = $this->vendor_id;
        $inventory->name = $request->get('name');
        $inventory->secondary_name = $request->get('secondary_name');
        $inventory->code = 'SIT-' . str_pad($last_item_id + 1, 5, "0", STR_PAD_LEFT);
        $inventory->save();
        $msg = __('msg.inventory_template_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function editStockInventoryTemplate(Request $request)
    {
        $inventory = StockInventoryTemplate::where('vendor_id', $this->vendor_id)->where('id', $request->get('id'))->first();
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
        $inventory = StockInventoryTemplate::where('vendor_id', $this->vendor_id)->where('id', $request->get('id'))->first();
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
        $last_item = StockPurchaseOrders::where('vendor_id', $this->vendor_id)->orderBy('id', 'DESC')->first();
        if ($last_item) {
            $num = explode('-', $last_item->code);
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
            $purchase_orders = StockPurchaseOrders::where('vendor_id', $this->vendor_id);
        elseif ($request->get('type') == 1) //draft
            $purchase_orders = StockPurchaseOrders::where('vendor_id', $this->vendor_id)->where('status_id', 0);
        elseif ($request->get('type') == 2) // sent
            $purchase_orders = StockPurchaseOrders::where('vendor_id', $this->vendor_id)->where('status_id', 1);
        elseif ($request->get('type') == 3) // canceled
            $purchase_orders = StockPurchaseOrders::where('vendor_id', $this->vendor_id)->where('status_id', 3);
        else
            $purchase_orders = StockPurchaseOrders::where('vendor_id', $this->vendor_id);

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
                    $tag->whereIn('id',$branch_tag);
                });
            });
        $purchase_orders = $purchase_orders->where('type_id', 0)->orderBy('id', 'DESC')->paginate(10);

        $data = [
            'count' => $purchase_orders->count(),
            'currentPage' => $purchase_orders->currentPage(),
            'firstItem' => $purchase_orders->firstItem(),
            'getOptions' => $purchase_orders->getOptions(),
            'hasPages' => $purchase_orders->hasPages(),
            'lastItem' => $purchase_orders->lastItem(),
            'lastPage' => $purchase_orders->lastPage(),
            'nextPageUrl' => $purchase_orders->nextPageUrl(),
            'perPage' => $purchase_orders->perPage(),
            'total' => $purchase_orders->total(),
            'getPageName' => $purchase_orders->getPageName(),
            'data' => $purchase_orders->map(function ($purchase) {
                return [
                    'id' => $purchase->id,
                    'supplier_name' => isset($purchase->supplier) && $purchase->supplier->name ? $purchase->supplier->name : '',
                    'supplier_company_name' => isset($purchase->supplier) && $purchase->supplier->name ? $purchase->supplier->name : '',
                    'branch_name' => isset($purchase->branch) && $purchase->branch->name($this->lang_code) ? $purchase->branch->name($this->lang_code) : '',
                    'code' => $purchase->code,
                    'status_name' => $purchase->status_name($this->lang_code),
                    'status_id' => $purchase->status_id,
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
            $purchase_orders = StockPurchaseOrders::where('vendor_id', $this->vendor_id);
        elseif ($request->get('type') == 1) //draft
            $purchase_orders = StockPurchaseOrders::where('vendor_id', $this->vendor_id)->where('status_id', 0);
        elseif ($request->get('type') == 2) // sent
            $purchase_orders = StockPurchaseOrders::where('vendor_id', $this->vendor_id)->where('status_id', 1);
        elseif ($request->get('type') == 3) // canceled
            $purchase_orders = StockPurchaseOrders::where('vendor_id', $this->vendor_id)->where('status_id', 3);
        else
            $purchase_orders = StockPurchaseOrders::where('vendor_id', $this->vendor_id);

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
                    'id' => $purchase->id,
                    'supplier_name' => isset($purchase->supplier) && $purchase->supplier->name ? $purchase->supplier->name : '',
                    'supplier_company_name' => isset($purchase->supplier) && $purchase->supplier->name ? $purchase->supplier->name : '',
                    'branch_name' => isset($purchase->branch) && $purchase->branch->name($this->lang_code) ? $purchase->branch->name($this->lang_code) : '',
                    'code' => $purchase->code,
                    'status_name' => $purchase->status_name($this->lang_code),
                    'status_id' => $purchase->status_id,
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
        $last_item = StockPurchaseOrders::where('vendor_id', $this->vendor_id)->orderBy('id', 'DESC')->first();
        if ($last_item) {
            $num = explode('-', $last_item->code);
            $last_item_id = $num[1];
        }

        $purchase_order = new StockPurchaseOrders();
        $purchase_order->vendor_id = $this->vendor_id;
        $purchase_order->code = 'buy-' . str_pad($last_item_id + 1, 3, "0", STR_PAD_LEFT);
        $purchase_order->supplier_id = $request->get('supplier_id');
        $purchase_order->branch_id = $request->get('branch_id');
        $purchase_order->extra_price = $request->get('extra_price') > 0 ? $request->get('extra_price') : 0;
        $purchase_order->delivery_date = $request->get('delivery_date');
        $purchase_order->notes = $request->get('notes');
        $purchase_order->work_date = $request->work_date;
        $purchase_order->created_by_name = $this->vendor_name;
        $purchase_order->type_id = 0;
        $purchase_order->save();

        $msg = __('msg.stock_purchase_orders_created_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function updateStockPurchaseOrders(Request $request)
    {
        $purchase_order = StockPurchaseOrders::where('vendor_id', $this->vendor_id)->where('id', $request->get('purchase_order_id'))->first();
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
        $purchase = StockPurchaseOrders::where('vendor_id', $this->vendor_id)->where('id', $request->get('purchase_order_id'))->first();
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
            'stock_purchase_order_materials' => isset($purchase->stock_materials) && count($purchase->stock_materials) > 0 ? $purchase->stock_materials->map(function ($material) {
                return [
                    'id' => $material->id,
                    'qty' => (float)$material->qty,
                    'price' => (float)$material->price,
                    'stock_qty' => 0,
                    'stock_name' => $material->stock->name,
                    'stock_code' => $material->stock->code,
                    'stock_price' => $material->stock->amount,
                ];
            }) : []
        ];

        $msg = __('msg.stock_purchase_orders_get_success', [], $this->lang_code);
        return $this->dataResponse($msg, $data);
    }

    public function generateStokPurchaseCode()
    {
        $last_item_id = 0;
        $last_item = StockPurchase::where('vendor_id', $this->vendor_id)->orderBy('id', 'DESC')->first();
        if ($last_item) {
            $num = explode('-', $last_item->code);
            $last_item_id = $num[1];
        }
        $data = [
            'operation_number' => 'PUR-' . str_pad($last_item_id + 1, 3, "0", STR_PAD_LEFT)
        ];
        return $this->dataResponse('generated successfully', $data);
    }

    public function StockPurchaseOrderChangeStatusSent(Request $request)
    {
        $purchase = StockPurchaseOrders::where('vendor_id', $this->vendor_id)
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
        $purchase = StockPurchaseOrders::where('vendor_id', $this->vendor_id)
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
        $purchase = StockPurchaseOrders::where('vendor_id', $this->vendor_id)
            ->where('id', $request->get('purchase_order_id'))->where('status_id', '!=', 2)->first();
        if (!$purchase)
            return $this->errorResponse(__('msg.stock_purchase_order_not_found', [], $this->lang_code), 400);
        $purchase->status_id = 2;
        $purchase->save();

        $last_item_id = 0;
        $last_item = StockPurchase::where('vendor_id', $this->vendor_id)->orderBy('id', 'DESC')->first();
        if ($last_item) {
            $num = explode('-', $last_item->code);
            $last_item_id = $num[1];
        }
        $stock_purch = new StockPurchase();
        $stock_purch->vendor_id = $this->vendor_id;
        $stock_purch->created_by_name = $this->vendor_name;
        $stock_purch->sender_by_name = $this->vendor_name;
        $stock_purch->code = 'PUR-' . str_pad($last_item_id + 1, 3, "0", STR_PAD_LEFT);
        $stock_purch->supplier_id = $purchase->supplier_id;
        $stock_purch->branch_id = $purchase->branch_id;
        $stock_purch->extra_price = $purchase->extra_price;
        $stock_purch->save();

        $msg = __('msg.stock_purchase_orders_updated_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function addStockPurchaseOrderStockMaterial(Request $request)
    {


        $purchase = StockPurchaseOrders::where('vendor_id', $this->vendor_id)
            ->where('id', $request->purchase_order_id)->first();
        if (!$purchase)
            return $this->errorResponse(__('msg.stock_purchase_order_not_found', [], $this->lang_code), 400);

        if ($request->type = 1 && $request->stock_materials) {
            $stock_materials = $request->stock_materials;
            if (!is_array($request->stock_materials))
                $stock_materials = json_decode($request->stock_materials);
            foreach ($stock_materials as $material) {
                $stock_pur_or_material = StockPurchaseOrderMaterial::where('stock_purchase_order_id', $purchase->id)
                    ->where('stock_material_id', $material)->first();
                if (!$stock_pur_or_material)
                    $stock_pur_or_material = new StockPurchaseOrderMaterial();
                $stock_pur_or_material->stock_purchase_order_id = $purchase->id;
                $stock_pur_or_material->stock_material_id = $material;
                $stock_pur_or_material->type = 1;
                $stock_pur_or_material->save();
            }
        } elseif ($request->type = 2 && $request->stock_materials_tags) {
            $stock_materials_tags = $request->stock_materials_tags;
            if (!is_array($request->stock_materials_tags))
                $stock_materials_tags = json_decode($request->stock_materials_tags);
            foreach ($stock_materials_tags as $tag) {
                $stock_material = Stock::where('vendor_id', $this->vendor_id)->whereHas('tags', function ($q) use ($tag) {
                    $q->where('name_ar', 'LIKE', '%' . $tag . '%')->orWhere('name_en', 'LIKE', '%' . $tag . '%');
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
        } elseif ($request->type = 3 && $request->stock_materials_suppliers) {
            $stock_materials_suppliers = $request->stock_materials_suppliers;
            if (!is_array($request->stock_materials_suppliers))
                $stock_materials_suppliers = json_decode($request->stock_materials_suppliers);
            foreach ($stock_materials_suppliers as $supplier) {
                $stock_material = Stock::where('vendor_id', $this->vendor_id)->whereHas('tags', function ($q) use ($supplier) {
                    $q->where('name_ar', 'LIKE', '%' . $supplier . '%')->orWhere('name_en', 'LIKE', '%' . $supplier . '%');
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
        } elseif ($request->type = 4) {
            if (!$request->hasFile('csv_file'))
                return $this->errorResponse(__('msg.please_add_csv_file', [], $this->lang_code), 400);


            $file = $request->csv_file;
            $type = $file->getClientOriginalExtension();
            $myfile = $file->getClientOriginalName();
            $destinationPath = 'uploads/csv_files';
            $file_data = $file->move(public_path($destinationPath), $myfile);

            if ($type <> 'csv')
                return $this->errorResponse(__('msg.only_csv_is_allowed', [], $this->lang_code), 400);
            SimpleExcelReader::create($file_data->getPathName())->getRows()
                ->each(function (array $rowProperties) use ($purchase) {
                    $arr_val = array_values($rowProperties)[0];
                    if (isset($arr_val)) {
                        $sku_item = explode(';', $arr_val);
                        $stock_code = $sku_item[0];
                        $stock = Stock::where('code', $stock_code)->first();
                        if ($stock) {
                            $stock_pur_or_material = StockPurchaseOrderMaterial::where('stock_purchase_order_id', $purchase->id)
                                ->where('stock_material_id', $stock->id)->first();
                            if (!$stock_pur_or_material)
                                $stock_pur_or_material = new StockPurchaseOrderMaterial();
                            $stock_pur_or_material->stock_purchase_order_id = $purchase->id;
                            $stock_pur_or_material->stock_material_id = $stock->id;
                            $stock_pur_or_material->type = 4;
                            $stock_pur_or_material->qty = isset($sku_item[2]) && $sku_item[2] > 0 ? $sku_item[2] : 1;
                            $stock_pur_or_material->price = $stock_pur_or_material->qty * $stock->amount;
                            $stock_pur_or_material->save();
                        }
                    }
                });
            File::delete($file_data->getPathName());
        }

        $msg = __('msg.stocks_created_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function updatePurchaseOrderStockMaterialQty(Request $request)
    {
        if (!$request->materials)
            return $this->errorResponse(__('msg.materials_field_required', [], $this->lang_code), 400);
        $materials = json_decode($request->materials);
        if (count($materials) <= 0)
            return $this->errorResponse(__('msg.stock_not_found', [], $this->lang_code), 400);
        foreach ($materials as $material) {
            $item = StockPurchaseOrderMaterial::find($material->id);
            if ($item) {
                $item->qty = $material->qty;
                $item->price = $item->qty * $item->stock->amount;
                $item->save();
            }
        }

        $msg = __('msg.stocks_updated_success', [], $this->lang_code);
        return $this->successResponse($msg, 200);
    }
    public function stockPurchaseOrderDeleteList(Request $request){
        $validator = Validator::make($request->all(), [
            'stock_purchase_orders' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $stock_purchase_orders = $request->stock_purchase_orders;
        if (!is_array($stock_purchase_orders))
            $stock_purchase_orders = json_decode($stock_purchase_orders);


        StockPurchaseOrders::whereIn('id',$stock_purchase_orders)->delete();

        $msg = __('msg.deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    ###################### stock production ##################33
    public function stockProductions(Request $request)
    {

        $code = $request->code;
        $work_date = $request->work_date;
        $sender_date = $request->sender_date;
        $status_id = $request->status_id;
        $tag = $request->tag;
        $store_house_id = $request->store_house_id;
        $created_at = $request->created_at;
        $sender = $request->sender;
        $creator = $request->creator;
        $stock_production = StockProduction::where('vendor_id', $this->vendor_id);
        if ($code)
            $stock_production = $stock_production->where('code', 'LIKE', '%' . $code . '%');
        if ($work_date)
            $stock_production = $stock_production->whereDate('work_date', $work_date);
        if ($sender_date)
            $stock_production = $stock_production->whereDate('sender_date', $sender_date);
        if ($created_at)
            $stock_production = $stock_production->whereDate('created_at', $created_at);
        if ($status_id)
            $stock_production = $stock_production->where('status_id', $status_id);
        if ($store_house_id)
            $stock_production = $stock_production->where('store_house_id', $store_house_id);
        if ($tag)
            $stock_production = $stock_production->whereHas('branch', function ($q) use ($tag) {
                $q->whereHas('tags', function ($t) use ($tag) {
                    $t->where('name_ar', 'LIKE', '%' . $tag . '%');
                });
            });
        if ($sender)
            $stock_production = $stock_production->where('sender', 'LIKE', '%' . $sender . '%');
        if ($creator)
            $stock_production = $stock_production->where('creator', 'LIKE', '%' . $creator . '%');

        $stock_production = $stock_production->orderBy('id', 'DESC')->get();

        $data = $stock_production->map(function ($production) {
            return [
                'branch' => isset($production->branch) ? $production->branch->name($this->lang_code) : '',
                'work_date' => date('d/m/Y H:i', strtotime($production->work_date)),
                'sender_date' => date('d/m/Y H:i', strtotime($production->sender_date)),
                'created_at' => date('d/m/Y H:i', strtotime($production->created_at)),
                'sender' => $production->sender,
                'creator' => $production->creator,
                'amount' => $production->amount,
                'id' => $production->id,
            ];
        });
        $msg = __('msg.stock_production_get_success', [], $this->lang_code);
        return $this->dataResponse($msg, $data, 200);
    }

    public function createStockProduction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $production = new StockProduction();
        $production->vendor_id = $this->vendor_id;
        $production->creator = $this->vendor_name;
        $production->branch_id = $request->branch_id;
        $production->status_id = 2;
        $production->save();

        $msg = __('msg.stock_production_created_success', [], $this->lang_code);
        return $this->successResponse($msg, 200);
    }

    public function updateStockProduction(Request $request)
    {

        $production = StockProduction::where('vendor_id', $this->vendor_id)->where('id', $request->production_id)->first();
        if (!$production)
            return $this->errorResponse(__('msg.stock_production_not_found', [], $this->lang_code), 400);
        if ($request->branch_id)
            $production->branch_id = $request->branch_id;
        if ($request->amount)
            $production->amount = $request->amount;
        $production->save();

        $msg = __('msg.stock_production_updated_success');
        return $this->successResponse($msg, 200);
    }

    public function stockProductionUpdateStatus(Request $request)
    {
        $production = StockProduction::where('vendor_id', $this->vendor_id)->where('id', $request->production_id)->first();
        if (!$production)
            return $this->errorResponse(__('msg.stock_production_not_found', [], $this->lang_code), 400);
        if ($request->status_id == 3) {
            $production->sender_date = date('Y-m-d H:i');
            $production->sender = vendor()->first_name . ' ' . vendor()->family_name;
            $production->status_id = 3;
            $production->save();
        } elseif ($request->status_id == 4) {
            $production->status_id = 4;
            $production->save();
        }

        $msg = __('msg.status_updated_success', [], $this->lang_code);
        return $this->successResponse($msg, 200);
    }

    public function stockProductionDetails(Request $request)
    {
        $production = StockProduction::where('vendor_id', $this->vendor_id)->where('id', $request->production_id)->first();
        if (!$production)
            return $this->errorResponse(__('msg.stock_production_not_found', [], $this->lang_code), 400);
        $data = [
            'branch' => isset($production->branch) ? $production->branch->name($this->lang_code) : '',
            'work_date' => date('d/m/Y H:i', strtotime($production->work_date)),
            'sender_date' => date('d/m/Y H:i', strtotime($production->sender_date)),
            'created_at' => date('d/m/Y H:i', strtotime($production->created_at)),
            'sender' => $production->sender,
            'creator' => $production->creator,
            'amount' => $production->amount,
            'status_id' => $production->status_id,
            'production_material' => isset($production->production_material) && count($production->production_material) > 0 ? $production->production_material->map(function ($mat) {
                return [
                    'id' => $mat->id,
                    'material_id' => $mat->id,
                    'stock_name' => isset($mat->stock) ? $mat->stock->name : '',
                    'stock_code' => isset($mat->stock) ? $mat->stock->code : '',
                    'storage_unit' => isset($mat->stock) ? $mat->stock->storage_unit : '',
                    'qty' => $mat->qty,

                ];
            }) : []
        ];
        $msg = __('msg.stock_production_get_success', [], $this->lang_code);
        return $this->dataResponse($msg, $data, 200);
    }

    public function productionAddStockMaterial(Request $request)
    {
        if ($request->type == 1) {
            $validator = Validator::make($request->all(), [
                'stock_id' => 'required',
                'production_id' => 'required',
                'qty' => 'required',
            ]);

            if ($validator->fails())
                return $this->errorResponse($validator->errors()->first(), 400);

            $prod_s_m = new ProductionStockMaterial();
            $prod_s_m->production_id = $request->production_id;
            $prod_s_m->stock_id = $request->stock_id;
            $prod_s_m->qty = $request->qty;
            $prod_s_m->save();
        } elseif ($request->type == 2) {
            if (!$request->hasFile('csv_file'))
                return $this->errorResponse(__('msg.please_add_csv_file', [], $this->lang_code), 400);
            $file = $request->csv_file;
            $type = $file->getClientOriginalExtension();
            $myfile = $file->getClientOriginalName();
            $destinationPath = 'uploads/csv_files';
            $file_data = $file->move(public_path($destinationPath), $myfile);

            if ($type <> 'csv')
                return $this->errorResponse(__('msg.only_csv_is_allowed', [], $this->lang_code), 400);
            SimpleExcelReader::create($file_data->getPathName())->getRows()
                ->each(function (array $rowProperties) use ($request) {
                    $arr_val = array_values($rowProperties)[0];
                    if (isset($arr_val)) {
                        $sku_item = explode(';', $arr_val);
                        $stock_code = $sku_item[0];
                        $stock = Stock::where('code', $stock_code)->first();
                        if ($stock) {
                            $prod_s_m = ProductionStockMaterial::where('production_id', $request->production_id)
                                ->where('stock_id', $stock->id)->first();
                            if (!$prod_s_m)
                                $prod_s_m = new ProductionStockMaterial();
                            $prod_s_m->production_id = $request->production_id;
                            $prod_s_m->stock_id = $stock->id;
                            $prod_s_m->qty = isset($sku_item[2]) && $sku_item[2] > 0 ? $sku_item[2] : 1;
                            $prod_s_m->save();
                        }
                    }
                });
            File::delete($file_data->getPathName());
        }

        $msg = __('msg.stocks_created_success', [], $this->lang_code);
        return $this->successResponse($msg, 200);
    }
    public function productionsDeleteList(Request $request){
        $validator = Validator::make($request->all(), [
            'productions' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $productions = $request->productions;
        if (!is_array($productions))
            $productions = json_decode($productions);


        StockProduction::whereIn('id',$productions)->delete();

        $msg = __('msg.deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    ###################### modify qty ########################33
    public function modifyQuantities(Request $request)
    {
        $reason = $request->reason;
        $created_at = $request->created_at;
        $work_date = $request->work_date;
        $name = $request->name;
        $status = $request->status;
        $code = $request->code;
        $branch_tag = $request->branch_tag;
        $created_by = $request->created_by;
        $send_by = $request->send_by;

        $modify_quantities = ModifyQuantitie::where('vendor_id', $this->vendor_id);
        if ($reason)
            $modify_quantities = $modify_quantities->where('reason_id', $reason);
        if ($status)
            $modify_quantities = $modify_quantities->where('status_id', $status);
        if ($created_at)
            $modify_quantities = $modify_quantities->whereDate('status_id', $created_at);
        if ($work_date)
            $modify_quantities = $modify_quantities->whereDate('status_id', $work_date);
        if ($created_by)
            $modify_quantities = $modify_quantities->where('created_by', 'LIKE', '%' . $created_by . '%');
        if ($send_by)
            $modify_quantities = $modify_quantities->where('send_by', 'LIKE', '%' . $send_by . '%');
        if ($name)
            $modify_quantities = $modify_quantities->whereHas('branch', function ($b) use ($name, $code, $branch_tag) {
                if ($name)
                    $b->where('name_ar', 'LIKE', '%' . $name . '%')->orWhere('name_en', 'LIKE', '%' . $name . '%');
                if ($code)
                    $b->where('code', 'LIKE', '%' . $code . '%');
                if ($branch_tag)
                    $b->whereHas('tags', function ($tag) use ($branch_tag) {
                        $tag->where('name_ar', 'LIKE', '%' . $branch_tag . '%')->orWhere('name_en', 'LIKE', '%' . $branch_tag . '%');
                    });
            });
        $modify_quantities = $modify_quantities->orderBy('id', 'DESC')->get();
        $data = $modify_quantities->map(function ($qty) {
            return [
                'id' => $qty->id,
                'created_at' => date('d/m/Y H:i', strtotime($qty->created_at)),
                'work_date' => date('d/m/Y H:i', strtotime($qty->work_date)),
                'branch' => isset($qty->branch) ? $qty->branch->name($this->lang_code) : '',
                'reason' => isset($qty->reason) ? $qty->reason->name($this->lang_code) : '',
            ];
        });

        $msg = __('msg.modify_quantities_get_success', [], $this->lang_code);
        return $this->dataResponse($msg, $data, 200);
    }

    public function createModifyQuantities(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => 'required',
            'reason_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $modify_qty = new ModifyQuantitie();
        $modify_qty->vendor_id = $this->vendor_id;
        $modify_qty->created_by = $this->vendor_name;
        $modify_qty->branch_id = $request->branch_id;
        $modify_qty->reason_id = $request->reason_id;
        $modify_qty->status_id = 1;
        $modify_qty->save();

        $msg = __('msg.modify_quantities_created_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function modifyQuantityDetails(Request $request)
    {
        $modify_qty = ModifyQuantitie::where('vendor_id', $this->vendor_id)->where('id', $request->modify_qty_id)->first();
        if (!$modify_qty)
            return $this->errorResponse(__('msg.modify_quantities_not_found', [], $this->lang_code), 400);
        $data = [
            'id' => $modify_qty->id,
            'branch_id' => $modify_qty->branch_id,
            'reason_id' => $modify_qty->reason_id,
            'status_id' => $modify_qty->status_id,
            'status_name' => $modify_qty->status_name,
            'created_at' => date('d/m/Y H:i', strtotime($modify_qty->created_at)),
            'work_date' => date('d/m/Y H:i', strtotime($modify_qty->work_date)),
            'branch' => isset($modify_qty->branch) ? $modify_qty->branch->name($this->lang_code) : '',
            'reason' => isset($qty->reason) ? $qty->reason->name($this->lang_code) : '',
            'stock_materials' => isset($modify_qty->stock_materials) && count($modify_qty->stock_materials) > 0 ? $modify_qty->stock_materials->map(function ($mat) {
                return [
                    'id' => $mat->id,
                    'material_id' => isset($mat->stock) ? $mat->stock->id : 0,
                    'stock_name' => isset($mat->stock) ? $mat->stock->name : '',
                    'stock_code' => isset($mat->stock) ? $mat->stock->code : '',
                    'storage_unit' => isset($mat->stock) ? $mat->stock->storage_unit : '',
                    'qty' => $mat->qty,
                ];
            }) : []
        ];

        $msg = __('msg.modify_quantities_get_success', [], $this->lang_code);
        return $this->dataResponse($msg, $data, 200);
    }

    public function updateModifyQuantities(Request $request)
    {
        $modify_qty = ModifyQuantitie::where('vendor_id', $this->vendor_id)->where('id', $request->modify_qty_id)->first();
        if (!$modify_qty)
            return $this->errorResponse(__('msg.modify_quantities_not_found', [], $this->lang_code), 400);

        if ($request->branch_id)
            $modify_qty->branch_id = $request->branch_id;
        if ($request->reason_id)
            $modify_qty->reason_id = $request->reason_id;
        $modify_qty->save();

        $msg = __('msg.modify_quantities_updated_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteModifyQuantities(Request $request)
    {
        $modify_qty = ModifyQuantitie::where('vendor_id', $this->vendor_id)->where('id', $request->modify_qty_id)->first();
        if (!$modify_qty)
            return $this->errorResponse(__('msg.modify_quantities_not_found', [], $this->lang_code), 400);

        $modify_qty->delete();

        $msg = __('msg.modify_quantities_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function executionModifyQuantities(Request $request)
    {
        $modify_qty = ModifyQuantitie::where('vendor_id', $this->vendor_id)->where('id', $request->modify_qty_id)->first();
        if (!$modify_qty)
            return $this->errorResponse(__('msg.modify_quantities_not_found', [], $this->lang_code), 400);
        $modify_qty->status_id = 2;
        $modify_qty->work_date = date('Y-m-d H:i');
        $modify_qty->save();
        $msg = __('msg.modify_quantities_updated_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function addModifyQuantitiesStockMaterial(Request $request)
    {
        $modify_qty = ModifyQuantitie::where('vendor_id', $this->vendor_id)->where('id', $request->modify_qty_id)->first();
        if (!$modify_qty)
            return $this->errorResponse(__('msg.modify_quantities_not_found', [], $this->lang_code), 400);

        if ($request->type = 1 && $request->stock_id) {
            $modify_qty_material = ModifyQuantityStockMaterial::where('modify_quantity_id', $modify_qty->id)
                ->where('stock_material_id', $request->stock_id)->first();
            if (!$modify_qty_material)
                $modify_qty_material = new ModifyQuantityStockMaterial();
            $modify_qty_material->modify_quantity_id = $modify_qty->id;
            $modify_qty_material->stock_material_id = $request->stock_id;
            $modify_qty_material->qty = $request->qty;
            $modify_qty_material->type = 1;
            $modify_qty_material->save();
        } elseif ($request->type = 2 && $request->stock_materials) {
            $stock_materials = json_decode($request->stock_materials);
            foreach ($stock_materials as $material) {
                $modify_qty_material = ModifyQuantityStockMaterial::where('modify_quantity_id', $modify_qty->id)
                    ->where('stock_material_id', $material->stock_material_id)->first();
                if (!$modify_qty_material)
                    $modify_qty_material = new ModifyQuantityStockMaterial();
                $modify_qty_material->modify_quantity_id = $modify_qty->id;
                $modify_qty_material->stock_material_id = $material->stock_material_id;
                $modify_qty_material->qty = $material->qty;
                $modify_qty_material->type = 1;
                $modify_qty_material->save();
            }
        } elseif ($request->type = 3) {
            if (!$request->hasFile('csv_file'))
                return $this->errorResponse(__('msg.please_add_csv_file', [], $this->lang_code), 400);


            $file = $request->csv_file;
            $type = $file->getClientOriginalExtension();
            $myfile = $file->getClientOriginalName();
            $destinationPath = 'uploads/csv_files';
            $file_data = $file->move(public_path($destinationPath), $myfile);

            if ($type <> 'csv')
                return $this->errorResponse(__('msg.only_csv_is_allowed', [], $this->lang_code), 400);
            SimpleExcelReader::create($file_data->getPathName())->getRows()
                ->each(function (array $rowProperties) use ($modify_qty) {
                    $arr_val = array_values($rowProperties)[0];
                    if (isset($arr_val)) {
                        $sku_item = explode(';', $arr_val);
                        $stock_code = $sku_item[0];
                        $stock = Stock::where('code', $stock_code)->first();
                        if ($stock) {
                            $modify_qty_material = ModifyQuantityStockMaterial::where('modify_quantity_id', $modify_qty->id)
                                ->where('stock_material_id', $stock->id)->first();
                            if (!$modify_qty_material)
                                $modify_qty_material = new ModifyQuantityStockMaterial();
                            $modify_qty_material->modify_quantity_id = $modify_qty->id;
                            $modify_qty_material->stock_material_id = $stock->id;
                            $modify_qty_material->qty = isset($sku_item[2]) && $sku_item[2] > 0 ? $sku_item[2] : 1;
                            $modify_qty_material->type = 3;
                            $modify_qty_material->save();
                        }
                    }
                });
            File::delete($file_data->getPathName());
        }

        $msg = __('msg.stocks_created_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function modifyQuantitiesDeleteList(Request $request){
        $validator = Validator::make($request->all(), [
            'modify_quantities' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $modify_quantities = $request->modify_quantities;
        if (!is_array($modify_quantities))
            $modify_quantities = json_decode($modify_quantities);


        ModifyQuantitie::whereIn('id',$modify_quantities)->delete();

        $msg = __('msg.deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    ###########################################################
    ####################### modify cost ########################33
    public function modifyCosts(Request $request)
    {
        $created_at = $request->created_at;
        $work_date = $request->work_date;
        $status = $request->status;
        $code = $request->code;
        $branch_tag = $request->branch_tag;
        $creator = $request->creator;
        $sender = $request->sender;
        $name = $request->name;

        $modify_costs = ModifyCost::where('vendor_id', $this->vendor_id);
        if ($status)
            $modify_costs = $modify_costs->where('status_id', $status);
        if ($created_at)
            $modify_costs = $modify_costs->whereDate('status_id', $created_at);
        if ($work_date)
            $modify_costs = $modify_costs->whereDate('status_id', $work_date);
        if ($creator)
            $modify_costs = $modify_costs->where('created_by', 'LIKE', '%' . $creator . '%');
        if ($sender)
            $modify_costs = $modify_costs->where('send_by', 'LIKE', '%' . $sender . '%');
        if ($name)
            $modify_costs = $modify_costs->whereHas('branch', function ($b) use ($name, $code, $branch_tag) {
                if ($name)
                    $b->where('name_ar', 'LIKE', '%' . $name . '%')->orWhere('name_en', 'LIKE', '%' . $name . '%');
                if ($code)
                    $b->where('code', 'LIKE', '%' . $code . '%');
                if ($branch_tag)
                    $b->whereHas('tags', function ($tag) use ($branch_tag) {
                        $tag->where('name_ar', 'LIKE', '%' . $branch_tag . '%')->orWhere('name_en', 'LIKE', '%' . $branch_tag . '%');
                    });
            });
        $modify_costs = $modify_costs->orderBy('id', 'DESC')->get();
        $data = $modify_costs->map(function ($cost) {
            return [
                'id' => $cost->id,
                'created_at' => date('d/m/Y H:i', strtotime($cost->created_at)),
                'work_date' => date('d/m/Y H:i', strtotime($cost->work_date)),
                'branch' => isset($cost->branch) ? $cost->branch->name($this->lang_code) : '',
                'creator' => $cost->creator,
                'sender' => $cost->sender,
            ];
        });

        $msg = __('msg.modify_costs_get_success', [], $this->lang_code);
        return $this->dataResponse($msg, $data, 200);
    }

    public function createModifyCost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $modify_cost = new ModifyCost();
        $modify_cost->vendor_id = $this->vendor_id;
        $modify_cost->creator = $this->vendor_name;
        $modify_cost->branch_id = $request->branch_id;
        $modify_cost->status_id = 1;
        $modify_cost->save();

        $msg = __('msg.modify_cost_created_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function modifyCostDetails(Request $request)
    {
        $modify_cost = ModifyCost::where('vendor_id', $this->vendor_id)
            ->where('id', $request->modify_cost_id)->first();
        if (!$modify_cost)
            return $this->errorResponse(__('msg.modify_cost_not_found', [], $this->lang_code), 400);
        $data = [
            'id' => $modify_cost->id,
            'branch_id' => $modify_cost->branch_id,
            'status_id' => $modify_cost->status_id,
            'status_name' => $modify_cost->status_name,
            'created_at' => date('d/m/Y H:i', strtotime($modify_cost->created_at)),
            'work_date' => date('d/m/Y H:i', strtotime($modify_cost->work_date)),
            'branch' => isset($modify_cost->branch) ? $modify_cost->branch->name($this->lang_code) : '',
            'creator' => $modify_cost->creator,
            'sender' => $modify_cost->sender,
            'stock_materials' => isset($modify_cost->stock_materials) && count($modify_cost->stock_materials) > 0 ? $modify_cost->stock_materials->map(function ($mat) {
                return [
                    'id' => $mat->id,
                    'material_id' => isset($mat->stock) ? $mat->stock->id : 0,
                    'stock_name' => isset($mat->stock) ? $mat->stock->name : '',
                    'stock_code' => isset($mat->stock) ? $mat->stock->code : '',
                    'storage_unit' => isset($mat->stock) ? $mat->stock->storage_unit : '',
                    'cost' => (float)$mat->cost,
                ];
            }) : []
        ];

        $msg = __('msg.modify_cost_get_success', [], $this->lang_code);
        return $this->dataResponse($msg, $data, 200);
    }

    public function updateModifyCost(Request $request)
    {
        $modify_cost = ModifyCost::where('vendor_id', $this->vendor_id)->where('id', $request->modify_cost_id)->first();
        if (!$modify_cost)
            return $this->errorResponse(__('msg.modify_cost_not_found', [], $this->lang_code), 400);

        if ($request->branch_id)
            $modify_cost->branch_id = $request->branch_id;
        $modify_cost->save();

        $msg = __('msg.modify_cost_updated_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteModifyCost(Request $request)
    {
        $modify_cost = ModifyCost::where('vendor_id', $this->vendor_id)->where('id', $request->modify_cost_id)->first();
        if (!$modify_cost)
            return $this->errorResponse(__('msg.modify_quantities_not_found', [], $this->lang_code), 400);

        $modify_cost->delete();

        $msg = __('msg.modify_cost_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function executionModifyCost(Request $request)
    {
        $modify_cost = ModifyCost::where('vendor_id', $this->vendor_id)->where('id', $request->modify_cost_id)->first();
        if (!$modify_cost)
            return $this->errorResponse(__('msg.modify_cost_not_found', [], $this->lang_code), 400);
        $modify_cost->status_id = 2;
        $modify_cost->work_date = date('Y-m-d H:i');
        $modify_cost->save();
        $msg = __('msg.modify_cost_updated_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function addModifyCostsStockMaterial(Request $request)
    {

        $modify_cost = ModifyCost::where('vendor_id', $this->vendor_id)->where('id', $request->modify_cost_id)->first();
        if (!$modify_cost)
            return $this->errorResponse(__('msg.modify_cost_not_found', [], $this->lang_code), 400);

        if ($request->type = 1 && $request->stock_id) {
            $modify_cost_material = ModifyCostStockMaterial::where('modify_cost_id', $modify_cost->id)
                ->where('stock_material_id', $request->stock_id)->first();
            if (!$modify_cost_material)
                $modify_cost_material = new ModifyCostStockMaterial();
            $modify_cost_material->modify_cost_id = $modify_cost->id;
            $modify_cost_material->stock_material_id = $request->stock_id;
            $modify_cost_material->cost = $request->cost ? $request->cost : 0;
            $modify_cost_material->type = 1;
            $modify_cost_material->save();
        } elseif ($request->type = 2 && $request->stock_materials) {
            $stock_materials = json_decode($request->stock_materials);
            foreach ($stock_materials as $material) {
                $modify_cost_material = ModifyCostStockMaterial::where('modify_cost_id', $modify_cost->id)
                    ->where('stock_material_id', $material->stock_material_id)->first();
                if (!$modify_cost_material)
                    $modify_cost_material = new ModifyCostStockMaterial();
                $modify_cost_material->modify_cost_id = $modify_cost->id;
                $modify_cost_material->stock_material_id = $material->stock_material_id;
                $modify_cost_material->cost = $material->cost;
                $modify_cost_material->type = 1;
                $modify_cost_material->save();
            }
        } elseif ($request->type = 3) {
            if (!$request->hasFile('csv_file'))
                return $this->errorResponse(__('msg.please_add_csv_file', [], $this->lang_code), 400);


            $file = $request->csv_file;
            $type = $file->getClientOriginalExtension();
            $myfile = $file->getClientOriginalName();
            $destinationPath = 'uploads/csv_files';
            $file_data = $file->move(public_path($destinationPath), $myfile);

            if ($type <> 'csv')
                return $this->errorResponse(__('msg.only_csv_is_allowed', [], $this->lang_code), 400);
            SimpleExcelReader::create($file_data->getPathName())->getRows()
                ->each(function (array $rowProperties) use ($modify_cost) {
                    $arr_val = array_values($rowProperties)[0];
                    if (isset($arr_val)) {
                        $sku_item = explode(';', $arr_val);
                        $stock_code = $sku_item[0];
                        $stock = Stock::where('code', $stock_code)->first();
                        if ($stock) {
                            $modify_cost_material = ModifyCostStockMaterial::where('stock_material_id', $modify_cost->id)
                                ->where('stock_material_id', $stock->id)->first();
                            if (!$modify_cost_material)
                                $modify_cost_material = new ModifyCostStockMaterial();
                            $modify_cost_material->stock_material_id = $modify_cost->id;
                            $modify_cost_material->stock_material_id = $stock->id;
                            $modify_cost_material->cost = isset($sku_item[2]) && $sku_item[2] > 0 ? $sku_item[2] : 1;
                            $modify_cost_material->type = 3;
                            $modify_cost_material->save();
                        }
                    }
                });
            File::delete($file_data->getPathName());
        }

        $msg = __('msg.stocks_created_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function modifyCostDeleteList(Request $request){
        $validator = Validator::make($request->all(), [
            'modify_costs' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $modify_costs = $request->modify_costs;
        if (!is_array($modify_costs))
            $modify_costs = json_decode($modify_costs);


        ModifyCost::whereIn('id',$modify_costs)->delete();

        $msg = __('msg.deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    ###########################################################
    ####################### stock trnsfare ##################
    public function stockTransfare(Request $request)
    {
        $code = $request->code;
        $work_date = $request->work_date;
        $created_at = $request->created_at;
        $status_id = $request->status_id;
        $branches = $request->branches;
        $store_house = $request->store_house;
        $created_by_name = $request->created_by_name;
        $transfare_type = $request->transfare_type;

        $store_transfare = StoreTransfare::where('vendor_id', $this->vendor_id);
        if ($code)
            $store_transfare = $store_transfare->where('code', 'LIKE', '%' . $code . '%');
        if ($created_by_name)
            $store_transfare = $store_transfare->where('created_by_name', 'LIKE', '%' . $created_by_name . '%');
        if ($work_date)
            $store_transfare = $store_transfare->whereDate('work_date', $work_date);
        if ($created_at)
            $store_transfare = $store_transfare->whereDate('created_at', $created_at);
        if ($transfare_type)
            $store_transfare = $store_transfare->where('transfare_type', $transfare_type);
        if ($status_id)
            $store_transfare = $store_transfare->where('status_id', $status_id);
        if ($branches)
            $store_transfare = $store_transfare->whereIn('to_branch_id', $branches);
        if ($store_house)
            $store_transfare = $store_transfare->where('to_store_house_id', $store_house);

        $store_transfare = $store_transfare->orderBy('id', 'DESC')->get();

        $data = $store_transfare->map(function ($transfare) {
            return [
                'id' => $transfare->id,
                'from_branch' => isset($transfare->from_branch) && $transfare->from_branch->name($this->lang_code) ? $transfare->from_branch->name($this->lang_code) : '',
                'to_branch' => isset($transfare->to_branch) && $transfare->to_branch->name($this->lang_code) ? $transfare->to_branch->name($this->lang_code) : '',
                'from_store_house' => isset($transfare->from_store_house) && $transfare->from_store_house->name($this->lang_code) ? $transfare->from_store_house->name($this->lang_code) : '',
                'to_store_house' => isset($transfare->to_store_house) && $transfare->to_store_house->name($this->lang_code) ? $transfare->to_store_house->name($this->lang_code) : '',
                'code' => $transfare->code,
                'transfare_type' => $transfare->transfare_type,
                'status_name' => $transfare->status_name,
                'status_id' => $transfare->status_id,
                'work_date' => $transfare->work_date ? date('d/m/Y H:i', strtotime($transfare->work_date)) : '',
                'created_at' => $transfare->created_at ? date('d/m/Y H:i', strtotime($transfare->created_at)) : '',
            ];
        });

        $msg = __('msg.store_transfare_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $data, 200);
    }

    public function generateStockTransfareCode()
    {
        $last_item_id = 0;
        $last_item = StoreTransfare::where('vendor_id', $this->vendor_id)->orderBy('id', 'DESC')->first();
        if ($last_item) {
            $num = explode('-', $last_item->number);
            $last_item_id = $num[1];
        }
        $data = [
            'code' => 'TRA-' . str_pad($last_item_id + 1, 5, "0", STR_PAD_LEFT)
        ];
        return $this->dataResponse('generated successfully', $data);
    }

    public function createStockTransfare(Request $request)
    {
        $last_item_id = 0;
        $last_item = StoreTransfare::where('vendor_id', $this->vendor_id)->orderBy('id', 'DESC')->first();
        if ($last_item) {
            $num = explode('-', $last_item->number);
            $last_item_id = $num[1];
        }

        $stock_transfare = new StoreTransfare();
        $stock_transfare->vendor_id = $this->vendor_id;
        $stock_transfare->status_id = 1;
        $stock_transfare->from_branch_id = $request->from_branch_id ? $request->from_branch_id : 0;
        $stock_transfare->to_branch_id = $request->to_branch_id ? $request->to_branch_id : 0;
        $stock_transfare->from_store_house_id = $request->from_store_house_id ? $request->from_store_house_id : 0;
        $stock_transfare->to_store_house_id = $request->to_store_house_id ? $request->to_store_house_id : 0;
        $stock_transfare->notes = $request->notes;
        $stock_transfare->transfare_type = $request->transfare_type;
        $stock_transfare->extra_price_name = $request->extra_price_name;
        $stock_transfare->extra_price = $request->extra_price;
        $stock_transfare->created_by_name = $this->vendor_name;
        $stock_transfare->code = 'TRA-' . str_pad($last_item_id + 1, 5, "0", STR_PAD_LEFT);
        $stock_transfare->save();

        $msg = __('msg.store_transfare_created_success', [], $this->lang_code);
        return $this->successResponse($msg);
    }

    public function updateStockTransfare(Request $request)
    {

        $stock_transfare = StoreTransfare::where('vendor_id', $this->vendor_id)->where('id', $request->stock_transfare_id)->first();
        if (!$stock_transfare)
            return $this->errorResponse(__('msg.store_transfare_not_found', [], $this->lang_code), 400);
        if ($request->from_branch_id)
            $stock_transfare->from_branch_id = $request->from_branch_id;
        if ($request->to_branch_id)
            $stock_transfare->to_branch_id = $request->to_branch_id;
        if ($request->from_store_house_id)
            $stock_transfare->from_store_house_id = $request->from_store_house_id;
        if ($request->to_store_house_id)
            $stock_transfare->to_store_house_id = $request->to_store_house_id;
        if ($request->status_id)
            $stock_transfare->status_id = $request->status_id;
        if ($request->notes)
            $stock_transfare->notes = $request->notes;
        if ($request->extra_price_name)
            $stock_transfare->extra_price_name = $request->extra_price_name;
        if ($request->extra_price)
            $stock_transfare->extra_price = $request->extra_price;
        $stock_transfare->save();

        $msg = __('msg.store_transfare_updated_success', [], $this->lang_code);
        return $this->successResponse($msg);
    }

    public function deleteStockTransfare(Request $request)
    {

        $stock_transfare = StoreTransfare::where('vendor_id', $this->vendor_id)->where('id', $request->stock_transfare_id)->first();
        if (!$stock_transfare)
            return $this->errorResponse(__('msg.store_transfare_not_found', [], $this->lang_code), 400);

        $stock_transfare->delete();

        $msg = __('msg.store_transfare_deleted_success', [], $this->lang_code);
        return $this->successResponse($msg);
    }

    public function addStockTransfareStocks(Request $request)
    {
        $stock_transfare = StoreTransfare::where('vendor_id', $this->vendor_id)->where('id', $request->stock_transfare_id)->first();
        if (!$stock_transfare)
            return $this->errorResponse(__('msg.store_transfare_not_found', [], $this->lang_code), 400);

        if ($request->type = 1 && $request->stock_materials) {
            $stock_materials = $request->stock_materials;
            if (!is_array($request->stock_materials))
                $stock_materials = json_decode($request->stock_materials);
            foreach ($stock_materials as $material) {
                $stock_transfare_material = StoreTransfareStock::where('store_transfare_id', $stock_transfare->id)
                    ->where('stock_id', $material)->first();
                if (!$stock_transfare_material)
                    $stock_transfare_material = new StoreTransfareStock();
                $stock_transfare_material->store_transfare_id = $stock_transfare->id;
                $stock_transfare_material->stock_id = $material;
                $stock_transfare_material->type = 1;
                $stock_transfare_material->qty = 1;
                $stock_transfare_material->save();
            }
        } elseif ($request->type = 2 && $request->stock_materials_tags) {
            $stock_materials_tags = $request->stock_materials_tags;
            if (!is_array($request->stock_materials_tags))
                $stock_materials_tags = json_decode($request->stock_materials_tags);
            foreach ($stock_materials_tags as $tag) {
                $stock_material = Stock::where('vendor_id', $this->vendor_id)->whereHas('tags', function ($q) use ($tag) {
                    $q->where('name_ar', 'LIKE', '%' . $tag . '%')->orWhere('name_en', 'LIKE', '%' . $tag . '%');
                })->first();
                if ($stock_material) {
                    $stock_transfare_material = StoreTransfareStock::where('store_transfare_id', $stock_transfare->id)
                        ->where('stock_id', $stock_material->id)->first();
                    if (!$stock_transfare_material)
                        $stock_transfare_material = new StoreTransfareStock();
                    $stock_transfare_material->store_transfare_id = $stock_transfare->id;
                    $stock_transfare_material->stock_id = $stock_material->id;
                    $stock_transfare_material->type = 2;
                    $stock_transfare_material->qty = 1;
                    $stock_transfare_material->save();
                }
            }
        } elseif ($request->type = 3) {
            if (!$request->hasFile('csv_file'))
                return $this->errorResponse(__('msg.please_add_csv_file', [], $this->lang_code), 400);

            $file = $request->csv_file;
            $type = $file->getClientOriginalExtension();
            $myfile = $file->getClientOriginalName();
            $destinationPath = 'uploads/csv_files';
            $file_data = $file->move(public_path($destinationPath), $myfile);

            if ($type <> 'csv')
                return $this->errorResponse(__('msg.only_csv_is_allowed', [], $this->lang_code), 400);
            SimpleExcelReader::create($file_data->getPathName())->getRows()
                ->each(function (array $rowProperties) use ($stock_transfare) {
                    $arr_val = array_values($rowProperties)[0];
                    if (isset($arr_val)) {
                        $sku_item = explode(';', $arr_val);
                        $stock_code = $sku_item[0];
                        $stock = Stock::where('code', $stock_code)->first();
                        if ($stock) {

                            $stock_transfare_material = StoreTransfareStock::where('store_transfare_id', $stock_transfare->id)
                                ->where('stock_id', $stock->id)->first();
                            if (!$stock_transfare_material)
                                $stock_transfare_material = new StoreTransfareStock();
                            $stock_transfare_material->store_transfare_id = $stock_transfare->id;
                            $stock_transfare_material->stock_id = $stock->id;
                            $stock_transfare_material->type = 3;
                            $stock_transfare_material->qty = isset($sku_item[2]) && $sku_item[2] > 0 ? $sku_item[2] : 1;
                            $stock_transfare_material->save();
                        }
                    }
                });
            File::delete($file_data->getPathName());
        }

        $msg = __('msg.stocks_created_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function updateStoreTransfareStock(Request $request)
    {

        $stock_transfare_material = StoreTransfareStock::where('store_transfare_id', $request->store_transfare_id)
            ->where('stock_id', $request->stock_id)->first();
        if (!$stock_transfare_material)
            return $this->errorResponse(__('msg.store_transfare_not_found', [], $this->lang_code), 400);
        $stock_transfare_material->qty = $request->qty;
        $stock_transfare_material->save();

        $msg = __('msg.stocks_updated_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function storeTransfareDetails(Request $request)
    {
        $transfare = StoreTransfare::where('vendor_id', $this->vendor_id)->where('id', $request->stock_transfare_id)->first();
        if (!$transfare)
            return $this->errorResponse(__('msg.store_transfare_not_found', [], $this->lang_code), 400);

        $data = [
            'id' => $transfare->id,
            'from_branch' => isset($transfare->from_branch) && $transfare->from_branch->name($this->lang_code) ? $transfare->from_branch->name($this->lang_code) : '',
            'to_branch' => isset($transfare->to_branch) && $transfare->to_branch->name($this->lang_code) ? $transfare->to_branch->name($this->lang_code) : '',
            'from_store_house' => isset($transfare->from_store_house) && $transfare->from_store_house->name($this->lang_code) ? $transfare->from_store_house->name($this->lang_code) : '',
            'to_store_house' => isset($transfare->to_store_house) && $transfare->to_store_house->name($this->lang_code) ? $transfare->to_store_house->name($this->lang_code) : '',
            'code' => $transfare->code,
            'from_branch_id' => $transfare->from_branch_id,
            'to_branch_id' => $transfare->to_branch_id,
            'from_store_house_id' => $transfare->from_store_house_id,
            'to_store_house_id' => $transfare->to_store_house_id,
            'status_name' => $transfare->status_name,
            'status_id' => $transfare->status_id,
            'sender' => $transfare->sender,
            'notes' => $transfare->notes,
            'extra_price' => $transfare->extra_price,
            'extra_price_name' => $transfare->extra_price_name,
            'work_date' => $transfare->work_date ? date('d/m/Y H:i', strtotime($transfare->work_date)) : '',
            'created_at' => $transfare->created_at ? date('d/m/Y H:i', strtotime($transfare->created_at)) : '',
            'stocks' => isset($transfare->stocks) && count($transfare->stocks) > 0 ? $transfare->stocks->map(function ($item) {
                return [
                    'stock_id' => $item->stock_id,
                    'store_transfare_id' => $item->store_transfare_id,
                    'qty' => $item->qty,
                    'total_warehouse' => isset($item->stock) && $item->stock->total_warehouse > 0 ? $item->stock->total_warehouse : 0,
                    'stock_name' => isset($item->stock) && $item->stock->name ? $item->stock->name : '',
                    'stock_code' => isset($item->stock) && $item->stock->code ? $item->stock->code : '',
                    'stock_amount' => isset($item->stock) && $item->stock->amount ? $item->stock->amount : 0,
                ];
            }) : []
        ];

        $msg = __('msg.store_transfare_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $data, 200);
    }
    public function storeTransfareDeleteList(Request $request){
        $validator = Validator::make($request->all(), [
            'store_transfare_list' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $store_transfare_list = $request->store_transfare_list;
        if (!is_array($store_transfare_list))
            $store_transfare_list = json_decode($store_transfare_list);


        StoreTransfare::whereIn('id',$store_transfare_list)->delete();

        $msg = __('msg.deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    ##################### stock_checks #############################

    public function stockChecks(Request $request)
    {
        $code = $request->code;
        $work_date = $request->work_date;
        $created_at = $request->created_at;
        $status_id = $request->status_id;
        $branches = $request->branches;

        $stock_checks = StockCheck::where('vendor_id', $this->vendor_id);
        if ($code)
            $stock_checks = $stock_checks->where('code', 'LIKE', '%' . $code . '%');
        if ($work_date)
            $stock_checks = $stock_checks->whereDate('work_date', $work_date);
        if ($created_at)
            $stock_checks = $stock_checks->whereDate('created_at', $created_at);
        if ($status_id)
            $stock_checks = $stock_checks->where('status_id', $status_id);
        if ($branches)
            $stock_checks = $stock_checks->whereIn('branch_id', $branches);
        $stock_checks = $stock_checks->orderBy('id', 'DESC')->get();

        $data = $stock_checks->map(function ($item) {
            return [
                'id' => $item->id,
                'code' => $item->code,
                'branch_id' => $item->branch_id,
                'branch_name' => $item->branch->name($this->lang_code),
                'work_date' => date('d/m/Y H:i', strtotime($item->work_date)),
                'created_at' => date('d/m/Y H:i', strtotime($item->created_at)),
            ];
        });

        $msg = __('msg.stock_checks_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $data, 200);
    }

    public function generateStockCheckCode()
    {
        $last_item_id = 0;
        $last_item = StockCheck::where('vendor_id', $this->vendor_id)->orderBy('id', 'DESC')->first();
        if ($last_item) {
            $num = explode('-', $last_item->number);
            $last_item_id = $num[1];
        }
        $data = [
            'code' => 'SQC-' . str_pad($last_item_id + 1, 5, "0", STR_PAD_LEFT)
        ];
        return $this->dataResponse('generated successfully', $data);
    }

    public function createStockCheck(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $last_item_id = 0;
        $last_item = StockCheck::where('vendor_id', $this->vendor_id)->orderBy('id', 'DESC')->first();
        if ($last_item) {
            $num = explode('-', $last_item->number);
            $last_item_id = $num[1];
        }

        $stock_check = new StockCheck();
        $stock_check->vendor_id = $this->vendor_id;
        $stock_check->branch_id = $request->branch_id;
        $stock_check->status_id = 1;
        $stock_check->creator = $this->vendor_name;
        $stock_check->code = 'SQC-' . str_pad($last_item_id + 1, 5, "0", STR_PAD_LEFT);
        $stock_check->save();
        $msg = __('msg.stock_checks_created_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function updateStockCheck(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $stock_check = StockCheck::where('vendor_id', $this->vendor_id)->where('id', $request->stock_check_id)->first();
        if (!$stock_check)
            return $this->errorResponse(__('msg.stock_check_not_found', [], $this->lang_code), 400);

        if ($request->branch_id)
            $stock_check->branch_id = $request->branch_id;
        if ($request->status_id)
            $stock_check->status_id = $request->status_id;
        if ($request->amount)
            $stock_check->amount = $request->amount;
        $stock_check->save();
        $msg = __('msg.stock_checks_updated_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function sendStockCheck(Request $request)
    {
        $stock_check = StockCheck::where('vendor_id', $this->vendor_id)->where('id', $request->stock_check_id)->first();
        if (!$stock_check)
            return $this->errorResponse(__('msg.stock_check_not_found', [], $this->lang_code), 400);

        $stock_check->status_id = 2;
        $stock_check->sender = $this->vendor_name;
        $stock_check->sender_date = date('Y-m-d H:i');
        $stock_check->save();

        $msg = __('msg.stock_checks_send_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteStockCheck(Request $request)
    {
        $stock_check = StockCheck::where('vendor_id', $this->vendor_id)->where('id', $request->stock_check_id)->first();
        if (!$stock_check)
            return $this->errorResponse(__('msg.stock_check_not_found', [], $this->lang_code), 400);

        $stock_check->delete();

        $msg = __('msg.stock_checks_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function addStockCheckStocks(Request $request)
    {
        $stock_check = StockCheck::where('vendor_id', $this->vendor_id)->where('id', $request->stock_check_id)->first();
        if (!$stock_check)
            return $this->errorResponse(__('msg.stock_check_not_found', [], $this->lang_code), 400);


        if ($request->type = 1 && $request->stock_materials) {
            $stock_materials = $request->stock_materials;
            if (!is_array($request->stock_materials))
                $stock_materials = json_decode($request->stock_materials);
            foreach ($stock_materials as $material) {
                $stock_check_material = StockCheckStock::where('stock_check_id', $stock_check->id)
                    ->where('stock_id', $material)->first();
                if (!$stock_check_material)
                    $stock_check_material = new StockCheckStock();
                $stock_check_material->stock_check_id = $stock_check->id;
                $stock_check_material->stock_id = $material;
                $stock_check_material->type = 1;
                $stock_check_material->qty = 1;
                $stock_check_material->save();
            }
        } elseif ($request->type = 2 && $request->stock_materials_tags) {
            $stock_materials_tags = $request->stock_materials_tags;
            if (!is_array($request->stock_materials_tags))
                $stock_materials_tags = json_decode($request->stock_materials_tags);
            foreach ($stock_materials_tags as $tag) {
                $stock_material = Stock::where('vendor_id', $this->vendor_id)->whereHas('tags', function ($q) use ($tag) {
                    $q->where('name_ar', 'LIKE', '%' . $tag . '%')->orWhere('name_en', 'LIKE', '%' . $tag . '%');
                })->first();
                if ($stock_material) {
                    $stock_check_material = StockCheckStock::where('stock_check_id', $stock_check->id)
                        ->where('stock_id', $stock_material->id)->first();
                    if (!$stock_check_material)
                        $stock_check_material = new StockCheckStock();
                    $stock_check_material->store_transfare_id = $stock_check->id;
                    $stock_check_material->stock_id = $stock_material->id;
                    $stock_check_material->type = 2;
                    $stock_check_material->qty = 1;
                    $stock_check_material->save();
                }
            }
        } elseif ($request->type = 3) {
            if (!$request->hasFile('csv_file'))
                return $this->errorResponse(__('msg.please_add_csv_file', [], $this->lang_code), 400);

            $file = $request->csv_file;
            $type = $file->getClientOriginalExtension();
            $myfile = $file->getClientOriginalName();
            $destinationPath = 'uploads/csv_files';
            $file_data = $file->move(public_path($destinationPath), $myfile);

            if ($type <> 'csv')
                return $this->errorResponse(__('msg.only_csv_is_allowed', [], $this->lang_code), 400);
            SimpleExcelReader::create($file_data->getPathName())->getRows()
                ->each(function (array $rowProperties) use ($stock_check) {
                    $arr_val = array_values($rowProperties)[0];
                    if (isset($arr_val)) {
                        $sku_item = explode(';', $arr_val);
                        $stock_code = $sku_item[0];
                        $stock = Stock::where('code', $stock_code)->first();
                        if ($stock) {

                            $stock_check_material = StockCheckStock::where('stock_check_id', $stock_check->id)
                                ->where('stock_id', $stock->id)->first();
                            if (!$stock_check_material)
                                $stock_check_material = new StockCheckStock();
                            $stock_check_material->stock_check_id = $stock_check->id;
                            $stock_check_material->stock_id = $stock->id;
                            $stock_check_material->type = 3;
                            $stock_check_material->qty = isset($sku_item[2]) && $sku_item[2] > 0 ? $sku_item[2] : 1;
                            $stock_check_material->save();
                        }
                    }
                });
            File::delete($file_data->getPathName());
        }

        $msg = __('msg.stocks_created_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function updateStockCheckStock(Request $request){
        $stock_check_material = StockCheckStock::where('stock_check_id', $request->stock_check_id)
            ->where('stock_id', $request->stock_id)->first();
        if (!$stock_check_material)
            return $this->errorResponse(__('msg.store_check_not_found', [], $this->lang_code), 400);
        $stock_check_material->qty = $request->qty;
        $stock_check_material->save();

        $msg = __('msg.stocks_updated_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function stockCheckDetails(Request $request)
    {
        $stock_check = StockCheck::where('vendor_id', $this->vendor_id)->where('id', $request->stock_check_id)->first();
        if (!$stock_check)
            return $this->errorResponse(__('msg.stock_check_not_found', [], $this->lang_code), 400);

        $data = [
            'id' => $stock_check->id,
            'code' => $stock_check->code,
            'sender' => $stock_check->sender,
            'creator' => $stock_check->creator,
            'amount' => $stock_check->amount,
            'branch_id' => $stock_check->branch_id,
            'branch_name' => $stock_check->branch->name($this->lang_code) ?? '',
            'work_date' => date('d/m/Y H:i', strtotime($stock_check->work_date)),
            'created_at' => date('d/m/Y H:i', strtotime($stock_check->created_at)),
            'sender_date' => date('d/m/Y H:i', strtotime($stock_check->sender_date)),
            'stocks' => isset($stock_check->stocks) && count($stock_check->stocks) > 0 ? $stock_check->stocks->map(function ($item) {
                return [
                    'stock_id' => $item->stock_id,
                    'stock_check_id' => $item->stock_check_id,
                    'qty' => $item->qty,
                    'total_warehouse' => isset($item->stock) && $item->stock->total_warehouse > 0 ? $item->stock->total_warehouse : 0,
                    'stock_name' => isset($item->stock) && $item->stock->name ? $item->stock->name : '',
                    'stock_code' => isset($item->stock) && $item->stock->code ? $item->stock->code : '',
                    'stock_amount' => isset($item->stock) && $item->stock->amount ? $item->stock->amount : 0,
                ];
            }) : []
        ];

        $msg = __('msg.stock_checks_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $data, 200);
    }
    public function stockCheckDeleteList(Request $request){
        $validator = Validator::make($request->all(), [
            'stock_check' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $stock_check = $request->stock_check;
        if (!is_array($stock_check))
            $stock_check = json_decode($stock_check);

        StockCheck::whereIn('id',$stock_check)->delete();


        $msg = __('msg.deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
}
