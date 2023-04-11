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

function user()
{
    return auth()->guard('web')->user();
}

function vendor()
{
    return auth()->guard('vendor')->user();
}

function vendor_employee()
{
    return auth()->guard('vendor_employee')->user();
}

function upload_vendor_file($image, $folder, $file, $modal, $vendor_id, $mediable_id, $type = null)
{
    if ($file && $file->file_path) {
        $filename = public_path() . '' . $file;
        File::delete($filename);
    }
    $image_name = $image->hashName();
    $image->move(public_path('/uploads/' . $folder . "/"), $image_name);
    $filePath = "/uploads/" . $folder . "/" . $image_name;
    $vendor_file = new \App\Models\VendorMedia();
    $vendor_file->vendor_id = $vendor_id;
    $vendor_file->mediable_type = $modal;
    $vendor_file->file_name = $image->getClientOriginalName();
    $vendor_file->mediable_id = $mediable_id;
    $vendor_file->file_path = $filePath;
    $vendor_file->type = $type;
    $vendor_file->save();

    return $filePath;
}

function vendor_data()
{
    if (auth()->guard('vendor')->check())
        $vendor = auth()->guard('vendor')->user();
     elseif (auth()->guard('vendor_employee')->check())
        $vendor = auth()->guard('vendor_employee')->user();

    return [
        'id' => $vendor->id ? $vendor->id : 0,
        'first_name' => $vendor->first_name ? $vendor->first_name : '',
        'family_name' => $vendor->family_name ? $vendor->family_name : '',
        'email' => $vendor->email ? $vendor->email : '',
        'status' => $vendor->status,
        'account_number' => $vendor->account_number ? $vendor->account_number : '',
        'country_id' => $vendor->country_id ? $vendor->country_id : 0,
        'city_id' => $vendor->city_id ? $vendor->city_id : 0,
        'activity_name' => $vendor->activity_name ? $vendor->activity_name : 0,
        'currency' => $vendor->currency ? $vendor->currency : 0,
        'country_code' => $vendor->country_code ? $vendor->country_code : 0,
        'mobile' => $vendor->mobile ? $vendor->mobile : 0,
        'activity_id' => (integer)$vendor->activity_id,
        'is_multi_store' => (integer)$vendor->is_multi_store,
        'commercial_registration_number' => $vendor->commercial_registration_number ? $vendor->commercial_registration_number : '',
        'municipal_license_number' => $vendor->municipal_license_number ? $vendor->municipal_license_number : '',
        'tax_number' => $vendor->tax_number ? $vendor->tax_number : '',
        'has_block' => $vendor->has_block,
        'has_device_block' => $vendor->has_device_block,
        'block_title' => $vendor->block_title,
        'block_reason' => $vendor->block_reason,
        'is_take_free_plan' => $vendor->is_take_free_plan == 1 ? 1 : 0,
        'is_active_plan' => $vendor->is_active_plan == 1 ? 1 : 0,
        'active_plan' => $vendor->active_plan ? $vendor->active_plan : null,
    ];

}
