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
    var $vendor_id;

    public function __construct()
    {
        $this->lang_code = \request()->get('lang') ? \request()->get('lang') : get_default_languages();
         if (auth()->guard('vendor')->check())
             $this->vendor_id = vendor()->id;
        elseif (auth()->guard('vendor_employee')->check())
            $this->vendor_id = vendor_employee()->vendor->id;
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

        $delivery_areas = DeliveryArea::where('vendor_id', $this->vendor_id);
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

        $delivery_areas = DeliveryArea::where('vendor_id', $this->vendor_id);
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
        $last_item = DeliveryArea::where('vendor_id',$this->vendor_id)->withTrashed()->orderBy('id','DESC')->first();
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
        $last_item = DeliveryArea::where('vendor_id',$this->vendor_id)->withTrashed()->orderBy('id','DESC')->first();
        if($last_item){
            $num = explode('-',$last_item->number);
            $last_item_id = $num[1];
        }

        $delivery = new DeliveryArea();
        $delivery->vendor_id = $this->vendor_id;
        $delivery->name_ar = $request->name_ar;
        $delivery->name_en = $request->name_en;
        $delivery->number = 'DA-'.($last_item_id + 1);
        $delivery->source = $request->source;
        $delivery->lat = $request->lat;
        $delivery->lng = $request->lng;
        $delivery->save();

        $msg = __('msg.delivery_areas_created_success', [], $this->lang_code);
        return $this->successResponse($msg);
    }

    public function updateDeliveryArea(Request $request)
    {

        $delivery = DeliveryArea::where('vendor_id', $this->vendor_id)->where('id', $request->get('delivery_id'))->first();
        if (!$delivery)
            return $this->errorResponse(__('msg.delivery_areas_not_found', [], $this->lang_code), 400);

        if ($request->name_ar)
            $delivery->name_ar = $request->name_ar;
        if ($request->name_en)
            $delivery->name_en = $request->name_en;
        if ($request->number)
            $delivery->number = $request->number;
        if ($request->source)
            $delivery->source = $request->source;
        if ($request->lat)
            $delivery->lat = $request->lat;
        if ($request->lng)
            $delivery->lng = $request->lng;
        $delivery->save();

        $msg = __('msg.delivery_areas_updated_success', [], $this->lang_code);
        return $this->successResponse($msg);
    }

    public function deleteDeliveryArea(Request $request){
        $delivery = DeliveryArea::where('vendor_id', $this->vendor_id)->where('id', $request->get('delivery_id'))->first();
        if (!$delivery)
            return $this->errorResponse(__('msg.delivery_areas_not_found', [], $this->lang_code), 400);

        $delivery->delete();

        $msg = __('msg.delivery_areas_deleted_success', [], $this->lang_code);
        return $this->successResponse($msg);
    }

    public function deliveryAreasDeleteList(Request $request){
        $validator = Validator::make($request->all(), [
            'delivery_areas' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $delivery_areas = $request->delivery_areas;
        if (!is_array($delivery_areas))
            $delivery_areas = json_decode($delivery_areas);

        DeliveryArea::whereIn('id',$delivery_areas)->delete();


        $msg = __('msg.deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function deliveryAreasRestoreList(Request $request){
        $validator = Validator::make($request->all(), [
            'delivery_areas' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $delivery_areas = $request->delivery_areas;
        if (!is_array($delivery_areas))
            $delivery_areas = json_decode($delivery_areas);

        DeliveryArea::withTrashed()->whereIn('id',$delivery_areas)->restore();


        $msg = __('msg.restore_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
    public function deliveryAreasRestoreSingleItem(Request $request){
        $validator = Validator::make($request->all(), [
            'delivery_area_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        DeliveryArea::withTrashed()->where('id',$request->delivery_area_id)->restore();


        $msg = __('msg.restore_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
}
