<?php

namespace App\Models;

class VendorStatus
{
    const NEW = 1;
    const ACCEPTED = 2;
    const COMPLETED = 3;
    const CANCELED = 4;
    const EXPIRED = 5;

    const PAID = 6;
    const SHIPMENT_RECEIVED = 7;
    const SHIPMENT_DELIVERED = 8;

    const statuses = [
        self::NEW => [
            'id' => self::NEW,
            'code' => 'new',
        ],
        self::ACCEPTED => [
            'id' => self::ACCEPTED,
            'code' => 'accepted',
        ],
        self::COMPLETED => [
            'id' => self::COMPLETED,
            'code' => 'completed',
        ],
        self::CANCELED => [
            'id' => self::CANCELED,
            'code' => 'canceled',
        ],
        self::EXPIRED => [
            'id' => self::EXPIRED,
            'code' => 'expired',
        ],
        self::PAID => [
            'id' => self::PAID,
            'code' => 'paid',
        ],
        self::SHIPMENT_RECEIVED => [
            'id' => self::SHIPMENT_RECEIVED,
            'code' => 'shipment_received',
        ],
        self::SHIPMENT_DELIVERED => [
            'id' => self::SHIPMENT_DELIVERED,
            'code' => 'shipment_delivered',
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
