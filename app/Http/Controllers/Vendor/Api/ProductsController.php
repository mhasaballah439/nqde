<?php

namespace App\Http\Controllers\Vendor\Api;

use App\Http\Controllers\Controller;
use App\Models\Addition;
use App\Models\AdditionOption;
use App\Models\AdditionOptionsSpecialBranchPrice;
use App\Models\AdditionOptionsStocks;
use App\Models\GiftCardBranches;
use App\Models\GiftCardTags;
use App\Models\Product;
use App\Models\ProductAddition;
use App\Models\ProductBranch;
use App\Models\ProductCategory;
use App\Models\ProductCollection;
use App\Models\ProductCollectionBranch;
use App\Models\ProductCollectionProducts;
use App\Models\ProductCollectionTag;
use App\Models\ProductComponent;
use App\Models\ProductTag;
use App\Models\ProductTrait;
use App\Models\VendorGiftCard;
use App\Traits\ApiTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductsController extends Controller
{
    use ApiTrait;

    var $lang_code;
    var $vendor_id;

    public function __construct()
    {
        $this->lang_code = \request()->get('lang') ? \request()->get('lang') : get_default_languages();
        if (auth()->guard('vendor')->check())
            $this->vendor_id = vendor()->id;
        elseif (auth()->guard('vendor_employee')->check())
            $this->vendor_id = vendor_employee()->vendor->id;
    }

    ################### categories area #####################
    public function allCategories(Request $request)
    {
        $categories = ProductCategory::where('vendor_id', $this->vendor_id)->Active();
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
        return $this->dataResponse(__('msg.data_success_get', [], $this->lang_code), $data);
    }

    public function generateCategoryCode()
    {
        $last_item_id = 0;
        $last_item = ProductCategory::where('vendor_id', $this->vendor_id)->withTrashed()->orderBy('id', 'DESC')->first();
        if ($last_item) {
            $num = explode('-', $last_item->operation_number);
            $last_item_id = $num[1];
        }

        $data = [
            'operation_number' => 'C-' . ($last_item_id + 1)
        ];
        return $this->dataResponse('generated successfully', $data);
    }

    public function trashedCategories(Request $request)
    {
        $categories = ProductCategory::where('vendor_id', $this->vendor_id)->Active();
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
        return $this->dataResponse(__('msg.data_success_get', [], $this->lang_code), $data);
    }

    public function createCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $last_item_id = 0;
        $last_item = ProductCategory::where('vendor_id', $this->vendor_id)->withTrashed()->orderBy('id', 'DESC')->first();
        if ($last_item) {
            $num = explode('-', $last_item->operation_number);
            $last_item_id = $num[1];
        }
        $category = new ProductCategory();
        $category->vendor_id = $this->vendor_id;
        $category->name_ar = $request->get('name_ar');
        $category->name_en = $request->get('name_en');
        $category->status = $request->get('status');
        $category->operation_number = 'C-' . ($last_item_id + 1);
        $category->save();

        return $this->dataResponse(__('msg.cat_created_success', [], $this->lang_code), $category);
    }

    public function updateCategory(Request $request)
    {

        $category = ProductCategory::where('id', $request->get('category_id'))->where('vendor_id', $this->vendor_id)->first();
        if (!$category)
            return $this->errorResponse(__('msg.cat_not_found', [], $this->lang_code), 400);

        if ($request->get('name_ar'))
            $category->name_ar = $request->get('name_ar');
        if ($request->get('name_en'))
            $category->name_en = $request->get('name_en');
        $category->status = (integer)$request->get('status');
        $category->save();
        return $this->dataResponse(__('msg.cat_updated_success', [], $this->lang_code), $category);
    }

    public function deleteCategory(Request $request)
    {
        $category = ProductCategory::where('id', $request->get('category_id'))->where('vendor_id', $this->vendor_id)->first();
        if (!$category)
            return $this->errorResponse(__('msg.cat_not_found', [], $this->lang_code), 400);

        $category->delete();

        return $this->successResponse(__('msg.cat_deleted_success', [], $this->lang_code));
    }

    public function sortCategories(Request $request)
    {
        $categories_list = $request->get('categories_list');
        if ($categories_list) {
            $list = json_decode($categories_list);
            foreach ($list as $item)
                ProductCategory::where('id', $item->category_id)->update(['sort' => $item->sort]);
        }
        return $this->successResponse(__('msg.cat_sorted_success', [], $this->lang_code));

    }
    ###########################################################
    #################### Products #############################
    public function products(Request $request)
    {
        $name = $request->name;
        $code = $request->code;
        $barcode = $request->barcode;
        $categories = $request->categories;
        $additions = $request->additions;
        $tax_group = $request->tax_group;
        $cost_calculation_method = $request->cost_calculation_method;
        $active = $request->active;
        $retail_product = $request->retail_product;
        $is_delete = $request->is_delete;
        $created_at = $request->created_at;

        $products = Product::where('vendor_id', $this->vendor_id);
        if ($name)
            $products = $products->where('name_ar', 'LIKE', '%' . $name . '%')->orWhere('name_en', 'LIKE', '%' . $name . '%');
        if ($code)
            $products = $products->where('code', 'LIKE', '%' . $code . '%');
        if ($barcode)
            $products = $products->where('barcode', 'LIKE', '%' . $barcode . '%');
        if ($categories && is_array($categories))
            $products = $products->whereHas('category', function ($cat) use ($categories) {
                $cat->whereIn('id', $categories);
            });
        if ($additions && is_array($additions))
            $products = $products->whereHas('additions', function ($cat) use ($additions) {
                $cat->whereIn('addition_id', $additions);
            });
        if ($tax_group)
            $products = $products->where('tax_group_id', $tax_group);
        if ($cost_calculation_method)
            $products = $products->where('cost_calculation_method', $cost_calculation_method);
        if ($active)
            $products = $products->where('active', $active);
        if ($retail_product)
            $products = $products->where('retail_product', $retail_product);
        if ($created_at)
            $products = $products->whereDate('created_at', $created_at);
        if ($is_delete == 1)
            $products = $products->withTrashed();

        $products = $products->orderBy('id', 'DESC')->get();

        $data = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name($this->lang_code),
                'image' => isset($product->image) ? asset('public' . $product->image->file_path) : '',
                'code' => $product->code,
                'category' => isset($product->category) && $product->category->name($this->lang_code) ? $product->category->name($this->lang_code) : '',
                'tax_group' => isset($product->tax_group) && $product->tax_group->name($this->lang_code) ? $product->tax_group->name($this->lang_code) : '',
                'price' => $product->price,
                'active' => $product->active,
            ];
        });
        $msg = __('msg.product_get_success', [], $this->lang_code);
        return $this->dataResponse($msg, $data);
    }

    public function generateProductCode()
    {
        $last_item_id = 0;
        $last_item = Product::withTrashed()->where('vendor_id', $this->vendor_id)->orderBy('id', 'DESC')->first();
        if ($last_item) {
            $num = explode('-', $last_item->code);
            $last_item_id = $num[1];
        }
        $data = [
            'code' => 'P-' . str_pad($last_item_id + 1, 5, "0", STR_PAD_LEFT)
        ];
        return $this->dataResponse('generated successfully', $data);
    }

    public function createProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $last_item_id = 0;
        $last_item = Product::withTrashed()->where('vendor_id', $this->vendor_id)->orderBy('id', 'DESC')->first();
        if ($last_item) {
            $num = explode('-', $last_item->code);
            $last_item_id = $num[1];
        }

        $product = new Product();
        $product->vendor_id = $this->vendor_id;
        $product->code = 'P-' . str_pad($last_item_id + 1, 5, "0", STR_PAD_LEFT);
        $product->category_id = $request->category_id;
        $product->name_ar = $request->name_ar;
        $product->cost_calculation_method = $request->cost_calculation_method;
        $product->price = $request->price;
        $product->pricing_method = $request->pricing_method;
        $product->tax_group_id = $request->tax_group_id;
        $product->retail_product = $request->retail_product;
        $product->type_sell = $request->type_sell;
        $product->barcode = $request->barcode;
        $product->preparation_time = $request->preparation_time;
        $product->calories = $request->calories;
        $product->number_people = $request->number_people;
        $product->description_ar = $request->description_ar;
        $product->active = 1;
        $product->save();
        if ($request->hasFile('image'))
            upload_vendor_file($request->image, 'products', null, 'App\Models\Product', $this->vendor_id, $product->id);

        $msg = __('msg.product_created_success', [], $this->lang_code);
        return $this->successResponse($msg);
    }

    public function updateProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $product = Product::where('vendor_id', $this->vendor_id)->where('id', $request->product_id)->first();
        if (!$product)
            return $this->errorResponse(__('msg.product_not_found', [], $this->lang_code), 400);

        if ($request->category_id)
            $product->category_id = $request->category_id;
        if ($request->active)
            $product->active = $request->active;
        if ($request->name_ar)
            $product->name_ar = $request->name_ar;
        if ($request->name_en)
            $product->name_en = $request->name_en;
        if ($request->cost_calculation_method)
            $product->cost_calculation_method = $request->cost_calculation_method;
        if ($request->price)
            $product->price = $request->price;
        if ($request->pricing_method)
            $product->pricing_method = $request->pricing_method;
        if ($request->tax_group_id)
            $product->tax_group_id = $request->tax_group_id;
        if ($request->retail_product)
            $product->retail_product = $request->retail_product;
        if ($request->type_sell)
            $product->type_sell = $request->type_sell;
        if ($request->barcode)
            $product->barcode = $request->barcode;
        if ($request->preparation_time)
            $product->preparation_time = $request->preparation_time;
        if ($request->calories)
            $product->calories = $request->calories;
        if ($request->number_people)
            $product->number_people = $request->number_people;
        if ($request->description_ar)
            $product->description_ar = $request->description_ar;
        if ($request->description_en)
            $product->description_en = $request->description_en;
        $product->save();
        $image = isset($product->image) ? $product->image : null;
        if ($request->hasFile('image'))
            upload_vendor_file($request->image, 'products', $image, 'App\Models\Product', $this->vendor_id, $product->id);

        $msg = __('msg.product_updated_success', [], $this->lang_code);
        return $this->successResponse($msg);
    }

    public function deleteProduct(Request $request)
    {
        $product = Product::where('vendor_id', $this->vendor_id)->where('id', $request->product_id)->first();
        if (!$product)
            return $this->errorResponse(__('msg.product_not_found', [], $this->lang_code), 400);
        $product->delete();

        $msg = __('msg.product_deleted_success', [], $this->lang_code);
        return $this->successResponse($msg);
    }

    public function productDetails(Request $request)
    {
        $product = Product::where('vendor_id', $this->vendor_id)->where('id', $request->product_id)->first();
        if (!$product)
            return $this->errorResponse(__('msg.product_not_found', [], $this->lang_code), 400);
        $data = [
            'id' => $product->id,
            'name_ar' => $product->name_ar,
            'name_en' => $product->name_en,
            'image' => isset($product->image) ? asset('public' . $product->image->file_path) : '',
            'code' => $product->code,
            'category_id' => $product->category_id,
            'tax_group_id' => $product->tax_group_id,
            'category_name' => isset($product->category) && $product->category->name($this->lang_code) ? $product->category->name($this->lang_code) : '',
            'tax_group' => isset($product->tax_group) && $product->tax_group->name($this->lang_code) ? $product->tax_group->name($this->lang_code) : '',
            'price' => $product->price,
            'active' => $product->active,
            'description_ar' => $product->description_ar,
            'description_en' => $product->description_en,
            'pricing_method' => $product->pricing_method,
            'retail_product' => $product->retail_product,
            'type_sell' => $product->type_sell,
            'barcode' => $product->barcode,
            'cost_calculation_method' => $product->cost_calculation_method,
            'preparation_time' => $product->preparation_time,
            'calories' => $product->calories,
            'number_people' => $product->number_people,
            'weight' => $product->weight,
            'expire' => $product->expire,
            'additions' => isset($product->additions) && count($product->additions) > 0 ? $product->additions->map(function ($addition) {
                return [
                    'addition_id' => $addition->addition_id,
                    'addition_name' => $addition->addition->name($this->lang_code) ?? '',
                    'max_choice' => $addition->max_choice,
                    'min_choice' => $addition->min_choice,
                    'free_choice' => $addition->free_choice,
                ];
            }) : [],
            'traits' => isset($product->traits) && count($product->traits) > 0 ? $product->traits->map(function ($trait) {
                return [
                    'trait_id' => $trait->trait_id,
                    'trait_name' => $trait->vendor_trait->name ?? '',
                    'value' => $trait->value,
                    'price' => $trait->price,
                ];
            }) : [],
            'active_branches' => isset($product->active_branches) && count($product->active_branches) > 0 ? $product->active_branches->map(function ($branch) {
                return [
                    'branch_id' => $branch->branch_id,
                    'branch_name' => $branch->branch->name($this->lang_code),
                    'active' => $branch->active,
                    'price' => $branch->price,
                ];
            }) : [],
            'deactive_branches' => isset($product->deactive_branches) && count($product->deactive_branches) > 0 ? $product->deactive_branches->map(function ($branch) {
                return [
                    'branch_id' => $branch->branch_id,
                    'branch_name' => $branch->branch->name($this->lang_code),
                    'active' => $branch->active,
                    'price' => $branch->price,
                ];
            }) : [],
            'tags' => $product->tags ?? [],
        ];

        $msg = __('msg.product_get_success', [], $this->lang_code);
        return $this->dataResponse($msg, $data, 200);
    }

    public function addProductTags(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'tags' => 'required',
            'product_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $tags = $request->tags;
        $product_id = $request->product_id;
        if (!is_array($tags))
            $tags = json_decode($tags);
        foreach ($tags as $tag) {
            $product_tag = ProductTag::where('product_id', $product_id)->where('tag_id', $tag)->first();
            if (!$product_tag)
                $product_tag = new ProductTag();
            $product_tag->product_id = $product_id;
            $product_tag->tag_id = $tag;
            $product_tag->save();
        }
        $msg = __('msg.tag_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteProductTag(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'tag_id' => 'required',
            'product_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $tag_id = $request->tag_id;
        $product_id = $request->product_id;
        $product_tag = ProductTag::where('product_id', $product_id)->where('tag_id', $tag_id)->first();
        if (!$product_tag)
            return $this->errorResponse(__('msg.tag_not_found', [], $this->lang_code), 400);
        $product_tag->delete();
        $msg = __('msg.tag_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function addProductAdditions(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'additions' => 'required',
            'product_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $additions = $request->additions;
        $product_id = $request->product_id;
        if (!is_array($additions))
            $additions = json_decode($additions);
        foreach ($additions as $addition) {
            $product_addition = ProductAddition::where('product_id', $product_id)->where('addition_id', $addition)->first();
            if (!$product_addition)
                $product_addition = new ProductAddition();
            $product_addition->product_id = $product_id;
            $product_addition->addition_id = $addition;
            $product_addition->save();
        }
        $msg = __('msg.addition_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function updateProductAdditions(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'addition_id' => 'required',
            'product_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $addition_id = $request->addition_id;
        $product_id = $request->product_id;

        $product_addition = ProductAddition::where('product_id', $product_id)->where('addition_id', $addition_id)->first();
        if (!$product_addition)
            return $this->errorResponse(__('msg.additions_not_found', [], $this->lang_code), 400);

        if ($request->max_choice)
            $product_addition->max_choice = $request->max_choice;
        if ($request->min_choice)
            $product_addition->min_choice = $request->min_choice;
        if ($request->free_choice)
            $product_addition->free_choice = $request->free_choice;
        $product_addition->save();

        $msg = __('msg.addition_updated_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteProductAdditions(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'addition_id' => 'required',
            'product_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $addition_id = $request->addition_id;
        $product_id = $request->product_id;

        $product_addition = ProductAddition::where('product_id', $product_id)->where('addition_id', $addition_id)->first();
        if (!$product_addition)
            return $this->errorResponse(__('msg.additions_not_found', [], $this->lang_code), 400);

        $product_addition->delete();

        $msg = __('msg.addition_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function addStockComponents(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'stock_components' => 'required',
            'product_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $stock_components = $request->stock_components;
        $product_id = $request->product_id;
        if (!is_array($stock_components))
            $stock_components = json_decode($stock_components);
        foreach ($stock_components as $stock_component) {
            $product_component = ProductComponent::where('product_id', $product_id)->where('stock_id', $stock_component)->first();
            if (!$product_component)
                $product_component = new ProductComponent();
            $product_component->product_id = $product_id;
            $product_component->stock_id = $stock_component;
            $product_component->save();
        }
        $msg = __('msg.stock_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function updateStockComponents(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'stock_id' => 'required',
            'product_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $stock_id = $request->stock_id;
        $product_id = $request->product_id;

        $product_component = ProductComponent::where('product_id', $product_id)->where('stock_id', $stock_id)->first();
        if (!$product_component)
            return $this->errorResponse(__('msg.stock_not_found', [], $this->lang_code), 400);
        $product_component->apply_on = $request->apply_on;
        $product_component->qty = $request->qty;
        $product_component->save();

        $msg = __('msg.stock_updated_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteStockComponents(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'stock_id' => 'required',
            'product_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $stock_id = $request->stock_id;
        $product_id = $request->product_id;

        $product_component = ProductComponent::where('product_id', $product_id)->where('stock_id', $stock_id)->first();
        if (!$product_component)
            return $this->errorResponse(__('msg.stock_not_found', [], $this->lang_code), 400);

        $product_component->delete();

        $msg = __('msg.stock_updated_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function addProductSpecialBranchPrice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'branch_id' => 'required',
            'price' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $product_id = $request->product_id;
        $branch_id = $request->branch_id;
        $product_bprice = ProductBranch::where('product_id', $product_id)
            ->where('branch_id', $branch_id)->first();
        if (!$product_bprice)
            $product_bprice = new ProductBranch();
        $product_bprice->product_id = $product_id;
        $product_bprice->branch_id = $branch_id;
        $product_bprice->price = $request->price;
        $product_bprice->active = $request->active;
        $product_bprice->save();

        $msg = __('msg.branch_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteProductSpecialBranchPrice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'branch_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $product_id = $request->product_id;
        $branch_id = $request->branch_id;
        $product_bprice = ProductBranch::where('product_id', $product_id)
            ->where('branch_id', $branch_id)->first();
        if (!$product_bprice)
            return $this->errorResponse(__('msg.branch_not_found', [], $this->lang_code), 400);
        $product_bprice->delete();

        $msg = __('msg.branch_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function addProductSpecialBranchDactive(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'branches' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $product_id = $request->product_id;
        $branches = $request->branches;
        if (!is_array($branches))
            $branches = json_decode($branches);
        foreach ($branches as $branch) {
            $product_bprice = ProductBranch::where('product_id', $product_id)
                ->where('branch_id', $branch)->first();
            if (!$product_bprice)
                $product_bprice = new ProductBranch();
            $product_bprice->product_id = $product_id;
            $product_bprice->branch_id = $branch;
            $product_bprice->price = 0;
            $product_bprice->active = 0;
            $product_bprice->save();
        }

        $msg = __('msg.branch_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function addProductTraits(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'traits' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $product_id = $request->product_id;
        $traits = $request->traits;
        if (!is_array($traits))
            $traits = json_decode($traits);
        foreach ($traits as $trait) {
            $product_trait = ProductTrait::where('product_id', $product_id)->where('trait_id', $trait->trait_id)->first();
            if (!$product_trait)
                $product_trait = new ProductTrait();
            $product_trait->product_id = $product_id;
            $product_trait->trait_id = $trait->trait_id;
            $product_trait->value = $trait->value;
            $product_trait->price = $trait->price > 0 ? $trait->price : 0;
            $product_trait->save();
        }

        $msg = __('msg.product_trait_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteProductTrait(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'trait_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $product_id = $request->product_id;
        $trait_id = $request->trait_id;

        $product_trait = ProductTrait::where('product_id', $product_id)->where('trait_id', $trait_id)->first();
        if (!$product_trait)
            return $this->errorResponse(__('msg.product_trait_not_found', [], $this->lang_code), 400);

        $product_trait->save();


        $msg = __('msg.product_trait_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    #################### addtions #############################
    public function additions(Request $request)
    {
        $name = $request->name;
        $code = $request->code;
        $is_delete = $request->is_delete;
        $created_at = $request->created_at;
        $additions = Addition::where('vendor_id', $this->vendor_id);
        if ($name)
            $additions = $additions->where('name', 'LIKE', '%' . $name . '%');
        if ($code)
            $additions = $additions->where('code', 'LIKE', '%' . $code . '%');
        if ($created_at)
            $additions = $additions->whereDate('created_at', $created_at);
        if ($is_delete == 1)
            $additions = $additions->withTrashed();

        $additions = $additions->orderBy('sort')->get();
        $data = $additions->map(function ($addition) {
            return [
                'id' => $addition->id,
                'name' => $addition->name($this->lang_code),
                'code' => $addition->code,
                'num_products' => isset($addition->products) && count($addition->products) > 0 ? count($addition->products) : 0,
                'num_addition_options' => isset($addition->addition_options) && count($addition->addition_options) > 0 ? count($addition->addition_options) : 0,
                'created_at' => date('d/m/Y H:i', strtotime($addition->created_at))
            ];
        });

        $msg = __('msg.addition_get_success', [], $this->lang_code);
        return $this->dataResponse($msg, $data);
    }

    public function generateAdditionCode()
    {
        $last_item_id = 0;
        $last_item = Addition::withTrashed()->where('vendor_id', $this->vendor_id)->orderBy('id', 'DESC')->first();
        if ($last_item) {
            $num = explode('-', $last_item->code);
            $last_item_id = $num[1];
        }
        $data = [
            'code' => 'AD-' . str_pad($last_item_id + 1, 5, "0", STR_PAD_LEFT)
        ];
        return $this->dataResponse('generated successfully', $data);
    }

    public function addNewAddition(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'name_ar' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $last_item_id = 0;
        $last_item = Addition::withTrashed()->where('vendor_id', $this->vendor_id)->orderBy('id', 'DESC')->first();
        if ($last_item) {
            $num = explode('-', $last_item->code);
            $last_item_id = $num[1];
        }

        $addition = new Addition();
        $addition->vendor_id = $this->vendor_id;
        $addition->name_ar = $request->name_ar;
        $addition->name_en = $request->name_en;
        $addition->code = 'AD-' . str_pad($last_item_id + 1, 5, "0", STR_PAD_LEFT);
        $addition->save();

        $msg = __('msg.addition_created_success', [], $this->lang_code);
        return $this->successResponse($msg);
    }

    public function updateAddition(Request $request)
    {

        $addition = Addition::where('vendor_id', $this->vendor_id)->where('id', $request->addition_id)->first();
        if (!$addition)
            return $this->errorResponse(__('msg.addition_not_found', [], $this->lang_code), 400);

        $validator = Validator::make($request->all(), [
            'name_ar' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $addition->name_ar = $request->name_ar;
        $addition->name_en = $request->name_en;
        $addition->save();

        $msg = __('msg.addition_updated_success', [], $this->lang_code);
        return $this->successResponse($msg);
    }

    public function deleteAddition(Request $request)
    {

        $addition = Addition::where('vendor_id', $this->vendor_id)->where('id', $request->addition_id)->first();
        if (!$addition)
            return $this->errorResponse(__('msg.addition_not_found', [], $this->lang_code), 400);

        $addition->delete();

        $msg = __('msg.addition_deleted_success', [], $this->lang_code);
        return $this->successResponse($msg);
    }

    ################################# addition options ##############
    public function additionOptions(Request $request)
    {
        $name = $request->name;
        $code = $request->code;
        $additions = $request->additions;
        $is_delete = $request->is_delete;
        $created_at = $request->created_at;
        $active = $request->active;

        $addition_options = AdditionOption::where('vendor_id', $this->vendor_id);
        if ($name)
            $addition_options = $addition_options->where('name_ar', 'LIKE', '%' . $name . '%')->orWhere('name_en', 'LIKE', '%' . $name . '%');
        if ($code)
            $addition_options = $addition_options->where('code', 'LIKE', '%' . $code . '%');
        if ($created_at)
            $addition_options = $addition_options->whereDate('created_at', $created_at);
        if ($additions)
            $addition_options = $addition_options->whereIn('addition_id', $additions);
        if ($active)
            $addition_options = $addition_options->where('active', $active);
        if ($is_delete == 1)
            $addition_options = $addition_options->withTrashed();

        $addition_options = $addition_options->orderBy('id', 'DESC')->get();

        $data = $addition_options->map(function ($option) {
            return [
                'id' => $option->id,
                'code' => $option->code,
                'name' => $option->name($this->lang_code),
                'addition' => isset($option->addition) && $option->addition->name($this->lang_code) ? $option->addition->name($this->lang_code) : '',
                'tax_group' => isset($option->tax_group) && $option->tax_group->name($this->lang_code) ? $option->tax_group->name($this->lang_code) : '',
                'active' => $option->active,
                'created_at' => date('d/m/Y H:i', strtotime($option->created_at))
            ];
        });

        $msg = __('msg.addition_options_get_success', [], $this->lang_code);
        return $this->dataResponse($msg, $data);
    }

    public function addAdditionOptions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required',
            'addition_id' => 'required',
            'cost_calculation_method' => 'required',
            'price' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $last_item_id = 0;
        $last_item = AdditionOption::withTrashed()->where('vendor_id', $this->vendor_id)->orderBy('id', 'DESC')->first();
        if ($last_item) {
            $num = explode('-', $last_item->code);
            $last_item_id = $num[1];
        }

        $additional_option = new AdditionOption();
        $additional_option->vendor_id = $this->vendor_id;
        $additional_option->name_ar = $request->name_ar;
        $additional_option->addition_id = $request->addition_id;
        $additional_option->cost_calculation_method = $request->cost_calculation_method;
        $additional_option->price = $request->price;
        $additional_option->tax_group_id = $request->tax_group_id ? $request->tax_group_id : 0;
        $additional_option->code = 'ADO-' . str_pad($last_item_id + 1, 5, "0", STR_PAD_LEFT);
        $additional_option->save();

        $msg = __('msg.addition_options_created_success', [], $this->lang_code);
        return $this->successResponse($msg);
    }

    public function updateAdditionOptions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required',
            'addition_id' => 'required',
            'cost_calculation_method' => 'required',
            'price' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $additional_option = AdditionOption::where('vendor_id', $this->vendor_id)->where('id', $request->additional_option_id)->first();
        if (!$additional_option)
            return $this->errorResponse(__('msg.addition_option_not_found', [], $this->lang_code), 400);

        if ($request->name_ar)
            $additional_option->name_ar = $request->name_ar;
        if ($request->name_en)
            $additional_option->name_en = $request->name_en;
        if ($request->addition_id)
            $additional_option->addition_id = $request->addition_id;
        if ($request->cost_calculation_method)
            $additional_option->cost_calculation_method = $request->cost_calculation_method;
        if ($request->price)
            $additional_option->price = $request->price;
        if ($request->tax_group_id)
            $additional_option->tax_group_id = $request->tax_group_id;
        if ($request->calories)
            $additional_option->calories = $request->calories;
        $additional_option->save();

        $msg = __('msg.addition_options_updated_success', [], $this->lang_code);
        return $this->successResponse($msg);
    }

    public function deleteAdditionOptions(Request $request)
    {

        $additional_option = AdditionOption::where('vendor_id', $this->vendor_id)->where('id', $request->additional_option_id)->first();
        if (!$additional_option)
            return $this->errorResponse(__('msg.addition_option_not_found', [], $this->lang_code), 400);

        $additional_option->delete();

        $msg = __('msg.addition_options_deleted_success', [], $this->lang_code);
        return $this->successResponse($msg);
    }

    public function generateAdditionOptionCode()
    {
        $last_item_id = 0;
        $last_item = AdditionOption::withTrashed()->where('vendor_id', $this->vendor_id)->orderBy('id', 'DESC')->first();
        if ($last_item) {
            $num = explode('-', $last_item->code);
            $last_item_id = $num[1];
        }
        $data = [
            'code' => 'ADO-' . str_pad($last_item_id + 1, 5, "0", STR_PAD_LEFT)
        ];
        return $this->dataResponse('generated successfully', $data);
    }

    public function additionOptionsDetails(Request $request)
    {

        $additional_option = AdditionOption::where('vendor_id', $this->vendor_id)->where('id', $request->additional_option_id)->first();
        if (!$additional_option)
            return $this->errorResponse(__('msg.addition_option_not_found', [], $this->lang_code), 400);

        $data = [
            'name_ar' => $additional_option->name_ar,
            'name_en' => $additional_option->name_en,
            'code' => $additional_option->code,
            'barcode' => $additional_option->barcode,
            'addition' => isset($additional_option->addition) && $additional_option->addition->name($this->lang_code) ? $additional_option->addition->name($this->lang_code) : '',
            'tax_group' => isset($additional_option->tax_group) && $additional_option->tax_group->name($this->lang_code) ? $additional_option->tax_group->name($this->lang_code) : '',
            'active' => $additional_option->active,
            'created_at' => date('d/m/Y H:i', strtotime($additional_option->created_at)),
            'cost_calculation_method' => $additional_option->cost_calculation_method,
            'cost_calculation_method_name' => $additional_option->cost_calc_method,
            'type_sell' => $additional_option->type_sell,
            'description_ar' => $additional_option->description_ar,
            'description_en' => $additional_option->description_en,
            'price' => $additional_option->price,
            'calories' => $additional_option->calories,
            'stocks' => isset($additional_option->stocks) && count($additional_option->stocks) > 0 ? $additional_option->stocks->map(function ($item) {
                return [
                    'id' => $item->id,
                    'stock_id' => $item->stock_id,
                    'stock_name' => $item->stock->name ?? '',
                    'stock_code' => $item->stock->code ?? '',
                    'stock_storage_unit' => $item->stock->storage_unit ?? '',
                    'stock_recipe_unit' => $item->stock->recipe_unit ?? '',
                    'stock_recipe_unit_quantity' => $item->stock->recipe_unit_quantity ?? '',
                ];
            }) : [],
            'active_branches_special_price' => isset($additional_option->active_branches_special_price) && count($additional_option->active_branches_special_price) > 0 ? $additional_option->active_branches_special_price->map(function ($item) {
                return [
                    'id' => $item->id,
                    'price' => (float)$item->price,
                    'branch_id' => $item->branch_id,
                    'branch_name' => $item->branch->name($this->lang_code) ?? '',
                    'branch_code' => $item->branch->code ?? '',
                ];
            }) : [],
            'dactive_branches_special_price' => isset($additional_option->dactive_branches_special_price) && count($additional_option->dactive_branches_special_price) > 0 ? $additional_option->dactive_branches_special_price->map(function ($item) {
                return [
                    'id' => $item->id,
                    'branch_id' => $item->branch_id,
                    'branch_name' => $item->branch->name($this->lang_code) ?? '',
                    'branch_code' => $item->branch->code ?? '',
                ];
            }) : [],
        ];

        $msg = __('msg.addition_options_get_success', [], $this->lang_code);
        return $this->dataResponse($msg, $data);
    }

    public function addAdditionOptionStock(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'additional_option_id' => 'required',
            'stocks' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $additional_option_id = $request->additional_option_id;
        $stocks = $request->stocks;
        if (!is_array($stocks))
            $stocks = json_decode($stocks);
        if ($stocks) {
            foreach ($stocks as $stock) {
                $stock_op = AdditionOptionsStocks::where('stock_id', $stock)
                    ->where('additional_option_id', $additional_option_id)->first();
                if (!$stock_op)
                    $stock_op = new AdditionOptionsStocks();
                $stock_op->stock_id = $stock;
                $stock_op->additional_option_id = $additional_option_id;
                $stock_op->save();

            }
        }

        $msg = __('msg.stocks_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteAdditionOptionStock(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'additional_option_id' => 'required',
            'stock_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $additional_option_id = $request->additional_option_id;
        $stock_id = $request->stock_id;

        $stock_op = AdditionOptionsStocks::where('stock_id', $stock_id)
            ->where('additional_option_id', $additional_option_id)->first();
        if (!$stock_op)
            return $this->errorResponse(__('msg.stock_not_found', [], $this->lang_code), 400);
        $stock_op->delete();

        $msg = __('msg.stocks_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function addAdditionOptionsSpecialBranchPrice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'additional_option_id' => 'required',
            'branch_id' => 'required',
            'price' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $additional_option_id = $request->additional_option_id;
        $branch_id = $request->branch_id;
        $additional_option_bprice = AdditionOptionsSpecialBranchPrice::where('additional_option_id', $additional_option_id)
            ->where('branch_id', $branch_id)->first();
        if (!$additional_option_bprice)
            $additional_option_bprice = new AdditionOptionsSpecialBranchPrice();
        $additional_option_bprice->additional_option_id = $additional_option_id;
        $additional_option_bprice->branch_id = $branch_id;
        $additional_option_bprice->price = $request->price;
        $additional_option_bprice->active = $request->active;
        $additional_option_bprice->save();

        $msg = __('msg.branch_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function addAdditionOptionsBranchDactive(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'additional_option_id' => 'required',
            'branches' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $additional_option_id = $request->additional_option_id;
        $branches = $request->branches;
        if (!is_array($branches))
            $branches = json_decode($branches);
        foreach ($branches as $branch) {
            $additional_option_bprice = AdditionOptionsSpecialBranchPrice::where('additional_option_id', $additional_option_id)
                ->where('branch_id', $branch)->first();
            if (!$additional_option_bprice)
                $additional_option_bprice = new AdditionOptionsSpecialBranchPrice();
            $additional_option_bprice->additional_option_id = $additional_option_id;
            $additional_option_bprice->branch_id = $branch;
            $additional_option_bprice->price = 0;
            $additional_option_bprice->active = 0;
            $additional_option_bprice->save();
        }

        $msg = __('msg.branch_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    ################# gif cards ########################3
    public function getGiftCards(Request $request)
    {
        $name = $request->name;
        $code = $request->code;
        $category_id = $request->category_id;
        $cost_calculation_method = $request->cost_calculation_method;
        $active = $request->active;
        $is_delete = $request->is_delete;
        $tags = $request->tags;
        $gift_card_number = $request->gift_card_number;
        $barcode = $request->barcode;
        $created_at = $request->created_at;

        $gift_cards = VendorGiftCard::where('vendor_id', $this->vendor_id);
        if ($name)
            $gift_cards = $gift_cards->where('name_ar', 'LIKE', '%' . $name . '%')->orWhere('name_en', 'LIKE', '%' . $name . '%');
        if ($code)
            $gift_cards = $gift_cards->where('code', 'LIKE', '%' . $code . '%');
        if ($barcode)
            $gift_cards = $gift_cards->where('barcode', 'LIKE', '%' . $barcode . '%');
        if ($gift_card_number)
            $gift_cards = $gift_cards->where('gift_card_number', 'LIKE', '%' . $gift_card_number . '%');
        if ($active)
            $gift_cards = $gift_cards->where('active', $active);
        if ($cost_calculation_method)
            $gift_cards = $gift_cards->where('cost_calculation_method', $cost_calculation_method);
        if ($created_at)
            $gift_cards = $gift_cards->whereDate('created_at', $created_at);
        if ($category_id)
            $gift_cards = $gift_cards->where('category_id', $category_id);
        if ($is_delete == 1)
            $gift_cards = $gift_cards->withTrashed();
        if ($tags)
            $gift_cards = $gift_cards->whereHas('tags', function ($q) use ($tags) {
                $q->whereIn('id', $tags);
            });

        $gift_cards = $gift_cards->orderBy('id', 'DESC')->get();

        $data = $gift_cards->map(function ($card) {
            return [
                'id' => $card->id,
                'image' => isset($card->municipal_file) && $card->municipal_file->file_path ? asset('public' . $card->municipal_file->file_path) : '',
                'name' => $card->name($this->lang_code),
                'code' => $card->code,
                'price' => $card->price,
                'active' => $card->active,
                'category' => isset($card->category) && $card->category->name($this->lang_code) ? $card->category->name($this->lang_code) : '',

            ];
        });

        $msg = __('msg.gift_card_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $data, 200);
    }

    public function generateGiftCardCode()
    {
        $last_item_id = 0;
        $last_item = VendorGiftCard::withTrashed()->where('vendor_id', $this->vendor_id)->orderBy('id', 'DESC')->first();
        if ($last_item) {
            $num = explode('-', $last_item->code);
            $last_item_id = $num[1];
        }
        $data = [
            'code' => 'GIC-' . str_pad($last_item_id + 1, 5, "0", STR_PAD_LEFT)
        ];
        return $this->dataResponse('generated successfully', $data);
    }

    public function genrateGiftCardNumber()
    {
        $gift_card_number = $this->genrateGiftNumber();
        $data = [
            'gift_number' => $gift_card_number
        ];
        return $this->dataResponse('generated successfully', $data);
    }

    public function addGiftCard(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required',
        ]);
        $last_item_id = 0;
        $last_item = VendorGiftCard::withTrashed()->where('vendor_id', $this->vendor_id)->orderBy('id', 'DESC')->first();
        if ($last_item) {
            $num = explode('-', $last_item->code);
            $last_item_id = $num[1];
        }
        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $gift_card = new VendorGiftCard();
        $gift_card->vendor_id = $this->vendor_id;
        $gift_card->name_ar = $request->name_ar;
        $gift_card->gift_card_number = $request->gift_card_number;
        $gift_card->cost_calculation_method = $request->cost_calculation_method;
        $gift_card->price = $request->price;
        $gift_card->barcode = $request->barcode;
        $gift_card->category_id = $request->category_id;
        $gift_card->active = 1;
        $gift_card->code = 'GIC-' . str_pad($last_item_id + 1, 5, "0", STR_PAD_LEFT);
        $gift_card->save();
        if ($request->image)
            upload_vendor_file($request->image, 'gift_cards', null, 'App\Models\VendorGiftCard', $this->vendor_id, $gift_card->id);

        $msg = __('msg.gift_card_created_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function updateGiftCard(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $gift_card = VendorGiftCard::where('vendor_id', $this->vendor_id)
            ->where('id', $request->gift_card_id)->first();
        if (!$gift_card)
            return $this->errorResponse(__('msg.gift_card_created_not_found', [], $this->lang_code), 400);

        if ($request->name_ar)
            $gift_card->name_ar = $request->name_ar;
        if ($request->name_en)
            $gift_card->name_en = $request->name_en;
        if ($request->gift_card_number)
            $gift_card->gift_card_number = $request->gift_card_number;
        if ($request->cost_calculation_method)
            $gift_card->cost_calculation_method = $request->cost_calculation_method;
        if ($request->price)
            $gift_card->price = $request->price;
        if ($request->barcode)
            $gift_card->barcode = $request->barcode;
        if ($request->category_id)
            $gift_card->category_id = $request->category_id;
        if ($request->active)
            $gift_card->active = $request->active;
        $gift_card->save();
        if ($request->image)
            upload_vendor_file($request->image, 'gift_cards', $gift_card->municipal_file, 'App\Models\VendorGiftCard', $this->vendor_id, $gift_card->id);

        $msg = __('msg.gift_card_update_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteGiftCard(Request $request)
    {
        $gift_card = VendorGiftCard::where('vendor_id', $this->vendor_id)
            ->where('id', $request->gift_card_id)->first();
        if (!$gift_card)
            return $this->errorResponse(__('msg.gift_card_created_not_found', [], $this->lang_code), 400);

        $gift_card->delete();
        $msg = __('msg.gift_card_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function giftCardDetails(Request $request)
    {
        $gift_card = VendorGiftCard::where('vendor_id', $this->vendor_id)
            ->where('id', $request->gift_card_id)->first();
        if (!$gift_card)
            return $this->errorResponse(__('msg.gift_card_created_not_found', [], $this->lang_code), 400);
        $branches = isset($gift_card->branches) && count($gift_card->branches) > 0 ? $gift_card->branches()->where('active', 0)->get() : [];
        $data = [
            'id' => $gift_card->id,
            'name' => $gift_card->name($this->lang_code),
            'code' => $gift_card->code,
            'category_id' => $gift_card->category_id,
            'category_name' => $gift_card->category->name($this->lang_code) ?? '',
            'cost_calculation_method' => $gift_card->cost_calculation_method,
            'active' => $gift_card->active,
            'gift_card_number' => $gift_card->gift_card_number,
            'barcode' => $gift_card->barcode,
            'price' => (float)$gift_card->price,
            'created_at' => date('d/m/Y H:i', strtotime($gift_card->created_at)),
            'tags' => $gift_card->tags ?? [],
            'deactive_branches' => count($branches) > 0 ? $branches->map(function ($item) {
                return [
                    'id' => $item->id,
                    'branch_id' => $item->branch_id,
                    'active' => $item->active,
                    'branch_name' => $item->branch->name($this->lang_code),
                    'branch_code' => $item->branch->code,
                ];
            }) : []
        ];
        $msg = __('msg.gift_card_get_success', [], $this->lang_code);

        return $this->dataResponse($msg, $data, 200);
    }

    public function addGiftCardTags(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'tags' => 'required',
            'gift_card_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $tags = $request->tags;
        $gift_card_id = $request->gift_card_id;
        if (!is_array($tags))
            $tags = json_decode($tags);
        foreach ($tags as $tag) {
            $gift_card_tag = GiftCardTags::where('gift_card_id', $gift_card_id)->where('tag_id', $tag)->first();
            if (!$gift_card_tag)
                $gift_card_tag = new GiftCardTags();
            $gift_card_tag->gift_card_id = $gift_card_id;
            $gift_card_tag->tag_id = $tag;
            $gift_card_tag->save();
        }
        $msg = __('msg.tag_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteGiftCardTag(Request $request)
    {
        $tag_id = $request->tag_id;
        $gift_card_id = $request->gift_card_id;

        $gift_card_tag = GiftCardTags::where('gift_card_id', $gift_card_id)->where('tag_id', $tag_id)->first();
        if (!$gift_card_tag)
            return $this->errorResponse(__('msg.tag_not_found', [], $this->lang_code), 400);

        $gift_card_tag->delete();

        $msg = __('msg.tag_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function addGiftCardBranch(Request $request)
    {
        $gift_card_id = $request->gift_card_id;
        $branch_id = $request->branch_id;
        $gift_card_branch = GiftCardBranches::where('gift_card_id', $gift_card_id)->where('branch_id', $branch_id)->first();
        if (!$gift_card_branch)
            $gift_card_branch = new GiftCardBranches();
        $gift_card_branch->gift_card_id = $gift_card_id;
        $gift_card_branch->branch_id = $branch_id;
        $gift_card_branch->active = $request->active;
        $gift_card_branch->save();

        $msg = __('msg.branch_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function genrateGiftNumber()
    {
        do {
            $code = Str::random(10);
            $data = VendorGiftCard::where('gift_card_number', $code)->first();
            if (!$data) return $code;
        } while (true);
    }

    ###################### product collections #############

    public function productCollections(Request $request)
    {
        $name = $request->name;
        $code = $request->code;
        $barcode = $request->barcode;
        $tax_group = $request->tax_group;
        $cost_calculation_method = $request->cost_calculation_method;
        $active = $request->active;
        $branches = $request->branches;
        $created_by = $request->created_by;
        $created_at = $request->created_at;

        $product_collections = ProductCollection::where('vendor_id', $this->vendor_id);
        if ($name)
            $product_collections = $product_collections->where('name_ar', 'LIKE', '%' . $name . '%')->orWhere('name_en', 'LIKE', '%' . $name . '%');
        if ($code)
            $product_collections = $product_collections->where('code', 'LIKE', '%' . $code . '%');
        if ($created_by)
            $product_collections = $product_collections->where('created_by', 'LIKE', '%' . $created_by . '%');
        if ($created_at)
            $product_collections = $product_collections->where('created_at', $created_at);
        if ($barcode)
            $product_collections = $product_collections->where('barcode', 'LIKE', '%' . $barcode . '%');
        if ($tax_group)
            $product_collections = $product_collections->where('tax_group_id', $tax_group);
        if ($cost_calculation_method)
            $product_collections = $product_collections->where('cost_calculation_method', $cost_calculation_method);
        if ($active)
            $product_collections = $product_collections->where('active', $active);
        if ($branches)
            $product_collections = $product_collections->whereHas('branches', function ($q) use ($branches) {
                $q->whereIn('branch_id', $branches);
            });
        $product_collections = $product_collections->orderBy('id', 'DESC')->get();

        $data = $product_collections->map(function ($collection) {
            return [
                'id' => $collection->id,
                'name' => $collection->name($this->lang_code),
                'image' => isset($collection->image) ? asset('public' . $collection->image->file_path) : '',
                'code' => $collection->code,
                'tax_group' => isset($collection->tax_group) && $collection->tax_group->name($this->lang_code) ? $collection->tax_group->name($this->lang_code) : '',
                'price' => $collection->price,
                'active' => $collection->active,
            ];
        });
        $msg = __('msg.product_collection_get_success', [], $this->lang_code);
        return $this->dataResponse($msg, $data);
    }

    public function generateProductCollectionCode()
    {
        $last_item_id = 0;
        $last_item = ProductCollection::where('vendor_id', $this->vendor_id)->orderBy('id', 'DESC')->first();
        if ($last_item) {
            $num = explode('-', $last_item->code);
            $last_item_id = $num[1];
        }
        $data = [
            'code' => 'PC-' . str_pad($last_item_id + 1, 5, "0", STR_PAD_LEFT)
        ];
        return $this->dataResponse('generated successfully', $data);
    }

    public function createProductCollection(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $last_item_id = 0;
        $last_item = ProductCollection::where('vendor_id', $this->vendor_id)->orderBy('id', 'DESC')->first();
        if ($last_item) {
            $num = explode('-', $last_item->code);
            $last_item_id = $num[1];
        }

        $collection = new ProductCollection();
        $collection->vendor_id = $this->vendor_id;
        $collection->name_ar = $request->name_ar;
        $collection->code = 'PC-' . str_pad($last_item_id + 1, 5, "0", STR_PAD_LEFT);
        $collection->barcode = $request->barcode;
        $collection->pricing_method = $request->pricing_method;
        $collection->tax_group_id = $request->tax_group_id;
        $collection->price = $request->price;
        $collection->type_sell = $request->type_sell;
        $collection->cost_calculation_method = $request->cost_calculation_method;
        $collection->created_by = vendor()->name;
        $collection->save();
        if ($request->hasFile('image'))
            upload_vendor_file($request->image, 'product_collection', null, 'App\Models\ProductCollection', $this->vendor_id, $collection->id);

        $msg = __('msg.product_collection_created_success', [], $this->lang_code);
        return $this->successResponse($msg);
    }

    public function updateProductCollection(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $collection = ProductCollection::where('vendor_id', $this->vendor_id)->where('id', $request->product_collection_id)->first();
        if (!$collection)
            return $this->errorResponse(__('msg.product_collection_not_found', [], $this->lang_code), 400);
        if ($request->name_ar)
            $collection->name_ar = $request->name_ar;
        if ($request->name_en)
            $collection->name_en = $request->name_en;
        if ($request->description_ar)
            $collection->description_ar = $request->description_ar;
        if ($request->description_en)
            $collection->description_en = $request->description_en;
        if ($request->barcode)
            $collection->barcode = $request->barcode;
        if ($request->pricing_method)
            $collection->pricing_method = $request->pricing_method;
        if ($request->tax_group_id)
            $collection->tax_group_id = $request->tax_group_id;
        if ($request->price)
            $collection->price = $request->price;
        if ($request->type_sell)
            $collection->type_sell = $request->type_sell;
        if ($request->cost_calculation_method)
            $collection->cost_calculation_method = $request->cost_calculation_method;
        if ($request->preparation_time)
            $collection->preparation_time = $request->preparation_time;
        if ($request->calories)
            $collection->calories = $request->calories;
        if ($request->number_people)
            $collection->number_people = $request->number_people;
        if ($request->retail_product)
            $collection->retail_product = $request->retail_product;
        $collection->save();
        $image = isset($product->image) ? $product->image : null;
        if ($request->hasFile('image'))
            upload_vendor_file($request->image, 'product_collection', $image, 'App\Models\ProductCollection', $this->vendor_id, $collection->id);

        $msg = __('msg.product_collection_updated_success', [], $this->lang_code);
        return $this->successResponse($msg);
    }
    public function productCollectionDetails(Request $request){
        $collection = ProductCollection::where('vendor_id', $this->vendor_id)->where('id', $request->product_collection_id)->first();
        if (!$collection)
            return $this->errorResponse(__('msg.product_collection_not_found', [], $this->lang_code), 400);

        $data = [
          'id' => $collection->id,
          'name_ar' => $collection->name_ar,
          'name_en' => $collection->name_en,
          'description_ar' => $collection->description_ar,
          'description_en' => $collection->description_en,
          'barcode' => $collection->barcode,
          'pricing_method' => $collection->pricing_method,
          'tax_group_id' => $collection->tax_group_id,
          'price' => $collection->price,
          'type_sell' => $collection->type_sell,
          'cost_calculation_method' => $collection->cost_calculation_method,
          'preparation_time' => $collection->preparation_time,
          'calories' => $collection->calories,
          'number_people' => $collection->number_people,
          'retail_product' => $collection->retail_product,
          'tags' => $collection->tags ?? [],
          'products' => isset($collection->products) && count($collection->products) > 0 ? $collection->products->map(function ($item){
              return [
                  'product_id' => $item->id,
                  'active' => $item->active,
                  'price' => $item->price,
                  'product_name' => $item->product->name($this->lang_code) ?? '',
                  'product_category_name' => $item->product->category->name($this->lang_code) ?? '',
              ];
          }) : [],
          'active_branches' => isset($collection->branches) && count($collection->branches) > 0 ? $collection->branches->where('active',1)->map(function ($item){
              return [
                  'branch_id' => $item->branch_id,
                  'price' => $item->price,
                  'active' => $item->active,
                  'branch_name' => $item->branch->name($this->lang_code) ?? '',
                  'branch_code' => $item->branch->code ?? '',
              ];
          }) : [],
            'deactive_branches' => isset($collection->branches) && count($collection->branches) > 0 ? $collection->branches->where('active',0)->map(function ($item){
              return [
                  'branch_id' => $item->branch_id,
                  'price' => $item->price,
                  'active' => $item->active,
                  'branch_name' => $item->branch->name($this->lang_code) ?? '',
                  'branch_code' => $item->branch->code ?? '',
              ];
          }) : [],
        ];

        $msg = __('msg.product_collection_get_success', [], $this->lang_code);

        return $this->dataResponse($msg,$data,200);
    }
    public function deleteProductCollection(Request $request)
    {


        $collection = ProductCollection::where('vendor_id', $this->vendor_id)->where('id', $request->product_collection_id)->first();
        if (!$collection)
            return $this->errorResponse(__('msg.product_collection_not_found', [], $this->lang_code), 400);

        $collection->delete();

        $msg = __('msg.product_collection_deleted_success', [], $this->lang_code);
        return $this->successResponse($msg);
    }

    public function addProductCollectionTags(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'tags' => 'required',
            'product_collection_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $tags = $request->tags;
        $product_collection_id = $request->product_collection_id;
        if (!is_array($tags))
            $tags = json_decode($tags);
        foreach ($tags as $tag) {
            $product_col_tag = ProductCollectionTag::where('product_collection_id', $product_collection_id)->where('tag_id', $tag)->first();
            if (!$product_col_tag)
                $product_col_tag = new ProductCollectionTag();
            $product_col_tag->product_collection_id = $product_collection_id;
            $product_col_tag->tag_id = $tag;
            $product_col_tag->save();
        }
        $msg = __('msg.tag_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteProductCollectionTag(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'tag_id' => 'required',
            'product_collection_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $tag_id = $request->tag_id;
        $product_collection_id = $request->product_collection_id;
        $product_col_tag = ProductCollectionTag::where('product_collection_id', $product_collection_id)->where('tag_id',$tag_id)->first();
        if (!$product_col_tag)
            return $this->errorResponse(__('msg.tag_not_found', [], $this->lang_code), 400);
        $product_col_tag->delete();
        $msg = __('msg.tag_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function addProductCollectionBranchPrice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_collection_id' => 'required',
            'branch_id' => 'required',
            'price' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $product_collection_id = $request->product_collection_id;
        $branch_id = $request->branch_id;
        $product_bprice = ProductCollectionBranch::where('product_collection_id', $product_collection_id)
            ->where('branch_id', $branch_id)->first();
        if (!$product_bprice)
            $product_bprice = new ProductCollectionBranch();
        $product_bprice->product_collection_id = $product_collection_id;
        $product_bprice->branch_id = $branch_id;
        $product_bprice->price = $request->price;
        $product_bprice->active = $request->active;
        $product_bprice->save();

        $msg = __('msg.branch_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteProductCollectionBranchPrice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_collection_id' => 'required',
            'branch_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $product_collection_id = $request->product_collection_id;
        $branch_id = $request->branch_id;
        $product_bprice = ProductCollectionBranch::where('product_collection_id', $product_collection_id)
            ->where('branch_id', $branch_id)->first();
        if (!$product_bprice)
            return $this->errorResponse(__('msg.branch_not_found', [], $this->lang_code), 400);
        $product_bprice->delete();

        $msg = __('msg.branch_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function addProductCollectionBranchDactive(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_collection_id' => 'required',
            'branches' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $product_collection_id = $request->product_collection_id;
        $branches = $request->branches;
        if (!is_array($branches))
            $branches = json_decode($branches);
        foreach ($branches as $branch) {
            $product_bprice = ProductCollectionBranch::where('product_collection_id', $product_collection_id)
                ->where('branch_id', $branch)->first();
            if (!$product_bprice)
                $product_bprice = new ProductCollectionBranch();
            $product_bprice->product_collection_id = $product_collection_id;
            $product_bprice->branch_id = $branch;
            $product_bprice->price = 0;
            $product_bprice->active = 0;
            $product_bprice->save();
        }

        $msg = __('msg.branch_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function addProductCollectionProducts(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'products' => 'required',
            'product_collection_id' => 'required',
            'active' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $products = $request->products;
        $product_collection_id = $request->product_collection_id;
        if (!is_array($products))
            $products = json_decode($products);
        foreach ($products as $product) {
            $product_col_product = ProductCollectionProducts::where('product_collection_id', $product_collection_id)
                ->where('product_id', $product)->first();
            if (!$product_col_product)
                $product_col_product = new ProductCollectionProducts();
            $product_col_product->product_collection_id = $product_collection_id;
            $product_col_product->product_id = $product;
            $product_col_product->active = $request->active;
            $product_col_product->save();
        }
        $msg = __('msg.products_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteProductCollectionProducts(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'product_collection_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $product_id = $request->product_id;
        $product_collection_id = $request->product_collection_id;

            $product_col_product = ProductCollectionProducts::where('product_collection_id', $product_collection_id)
                ->where('product_id', $product_id)->first();
            if (!$product_col_product)
                return $this->errorResponse(__('msg.product_not_found', [], $this->lang_code), 400);

        $product_col_product->delete();

        $msg = __('msg.product_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
}
