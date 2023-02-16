<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;


function get_default_languages()
{
    return Config::get('app.locale');
}


//function admin(){
//    return auth()->guard('admin')->user();
//}

function user(){
    return auth()->guard('web')->user();
}
function vendor(){
    return auth()->guard('vendor')->user();
}

function vendor_employee(){
    return auth()->guard('vendor_employee')->user();
}

function upload_file($image,$folder,$file = null){
    if ($file != null){
        $filename = public_path() . '' . $file;
        File::delete($filename);
    }
    $image_name = $image->hashName();
    $image->move(public_path('/uploads/'.$folder."/"), $image_name);
    $filePath = "/uploads/".$folder."/". $image_name;

    return $filePath;
}

function vendor_data(){
    if (auth()->guard('vendor')->check()){
        $data = [
            'first_name' => auth()->guard('vendor')->user()->first_name,
            'family_name' => auth()->guard('vendor')->user()->family_name,
            'email' => auth()->guard('vendor')->user()->email,
            'status' => auth()->guard('vendor')->user()->status,
            'account_number' => auth()->guard('vendor')->user()->account_number,
            'country_id' => auth()->guard('vendor')->user()->country_id,
            'city_id' => auth()->guard('vendor')->user()->city_id,
            'activity_name' => auth()->guard('vendor')->user()->activity_name,
            'currency' => auth()->guard('vendor')->user()->currency,
            'country_code' => auth()->guard('vendor')->user()->country_code,
            'mobile' => auth()->guard('vendor')->user()->mobile,
            'activity_id' => auth()->guard('vendor')->user()->activity_id,
            'is_multi_store' => auth()->guard('vendor')->user()->is_multi_store,
            'commercial_registration_number' => auth()->guard('vendor')->user()->commercial_registration_number,
            'municipal_license_number' => auth()->guard('vendor')->user()->municipal_license_number,
            'tax_number' => auth()->guard('vendor')->user()->tax_number,
            'has_block' => auth()->guard('vendor')->user()->has_block,
            'has_device_block' => auth()->guard('vendor')->user()->has_device_block,
            'block_title' => auth()->guard('vendor')->user()->block_title,
            'block_reason' => auth()->guard('vendor')->user()->block_reason,
            'is_take_free_plan' => auth()->guard('vendor')->user()->is_take_free_plan == 1 ? 1 : 0,
            'is_active_plan' => auth()->guard('vendor')->user()->is_active_plan == 1 ? 1 : 0,
        ];
    }else{
        $employee_data = auth()->guard('vendor_employee')->user();
        $data = [
            'first_name' => $employee_data->name,
            'email' => $employee_data->email,
            'mobile' => $employee_data->mobile,
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

    return $data;
}
