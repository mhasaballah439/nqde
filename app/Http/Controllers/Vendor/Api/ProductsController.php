<?php

namespace App\Http\Controllers\Vendor\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Traits\ApiTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductsController extends Controller
{
    use ApiTrait;
    var $lang_code;
    var $vendor_id;
    public function __construct()
    {
        $this->lang_code = \request()->get('lang') ? \request()->get('lang') : get_default_languages();
        if (auth()->guard('vendor')->check())
            $vendor_id = vendor()->id;
        elseif (auth()->guard('vendor_employee')->check())
            $vendor_id = vendor_employee()->vendor->id;
    }
    ################### categories area #####################
    public function allCategories(Request $request)
    {
        $categories = ProductCategory::where('vendor_id',$this->vendor_id)->Active();
        $filter_name = $request->get('filter_name');
        $filter_is_delete = $request->get('is_delete');
        $filter_operation_number = $request->get('operation_number');
        $filter_created_at = $request->get('created_at');
        if ($filter_name)
            $categories = $categories->where('name_ar', 'LIKE', '%' . $filter_name . '%')->orWhere('name_en', 'LIKE', '%' . $filter_name . '%');
        if ($filter_is_delete == 0)
            $categories = $categories->whereNull('deleted_at');
        if ($filter_operation_number)
            $categories = $categories->where('operation_number', 'LIKE', '%' . $filter_operation_number . '%');
        if ($filter_created_at)
            $categories = $categories->whereDate('created_at', $filter_created_at);

        $categories = $categories->withTrashed()->orderBy('sort')->get();
        $data = $categories->map(function ($item) {
            return [
                'id' => $item->id,
                'sort' => $item->sort ? $item->sort : 0,
                'status' => $item->status,
                'operation_number' => $item->operation_number,
                'is_delete' => $item->deleted_at ? 1 : 0,
                'name' => $item->name($this->lang_code),
                'products_count' => isset($item->products) && count($item->products) > 0 ? count($item->products) : 0
            ];
        });
        return $this->dataResponse(__('msg.data_success_get',[],$this->lang_code),$data);
    }

    public function generateCategoryCode(){
         $last_item_id = 0;
        $last_item = ProductCategory::where('vendor_id',$this->vendor_id)->withTrashed()->orderBy('id','DESC')->first();
         if($last_item){
             $num = explode('-',$last_item->operation_number);
             $last_item_id = $num[1];
         }

        $data = [
            'operation_number' => 'C-'.($last_item_id + 1)
        ];
        return $this->dataResponse('generated successfully',$data);
    }
    public function trashedCategories(Request $request)
    {
        $categories = ProductCategory::where('vendor_id',$this->vendor_id)->Active();
        $filter_name = $request->get('filter_name');
        $filter_operation_number = $request->get('operation_number');
        $filter_created_at = $request->get('created_at');
        if ($filter_name)
            $categories = $categories->where('name_ar', 'LIKE', '%' . $filter_name . '%')->orWhere('name_en', 'LIKE', '%' . $filter_name . '%');
        if ($filter_operation_number)
            $categories = $categories->where('operation_number', 'LIKE', '%' . $filter_operation_number . '%');
        if ($filter_created_at)
            $categories = $categories->whereDate('created_at', $filter_created_at);

        $categories = $categories->onlyTrashed()->orderBy('sort')->get();
        $data = $categories->map(function ($item) {
            return [
                'id' => $item->id,
                'sort' => $item->sort ? $item->sort : 0,
                'operation_number' => $item->operation_number,
                'name' => $item->name($this->lang_code),
                'status' => $item->status,
                'products_count' => isset($item->products) && count($item->products) > 0 ? count($item->products) : 0
            ];
        });
        return $this->dataResponse(__('msg.data_success_get',[],$this->lang_code),$data);
    }

    public function createCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $last_item_id = 0;
        $last_item = ProductCategory::where('vendor_id',$this->vendor_id)->withTrashed()->orderBy('id','DESC')->first();
        if($last_item){
            $num = explode('-',$last_item->operation_number);
            $last_item_id = $num[1];
        }
        $category = new ProductCategory();
        $category->vendor_id = $this->vendor_id;
        $category->name_ar = $request->get('name_ar');
        $category->name_en = $request->get('name_en');
        $category->status = $request->get('status');
        $category->operation_number = 'C-'.($last_item_id + 1);
        $category->save();

        return $this->dataResponse(__('msg.cat_created_success',[],$this->lang_code),$category);
    }

    public function updateCategory(Request $request)
    {

        $category = ProductCategory::where('id', $request->get('category_id'))->where('vendor_id', $this->vendor_id)->first();
        if (!$category)
            return $this->errorResponse(__('msg.cat_not_found',[],$this->lang_code),400);

        if ($request->get('name_ar'))
            $category->name_ar = $request->get('name_ar');
        if ($request->get('name_en'))
            $category->name_en = $request->get('name_en');
        $category->status = (integer)$request->get('status');
        $category->save();
        return $this->dataResponse(__('msg.cat_updated_success',[],$this->lang_code),$category);
    }

    public function deleteCategory(Request $request)
    {
        $category = ProductCategory::where('id', $request->get('category_id'))->where('vendor_id',$this->vendor_id)->first();
        if (!$category)
            return $this->errorResponse(__('msg.cat_not_found',[],$this->lang_code),400);

        $category->delete();

        return $this->successResponse(__('msg.cat_deleted_success',[],$this->lang_code));
    }

    public function sortCategories(Request $request)
    {
        $categories_list = $request->get('categories_list');
        if ($categories_list) {
            $list = json_decode($categories_list);
            foreach ($list as $item)
                ProductCategory::where('id', $item->category_id)->update(['sort' => $item->sort]);
        }
        return $this->successResponse(__('msg.cat_sorted_success',[],$this->lang_code));

    }
    ###########################################################
    #################### Products #############################
    public function products(Request $request){
        $name = $request->get('name');
        $code = $request->get('code');
    }
}
