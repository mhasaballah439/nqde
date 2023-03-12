<?php

namespace App\Http\Controllers\Vendor\Api;

use App\Events\SendResetPasswordEmail;
use App\Events\SendVendorActiveEmail;
use App\Models\Branch;
use App\Models\StoreHouse;
use App\Models\Vendor;
use App\Models\VendorEmployee;
use App\Models\VendorMedia;
use App\Traits\ApiTrait;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Event;

class AuthController extends Controller
{
    use ApiTrait;

    var $lang_code;

    public function __construct()
    {
        $this->lang_code = \request()->get('lang') ? \request()->get('lang') : get_default_languages();
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
            'account_number' => 'required',
        ]);
        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $vendor = Vendor::where('email', $request->email)->where('account_number', $request->account_number)->first();
        if ($vendor) {
            if (!Hash::check($request->password, $vendor->password))
                return $this->errorResponse(__('msg.password_error', [], $this->lang_code), 400);
            if ($vendor->status == 0)
                return $this->errorResponse(__('msg.account_not_active', [], $this->lang_code), 403);
            $token = auth('vendor')->login($vendor);
            $vendor_data = auth('vendor')->user();
            $data = [
                'first_name' => $vendor_data->first_name,
                'family_name' => $vendor_data->family_name,
                'email' => $vendor_data->email,
                'status' => $vendor_data->status,
                'account_number' => $vendor_data->account_number,
                'country_id' => $vendor_data->country_id,
                'city_id' => $vendor_data->city_id,
                'activity_name' => $vendor_data->activity_name,
                'currency' => $vendor_data->currency,
                'country_code' => $vendor_data->country_code,
                'mobile' => $vendor_data->mobile,
                'activity_id' => $vendor_data->activity_id,
                'is_multi_store' => $vendor_data->is_multi_store,
                'commercial_registration_number' => $vendor_data->commercial_registration_number,
                'municipal_license_number' => $vendor_data->municipal_license_number,
                'tax_number' => $vendor_data->tax_number,
                'has_block' => $vendor_data->has_block,
                'has_device_block' => $vendor_data->has_device_block,
                'block_title' => $vendor_data->block_title,
                'block_reason' => $vendor_data->block_reason,
                'is_take_free_plan' => $vendor_data->is_take_free_plan,
                'is_active_plan' => $vendor_data->is_active_plan == 1 ? 1 : 0,
            ];
        } else {
            $employee = VendorEmployee::where('email', $request->email)
                ->whereHas('vendor', function ($q) use ($request) {
                    $q->where('account_number', $request->account_number);
                })->first();
            if (!Hash::check($request->password, $employee->password))
                return $this->errorResponse(__('msg.password_error', [], $this->lang_code), 400);
            if (isset($employee->vendor) && $employee->vendor->status == 0)
                return $this->errorResponse(__('msg.account_not_active', [], $this->lang_code), 403);
            $employee->is_web = $request->is_web;
            $employee->save();

            $token = auth('vendor_employee')->login($employee);
            $employee_data = auth('vendor_employee')->user();
            $data = [
                'first_name' => $employee_data->name,
                'email' => $employee_data->email,
                'mobile' => $employee_data->mobile,
                'is_web' => $employee_data->is_web,
                'lang' => $employee_data->lang,
                'period_id' => $employee_data->period_id,
                'currency' => $employee_data->currency,
                'account_number' => $employee_data->vendor->account_number,
                'is_take_free_plan' => $employee_data->vendor->is_take_free_plan,
                'is_active_plan' => $employee_data->vendor->is_active_plan == 1 ? 1 : 0,
                'family_name' => '',
                'status' => '',
                'country_id' => '',
                'city_id' => '',
                'activity_name' => '',
                'country_code' => '',
                'activity_id' => '',
                'is_multi_store' => '',
                'commercial_registration_number' => '',
                'municipal_license_number' => '',
                'tax_number' => '',
                'has_block' => '',
                'has_device_block' => '',
                'block_title' => '',
                'block_reason' => '',
            ];
        }

        return response()->json([
            'status' => 200,
            'msg' => __('msg.success_login', [], $this->lang_code),
            'token' => $token,
            'data' => $data
        ]);
    }

    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|unique:vendors|unique:vendor_employees',
            'password' => 'required',
        ]);

        if ($validator->fails())
            return $this->errorResponse($validator->errors()->first(), 400);

        $vendor = Vendor::create([
            'first_name' => $request->first_name,
            'family_name' => $request->family_name,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'country_id' => $request->country_id,
            'city_id' => $request->city_id,
            'activity_name' => $request->activity_name,
            'activity_id' => $request->activity_id,
            'currency' => $request->currency,
            'commercial_registration_number' => $request->commercial_registration_number,
            'municipal_license_number' => $request->municipal_license_number,
            'tax_number' => $request->tax_number,
            'account_number' => $this->genrateAccountNmumber(),
            'status' => 0,
            'active_code' => rand(100000, 999999),
            'password' => Hash::make($request->password)
        ]);
        $branch = new Branch();
        $branch->vendor_id = $vendor->id;
        $branch->name_ar = 'فرع 1';
        $branch->name_en = 'Branch 1';
        $branch->code = 'B-' . str_pad(1, 2, "0", STR_PAD_LEFT);
        $branch->mobile = $vendor->mobile;
        $branch->status = 1;
        $branch->is_free = 1;
        $branch->save();

        $store = new StoreHouse();
        $store->vendor_id = $vendor->id;
        $store->name_ar = 'مستودع 1';
        $store->name_en = 'Store house 1';
        $store->number = 'SH-1';
        $store->branches = 1;
        $store->status = 1;
        $store->is_free = 1;
        $store->save();
        $token = auth('vendor')->login($vendor);

        $name = $vendor->first_name . ' ' . $vendor->family_name;
        Mail::send(['html' => 'emails.verify_account'], ['name' => $name, 'code' => $vendor->active_code], function ($message) use ($vendor) {
            $message->from('no-replay@nqde.net', 'nqde.net');
            $message->subject('Active nqde account');
            $message->to($vendor->email);
        });


        $data = [
            'first_name' => $vendor->first_name,
            'family_name' => $vendor->family_name,
            'email' => $vendor->email,
            'status' => $vendor->status,
            'account_number' => $vendor->account_number,
            'country_id' => $vendor->country_id,
            'city_id' => $vendor->city_id,
            'activity_name' => $vendor->activity_name,
            'currency' => $vendor->currency,
            'country_code' => $vendor->country_code,
            'mobile' => $vendor->mobile,
            'activity_id' => $vendor->activity_id,
            'is_multi_store' => $vendor->is_multi_store,
            'commercial_registration_number' => $vendor->commercial_registration_number,
            'municipal_license_number' => $vendor->municipal_license_number,
            'tax_number' => $vendor->tax_number,
            'has_block' => $vendor->has_block,
            'has_device_block' => $vendor->has_device_block,
            'block_title' => $vendor->block_title,
            'block_reason' => $vendor->block_reason,
            'is_take_free_plan' => $vendor->is_take_free_plan,
            'is_active_plan' => $vendor->is_active_plan == 1 ? 1 : 0,
        ];

        return response()->json([
            'status' => 200,
            'msg' => __('msg.success_register', [], $this->lang_code),
            'token' => $token,
            'data' => $data,
        ]);
    }

    public function forgetPassword(Request $request)
    {
        $vendor = Vendor::where('email', $request->email)->where('account_number', $request->account_number)->first();
        if (!$vendor)
            return $this->errorResponse(__('msg.user_not_found', [], $this->lang_code), 400);

        $password = uniqid();
        $vendor->update(['password' => bcrypt($password)]);

        $name = $vendor->first_name . ' ' . $vendor->family_name;
        Mail::send(['html' => 'emails.restore_password'], ['name' => $name, 'password' => $password, 'vendor' => $vendor],
            function ($message) use ($vendor) {
                $message->from('no-replay@nqde.net', 'nqde.net');
                $message->subject('Restore nqde account');
                $message->to($vendor->email);
            });

        return $this->successResponse(__('msg.success_email_send', [], $this->lang_code));

    }

    public function verifyAccount(Request $request)
    {
        $vendor = Vendor::find(vendor()->id);

        if (!$vendor)
            return $this->errorResponse(__('msg.user_not_found', [], $this->lang_code), 400);

        if ($vendor->active_code == $request->get('code')) {
            $vendor->update(['status' => 1]);
            return $this->successResponse(__('msg.success_active_user', [], $this->lang_code));
        } else {
            return $this->errorResponse(__('msg.error_active_code', [], $this->lang_code), 400);
        }
    }

    public function resendActiveCode()
    {
        $vendor = Vendor::find(vendor()->id);
        if ($vendor)
            $vendor->update(['active_code' => rand(100000, 999999)]);
        $name = $vendor->first_name . ' ' . $vendor->family_name;
        Mail::send(['html' => 'emails.verify_account'], ['name' => $name, 'code' => $vendor->active_code], function ($message) use ($vendor) {
            $message->from('no-replay@nqde.net', 'nqde.net');
            $message->subject('Active nqde account');
            $message->to($vendor->email);
        });
        return $this->successResponse(__('msg.success_send_code', [], $this->lang_code));
    }

    public function logout()
    {
        auth('vendor')->logout();
        return $this->successResponse(__('msg.success_send_code', [], $this->lang_code));
    }

    protected function getCode($length)
    {
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            if ($i == 0) $result .= mt_rand(1, 9);
            else $result .= mt_rand(0, 9);
        }
        return $result;
    }

    protected function genrateAccountNmumber()
    {
        do {
            $code = $this->getCode(6);
            $data = Vendor::where('account_number', $code)->first();
            if (!$data) return $code;
        } while (true);
    }
}
