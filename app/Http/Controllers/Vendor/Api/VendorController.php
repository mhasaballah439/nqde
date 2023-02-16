<?php

namespace App\Http\Controllers\Vendor\Api;

use App\Http\Controllers\Controller;
use App\Models\DeliveryArea;
use App\Models\Vendor;
use App\Traits\ApiTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VendorController extends Controller
{
    use ApiTrait;

    var $lang_code;

    public function __construct()
    {
        $this->lang_code = \request()->get('lang') ? \request()->get('lang') : get_default_languages();
    }

    public function vendorData()
    {

        return $this->dataResponse(__('msg.vend_sorted_success', [], $this->lang_code), vendor_data());
    }

    public function deliveryAreas(Request $request)
    {
        $name = $request->get('name');
        $number = $request->get('number');
        $is_delete = $request->get('is_delete');
        $created_at = $request->get('created_at');

        $delivery_areas = DeliveryArea::where('vendor_id', vendor()->id);
        if ($name)
            $delivery_areas = $delivery_areas->where('name_ar', 'LIKE', '%' . $name . '%')->orWhere('name_en', 'LIKE', '%' . $name . '%');
        if ($number)
            $delivery_areas = $delivery_areas->where('number', 'LIKE', '%' . $number . '%');
        if ($created_at)
            $delivery_areas = $delivery_areas->whereDate('created_at', $created_at);
        if ($is_delete == 0)
            $delivery_areas = $delivery_areas->whereNull('deleted_at');
        else
            $delivery_areas = $delivery_areas->withTrashed();

        $delivery_areas = $delivery_areas->orderBy('id', 'DESC')->get();

        $data = $delivery_areas->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name($this->lang_code),
                'number' => $item->number,
                'source' => $item->source,
                'created_at' => date('d/m/Y H:i', strtotime($item->created_at))
            ];
        });
        $msg = __('msg.delivery_areas_get_success', [], $this->lang_code);
        return $this->dataResponse($msg, $data, 200);
    }

    public function treashedDeliveryAreas(Request $request)
    {
        $name = $request->get('name');
        $number = $request->get('number');
        $created_at = $request->get('created_at');

        $delivery_areas = DeliveryArea::where('vendor_id', vendor()->id);
        if ($name)
            $delivery_areas = $delivery_areas->where('name_ar', 'LIKE', '%' . $name . '%')->orWhere('name_en', 'LIKE', '%' . $name . '%');
        if ($number)
            $delivery_areas = $delivery_areas->where('number', 'LIKE', '%' . $number . '%');
        if ($created_at)
            $delivery_areas = $delivery_areas->whereDate('created_at', $created_at);

        $delivery_areas = $delivery_areas->onlyTrashed()->orderBy('id', 'DESC')->get();

        $data = $delivery_areas->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name($this->lang_code),
                'number' => $item->number,
                'source' => $item->source,
                'created_at' => date('d/m/Y H:i', strtotime($item->created_at))
            ];
        });
        $msg = __('msg.delivery_areas_get_success', [], $this->lang_code);
        return $this->dataResponse($msg, $data, 200);
    }
    public function generateDeliveryAreaCode(){
        $last_item_id = 0;
        $last_item = DeliveryArea::where('vendor_id',vendor()->id)->withTrashed()->orderBy('id','DESC')->first();
        if($last_item){
            $num = explode('-',$last_item->number);
            $last_item_id = $num[1];
        }

        $data = [
            'operation_number' => 'DA-'.($last_item_id + 1)
        ];
        return $this->dataResponse('generated successfully',$data);
    }
    public function createDeliveryArea(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $last_item_id = 0;
        $last_item = DeliveryArea::where('vendor_id',vendor()->id)->withTrashed()->orderBy('id','DESC')->first();
        if($last_item){
            $num = explode('-',$last_item->number);
            $last_item_id = $num[1];
        }

        $delivery = new DeliveryArea();
        $delivery->vendor_id = vendor()->id;
        $delivery->name_ar = $request->get('name_ar');
        $delivery->name_en = $request->get('name_en');
        $delivery->number = 'DA-'.($last_item_id + 1);
        $delivery->source = $request->get('source');
        $delivery->save();

        $msg = __('msg.delivery_areas_created_success', [], $this->lang_code);
        return $this->successResponse($msg);
    }

    public function updateDeliveryArea(Request $request)
    {

        $delivery = DeliveryArea::where('vendor_id', vendor()->id)->where('id', $request->get('delivery_id'))->first();
        if (!$delivery)
            return $this->errorResponse(__('msg.delivery_areas_not_found', [], $this->lang_code), 400);

        if ($request->get('name_ar'))
            $delivery->name_ar = $request->get('name_ar');
        if ($request->get('name_en'))
            $delivery->name_en = $request->get('name_en');
        if ($request->get('number'))
            $delivery->number = $request->get('number');
        if ($request->get('source'))
            $delivery->source = $request->get('source');
        $delivery->save();

        $msg = __('msg.delivery_areas_updated_success', [], $this->lang_code);
        return $this->successResponse($msg);
    }

    public function deleteDeliveryArea(Request $request){
        $delivery = DeliveryArea::where('vendor_id', vendor()->id)->where('id', $request->get('delivery_id'))->first();
        if (!$delivery)
            return $this->errorResponse(__('msg.delivery_areas_not_found', [], $this->lang_code), 400);

        $delivery->delete();

        $msg = __('msg.delivery_areas_deleted_success', [], $this->lang_code);
        return $this->successResponse($msg);
    }
}
