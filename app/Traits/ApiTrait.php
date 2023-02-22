<?php

namespace App\Traits;

use App\Models\UsersCard;
use App\Models\VendorPlane;
use Illuminate\Support\Facades\Crypt;
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

    public function myfatorah_payment($request,$price)
    {

        $apiURL = 'https://apitest.myfatoorah.com';
        $apiKey = 'rLtt6JWvbUHDDhsZnfpAhpYk4dxYDQkbcPTyGaKp2TYqQgG7FGZ5Th_WD53Oq8Ebz6A53njUoo1w3pjU1D4vs_ZMqFiz_j0urb_BH9Oq9VZoKFoJEDAbRZepGcQanImyYrry7Kt6MnMdgfG5jn4HngWoRdKduNNyP4kzcp3mRv7x00ahkm9LAK7ZRieg7k1PDAnBIOG3EyVSJ5kK4WLMvYr7sCwHbHcu4A5WwelxYK0GMJy37bNAarSJDFQsJ2ZvJjvMDmfWwDVFEVe_5tOomfVNt6bOg9mexbGjMrnHBnKnZR1vQbBtQieDlQepzTZMuQrSuKn-t5XZM7V6fCW7oP-uXGX-sMOajeX65JOf6XVpk29DP6ro8WTAflCDANC193yof8-f5_EYY-3hXhJj7RBXmizDpneEQDSaSz5sFk0sV5qPcARJ9zGG73vuGFyenjPPmtDtXtpx35A-BVcOSBYVIWe9kndG3nclfefjKEuZ3m4jL9Gg1h2JBvmXSMYiZtp9MR5I6pvbvylU_PP5xJFSjVTIz7IQSjcVGO41npnwIxRXNRxFOdIUHn0tjQ-7LwvEcTXyPsHXcMD8WtgBh-wxR8aKX7WPSsT1O8d8reb2aR7K3rkV3K82K_0OgawImEpwSvp9MNKynEAJQS6ZHe_J_l77652xwPNxMRTMASk1ZsJL';

        $postFields = [
            //Fill required data
            'paymentMethodId' => '20',
            'InvoiceValue' => $price,
            'CallBackUrl' => 'https://nqde.net/success',
            'ErrorUrl' => 'https://nqde.net/faild',
        ];

        $data = executePayment($apiURL, $apiKey, $postFields);
        $paymentURL = $data->PaymentURL;
        $cardData = UsersCard::find($request->card_id);

        $cardInfo = [
            'PaymentType' => 'card',
            'Bypass3DS' => true,
            "SaveToken" => true,
            'Card' => [
                'Number' => $cardData && $cardData->card_number ? decrypt($cardData->card_number) : $request->card_number,
                'ExpiryMonth' => $cardData && $cardData->ex_month ? $cardData->ex_month : $request->ex_month,
                'ExpiryYear' => $cardData && $cardData->ex_year ? $cardData->ex_year : $request->ex_year,
                'SecurityCode' => $cardData && $cardData->cvv ? $cardData->cvv : $request->cvv,
                'CardHolderName' => $cardData && $cardData->holder_name ? $cardData->holder_name : $request->holder_name
            ]
        ];

        $directData = directPayment($paymentURL, $apiKey, $cardInfo);

        if (isset($directData->Status) && $directData->Status == 'SUCCESS') {
            if ($request->save_card == 1) {
                $card_num = $cardData && $cardData->card_number ? decrypt($cardData->card_number) : $request->card_number;
                $user_card = UsersCard::where('vendor_id', vendor()->id)->where('last_4number',substr($card_num, -4))->first();
                if (!$user_card && !$cardData) {
                    if ($request->is_default == 1)
                        UsersCard::where('vendor_id', vendor()->id)->update(['is_default' => 0]);
                    $user_card = new UsersCard();
                    $user_card->card_number = encrypt($request->card_number);
                    $user_card->ex_month = $request->ex_month;
                    $user_card->ex_year = $request->ex_year;
                    $user_card->cvv = $request->cvv;
                    $user_card->holder_name = $request->holder_name;
                    $user_card->brand = $directData->CardInfo->Brand;
                    $user_card->payment_id = $directData->PaymentId;
                    $user_card->last_4number = substr($card_num, -4);
                    $user_card->is_default = $request->is_default;
                    $user_card->vendor_id = vendor()->id;
                    $user_card->save();
                }
            }
        }
        return $directData;
    }
}
