<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorPaymentMethodeType
{
    const CASH = 1;
    const HOUSE_ACCOUNT = 2;
    const GIFT_CARD = 3;


    const statuses = [
        self::CASH => [
            'id' => self::CASH,
            'code' => 'cash',
        ],
        self::HOUSE_ACCOUNT => [
            'id' => self::HOUSE_ACCOUNT,
            'code' => 'house_account',
        ],
        self::GIFT_CARD => [
            'id' => self::GIFT_CARD,
            'code' => 'gift_card',
        ],


    ];

    public static function all(array $without = null)
    {
        $methods = self::statuses;

        if($without){
            foreach ($without as $value){
                unset($methods[$value]);
            }
        }

        // add labels to array
        $methods = array_map(function($item){

            $item['label'] = __('const.'.$item['code']);

            if(isset($item['children'])){

                $children = array_map(function ($item){
                    $item['label'] = __('const.'.$item['code']);
                    return $item;
                },$item['children']);

                $item['children'] = array_values($children);

            }

            return $item;

        },$methods);

        return collect(array_values($methods));
    }

    public static function find($id)
    {
        return self::all()->where('id',$id)->first();
    }

}

