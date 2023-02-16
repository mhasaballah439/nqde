<?php

namespace App\Http\Controllers\Vendor\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityType;
use App\Models\Country;
use App\Models\Currency;
use App\Traits\ApiTrait;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    use ApiTrait;
    var $lang_code;

    public function __construct()
    {
        $this->lang_code = \request()->get('lang') ? \request()->get('lang') : get_default_languages();
    }
    public function countries(){
        $countries = Country::Active()->get();
        $data = $countries->map(function ($country){
           return [
                'id' => $country->id,
               'name' => $country->name($this->lang_code)
           ] ;
        });
        return $this->dataResponse(__('msg.data_success_get',[],$this->lang_code),$data);
    }

    public function countryCities(Request $request){
        $country = Country::find($request->country_id);

        $data = isset($country->cities) && count($country->cities) > 0 ? $country->cities->map(function ($city){
            return [
                'id' => $city->id,
                'name' => $city->name($this->lang_code)
            ] ;
        }) : [];

        return $this->dataResponse(__('msg.data_success_get',[],$this->lang_code),$data);
    }

    public function currencies(){
        $currencies = Currency::Active()->get();
        $data = $currencies->map(function ($data){
            return [
                'id' => $data->id,
                'name' => $data->name,
                'code' => $data->code
            ] ;
        });
        return $this->dataResponse(__('msg.data_success_get',[],$this->lang_code),$data);
    }

    public function activetiesTypes(){
        $cativites = ActivityType::Active()->get();
        $data = $cativites->map(function ($item){
            return [
                'id' => $item->id,
                'name' => $item->name($this->lang_code)
            ] ;
        });
        return $this->dataResponse(__('msg.data_success_get',[],$this->lang_code),$data);
    }
}
