<?php

namespace App\Http\Controllers\Vendor\Api;

use App\Http\Controllers\Controller;
use App\Models\Addition;
use App\Models\AdditionOption;
use App\Models\Bouquet;
use App\Models\Order;
use App\Models\OrderTags;
use App\Models\Setting;
use App\Models\Tax;
use App\Models\TaxGroup;
use App\Models\UsersCard;
use App\Models\VendorDiscount;
use App\Models\VendorGiftCard;
use App\Models\VendorPlane;
use App\Models\VendorTrait;
use App\Traits\ApiTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class OrdersController extends Controller
{
    use ApiTrait;

    var $lang_code;
    var $vendor;
    var $vendor_id;
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

    public function orders(Request $request)
    {
        $from_created_at = $request->from_created_at;
        $to_created_at = $request->to_created_at;
        $code = $request->code;
        $order_number = $request->order_number;
        $work_date = $request->work_date;
        $branch_id = $request->branch_id;
        $tags = $request->tags;
        $status_id = $request->status_id;
        $type = $request->type;
        $source = $request->source;
        $notes = $request->notes;
        $notes_to_kitchen = $request->notes_to_kitchen;
        $user_id = $request->user_id;
        $discount_name = $request->discount_name;
        $received_date = $request->received_date;
        $device_id = $request->device_id;

        $orders = Order::where('vendor_id', $this->vendor_id);
        if ($from_created_at && $to_created_at)
            $orders = $orders->whereBetween('created_at', [$from_created_at, $to_created_at]);
        if ($code)
            $orders = $orders->where('code', 'LIKE', '%' . $code . '%');
        if ($order_number)
            $orders = $orders->where('order_number', 'LIKE', '%' . $order_number . '%');
        if ($notes)
            $orders = $orders->where('notes', 'LIKE', '%' . $notes . '%');
        if ($notes_to_kitchen)
            $orders = $orders->where('notes_to_kitchen', 'LIKE', '%' . $notes_to_kitchen . '%');
        if ($work_date)
            $orders = $orders->whereDate('work_date', $work_date);
        if ($received_date)
            $orders = $orders->whereDate('received_date', $received_date);
        if ($branch_id)
            $orders = $orders->where('branch_id', $branch_id);
        if ($type)
            $orders = $orders->where('type', $type);
        if ($source)
            $orders = $orders->where('source', $source);
        if ($user_id)
            $orders = $orders->where('user_id', $user_id);
        if ($device_id)
            $orders = $orders->where('device_id', $device_id);
        if ($discount_name)
            $orders = $orders->whereHas('discount_data', function ($q) use ($discount_name) {
                $q->where('name_ar', 'LIKE', '%' . $discount_name . '%')->orWhere('name_en', 'LIKE', '%' . $discount_name . '%');
            });

        $orders = $orders->orderBy('id', 'DESC')->get();

        $data = $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'code' => $order->code,
                'order_number' => $order->order_number,
                'branch_name' => $order->branch->name($this->lang_code) ?? '',
                'user_name' => $order->user->name ?? '',
                'type_name' => $order->type_name,
                'open_time' => $order->open_time,
                'total' => $order->total,
                'sub_total' => $order->sub_total,
                'work_date' => date('d/m/Y H:i', strtotime($order->work_date)),
            ];
        });

        return $this->dataResponse('Orders get success', $data, 200);
    }

    public function createOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => 'required',
            'user_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $gift_card = VendorGiftCard::find($request->gift_card_id);
        $discount = VendorDiscount::find($request->discount_id);

        $last_order_id = 0;
        $last_order = Order::where('vendor_id', $this->vendor_id)->orderBy('id', 'DESC')->first();
        if ($last_order) {
            $num = explode('-', $last_order->code);
            $last_order_id = $num[1];
        }

        $order = new Order();
        $order->vendor_id = $this->vendor_id;
        $order->user_id = $request->user_id;
        $order->branch_id = $request->branch_id;
        $order->discount_id = $discount ? $discount->id : 0;
        $order->gift_card_id = $gift_card ? $gift_card->id : 0;
        $order->gift_card_cost_calc_method = $gift_card ? $gift_card->cost_calculation_method : 0;
        $order->gift_card_cost = $gift_card ? $gift_card->price : 0;
        $order->code = 'ORD-' . str_pad($last_order_id + 1, 5, "0", STR_PAD_LEFT);
        $order->order_number = $this->genrateOrderNumber();
        $order->creator = $this->vendor_name;
        $order->discount = $discount ? $discount->discount : 0;
        $order->discount_type = $discount ? $discount->discount_type : 0;
        $order->status_id = $request->status_id ? $request->status_id : 1;
        if ($request->table_id)
            $order->table_id = $request->table_id;
        if ($request->device_id)
            $order->device_id = $request->device_id;
        if ($request->order_type_id)
            $order->order_type_id = $request->order_type_id;
        if ($request->source)
            $order->source = $request->source;
        if ($request->open_time)
            $order->open_time = $request->open_time;
        if ($request->close_time)
            $order->close_time = $request->close_time;
        if ($request->pickup_time)
            $order->pickup_time = $request->pickup_time;
        if ($request->visitors)
            $order->visitors = $request->visitors;
        if ($request->notes_to_kitchen)
            $order->notes_to_kitchen = $request->notes_to_kitchen;
        if ($request->work_date)
            $order->work_date = $request->work_date;
        if ($request->notes)
            $order->notes = $request->notes;
        if ($request->received_date)
            $order->received_date = $request->received_date;
        $order->save();

        $msg = __('msg.order_saved_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    protected function genrateOrderNumber()
    {
        do {
            $code = rand(111111, 999999);
            $data = Order::where('order_number', $code)->first();
            if (!$data) return $code;
        } while (true);
    }

    public function addOrderTags(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
            'tags' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $tags = $request->tags;
        if (!is_array($request->tags))
            $tags = json_decode($request->tags);
        if (count($tags) > 0) {
            foreach ($tags as $tag) {
                $order_tag = OrderTags::where('order_id', $request->order_id)
                    ->where('tag_id', $tag)->first();
                if (!$order_tag)
                    $order_tag = new OrderTags();
                $order_tag->order_id = $request->order_id;
                $order_tag->tag_id = $tag;
                $order_tag->save();
            }
        }

        $msg = __('msg.tags_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function deleteOrderTag(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
            'tag_id' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);
        $order_tag = OrderTags::where('order_id', $request->order_id)
            ->where('tag_id', $request->tag_id)->first();
        if (!$order_tag)
            return $this->errorResponse(__('msg.tag_not_found', [], $this->lang_code), 400);
        $order_tag->delete();


        $msg = __('msg.tags_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function orderAddTagsList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'orders' => 'required',
            'tags' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $orders = $request->orders;
        if (!is_array($orders))
            $orders = json_decode($orders);

        $tags = $request->tags;
        if (!is_array($tags))
            $tags = json_decode($tags);
        if (count($orders) > 0 && count($tags) > 0) {
            foreach ($orders as $order) {
                foreach ($tags as $tag) {
                    $order_tag = OrderTags::where('order_id', $order)
                        ->where('tag_id', $tag)->first();
                    if (!$order_tag)
                        $order_tag = new OrderTags();
                    $order_tag->order_id = $order;
                    $order_tag->tag_id = $tag;
                    $order_tag->save();
                }
            }
        }

        $msg = __('msg.tag_add_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function orderDeleteTagsList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'orders' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $orders = $request->orders;
        if (!is_array($orders))
            $orders = json_decode($orders);

        OrderTags::whereIn('order_id', $orders)->delete();

        $msg = __('msg.tag_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }

    public function orderListDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'orders' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $orders = $request->orders;
        if (!is_array($orders))
            $orders = json_decode($orders);

        Order::whereIn('id', $orders)->delete();

        $msg = __('msg.orders_deleted_success', [], $this->lang_code);

        return $this->successResponse($msg, 200);
    }
}
