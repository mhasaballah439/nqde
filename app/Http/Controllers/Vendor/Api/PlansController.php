<?php

namespace App\Http\Controllers\Vendor\Api;

use App\Http\Controllers\Controller;
use App\Models\Bouquet;
use App\Models\Setting;
use App\Models\UsersCard;
use App\Models\VendorPlane;
use App\Traits\ApiTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PlansController extends Controller
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

    public function bouquets()
    {
        $bouquets = Bouquet::get();
        $data = $bouquets->map(function ($plan) {
            $options = json_decode($plan->bouquet_options);

            $optionsArr = [];
            if ($options) {
                for ($i = 1; $i <= 31; $i++) {
                    if (in_array($i, $options)) {
                        if ($i == 2)
                            $optionsArr[] = (string)((float)$plan->branch_price);
                        elseif ($i == 7)
                            $optionsArr[] = $plan->report_id == 1 ? 'تقارير محدودة' : 'true';
                        elseif ($i == 18)
                            $optionsArr[] = (string)((float)$plan->warehouse_price);
                        elseif ($i == 31)
                            $optionsArr[] = $plan->report_id == 1 ? 'تطبيقات محدودة' : 'جميع التطبيقات';
                        else
                            $optionsArr[] = 'true';
                    } else {
                        $optionsArr[] = 'false';
                    }

                }
            }

            return [
                'id' => $plan->id,
                'name' => $plan->name,
                'month_price' => $plan->month_price,
                'year_price' => $plan->year_price,
                'is_free' => $plan->is_free,
                'trail_days' => $plan->trail_days,
                'bouquet_options' => $optionsArr
            ];
        });
        return $this->dataResponse(__('msg.data_success_get', [], $this->lang_code), $data);
    }

    public function bouquetDetails(Request $request)
    {
        $plan = Bouquet::find($request->get('plan_id'));
        if (!$plan)
            return $this->errorResponse(__('msg.plan_not_found', [], $this->lang_code), 400);

        $options = json_decode($plan->bouquet_options);

        $optionsArr = [];
        if ($options) {
            for ($i = 1; $i <= 31; $i++) {
                if (in_array($i, $options)) {
                    if ($i == 2)
                        $optionsArr[] = (string)((float)$plan->branch_price);
                    elseif ($i == 7)
                        $optionsArr[] = $plan->report_id == 1 ? 'تقارير محدودة' : 'true';
                    elseif ($i == 18)
                        $optionsArr[] = (string)((float)$plan->warehouse_price);
                    elseif ($i == 31)
                        $optionsArr[] = $plan->report_id == 1 ? 'تطبيقات محدودة' : 'جميع التطبيقات';
                    else
                        $optionsArr[] = 'true';
                } else {
                    $optionsArr[] = 'false';
                }

            }
        }
        $data = [
            'id' => $plan->id,
            'name' => $plan->name,
            'month_price' => $plan->month_price,
            'year_price' => $plan->year_price,
            'is_free' => $plan->is_free,
            'trail_days' => $plan->trail_days,
            'bouquet_options' => $optionsArr
        ];

        return $this->dataResponse(__('msg.data_success_get', [], $this->lang_code), $data);
    }

    public function subscribePlan(Request $request)
    {

        $c_date = date('Y-m-d');
        $vendor = vendor();
        if ($request->get('is_trail') == 1) {
            $plan = Bouquet::where('is_free', 1)->first();
            if (!$plan)
                return $this->errorResponse(__('msg.plan_not_found', [], $this->lang_code), 400);
            if (vendor()->is_take_free_plan == 1)
                return $this->errorResponse(__('msg.subscribed_demo_before', [], $this->lang_code), 403);


            $subscribe = new VendorPlane();
            $subscribe->vendor_id = $this->vendor_id;
            $subscribe->bouquet_id = $plan->id;
            $subscribe->price_type = 0;
            $subscribe->is_free_trail = 1;
            $subscribe->st_date = $c_date;
            $subscribe->end_date = date('Y-m-d', strtotime($c_date . ' + ' . $plan->trail_days . ' days'));
            $subscribe->price = 0;
            $subscribe->save();

            vendor()->update(['is_take_free_plan' => 1]);

            $name = $vendor->first_name . ' ' . $vendor->family_name;
            Mail::send(['html' => 'emails.account_data'], ['name' => $name, 'email' => $vendor->email, 'account_number' => $vendor->account_number], function ($message) use ($vendor) {
                $message->from('no-replay@nqde.net', 'nqde.net');
                $message->subject('New nqde account');
                $message->to($vendor->email);
            });
            return $this->dataResponse(__('msg.subscribed_successfully', [], $this->lang_code), vendor_data());

        }else{
            $plan = Bouquet::find($request->get('plan_id'));
            if (!$plan)
                return $this->errorResponse(__('msg.plan_not_found', [], $this->lang_code), 400);

            $price_type = $request->price_type;
            if (!$price_type)
                return $this->errorResponse(__('msg.please_add_price_type', [], $this->lang_code), 400);

            if ($price_type == 1)
                $price = $plan->month_price + $plan->warehouse_price + $plan->branch_price;
            else
                $price = $plan->year_price + $plan->warehouse_price + $plan->branch_price;

            $directData = $this->myfatorah_payment($request,$price);

            if (isset($directData->Status) && $directData->Status == 'SUCCESS') {
                $name = $vendor->first_name . ' ' . $vendor->family_name;
                Mail::send(['html' => 'emails.account_data'], ['name' => $name, 'email' => $vendor->email, 'account_number' => $vendor->account_number], function ($message) use ($vendor) {
                    $message->from('no-replay@nqde.net', 'nqde.net');
                    $message->subject('New nqde account');
                    $message->to($vendor->email);
                });
                $subscribe = new VendorPlane();
                $subscribe->vendor_id = $this->vendor_id;
                $subscribe->bouquet_id = $plan->id;
                $subscribe->price_type = $request->price_type;
                $subscribe->st_date = date('Y-m-d');
                $subscribe->end_date = $subscribe->price_type == 1 ? date('Y-m-d', strtotime($subscribe->st_date . ' + 1 month')) : date('Y-m-d', strtotime($subscribe->st_date . ' + 1 years'));
                $subscribe->price = $price;
                $subscribe->payment = json_encode($directData);
                $subscribe->is_payment = 1;
                $subscribe->save();

                return $this->dataResponse(__('msg.subscribed_successfully', [], $this->lang_code), vendor_data());
            }else{
                $msg = isset($directData->ErrorMessage) && $directData->ErrorMessage ? $directData->ErrorMessage : 'Payment failed';
                return $this->errorResponse($msg, 400);
            }
        }

    }


}
