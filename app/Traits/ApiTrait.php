<?php

namespace App\Traits;

use Illuminate\Support\Facades\File;

trait ApiTrait
{
    public function distance($lat1, $lon1, $lat2, $lon2, $unit)
    {

        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
            return ($miles * 1.609344);
        } else if ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }

    public function errorResponse($msg, $code = 401)
    {
        return response()->json([
            'status' => $code,
            'msg' => $msg,
        ],$code);
    }

    public function successResponse($msg, $code = 200)
    {
        return response()->json([
            'status' => $code,
            'msg' => $msg,
        ],$code);
    }

    public function dataResponse($msg, $data, $code = 200)
    {
        return response()->json([
            'status' => $code,
            'msg' => $msg,
            'data' => $data,
        ],$code);
    }

    public function send_notification($to, $title, $text)
    {

        $data = [
            "to" => $to,
            "notification" => [
                "title" => $title,
                'body' => $text,
            ],
            "data" => [
                "title" => $title,
                'body' => $text,
                "click_action" => "FLUTTER_NOTIFICATION_CLICK",
                'type' => 'public'
            ],
        ];
        $dataString = json_encode($data);
        $headers = [
            'Authorization: key=AAAAPlAhL50:APA91bGtyvRigEDlx213szdbUcx1urQrCvMw_2eISA68qKtijr1peishrUiF3moJ2JxJiXtxLRC8v5-QdGopUpKLsVq2IEn6WA9oIWTkL3g2PvB6O41jJX8QYBbAa6qDC--vVm5shuVr',
            'Content-Type: application/json'
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
        $result = curl_exec($ch);
        return true;
    }
}
