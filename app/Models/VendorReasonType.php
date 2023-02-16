<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorReasonType
{
    const CANCELLATION_RETURN = 1;
    const AMOUNT_UPDATE= 2;
    const BOX_OPERACTION= 3;
    const EXPENSES= 4;


    const statuses = [
        self::CANCELLATION_RETURN => [
            'id' => self::CANCELLATION_RETURN,
            'code' => 'CANCELLATION_RETURN',
        ],
        self::AMOUNT_UPDATE => [
            'id' => self::AMOUNT_UPDATE,
            'code' => 'AMOUNT_UPDATE',
        ],
        self::BOX_OPERACTION => [
            'id' => self::BOX_OPERACTION,
            'code' => 'BOX_OPERACTION',
        ],
        self::EXPENSES => [
            'id' => self::EXPENSES,
            'code' => 'EXPENSES',
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

