<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $apiKey = "DEV-M5UUd2LqMpoejcUbHIzzoKl3Iq1bMoTt3xmzUDjk";
    protected $merchantCode = "T32426";
    protected $privateKey = "yZpob-TXdyg-krir0-EeEbz-X5P3B";
    protected $channel = "QRIS";

    public function getPayments(Request $request)
    {
        $account = $request->attributes->get('accountDetail');
        $result = \App\Models\Payment::where('user_id', $account['uuid'])->get();

        return response()->json([
            'message' => 'Success',
            'data' => $result
        ]);
    }

    public function tripay(Request $request)
    {
        $account = $request->attributes->get('accountDetail');
        $amount = $request->input("amount");
        $day = $request->input("day");
        $apiKey = $this->apiKey;
        $privateKey = $this->privateKey;
        $merchantCode = $this->merchantCode;
        $merchantRef = 'INV345678';
        $method = $this->channel;

        $data = [
            'method' => $method,
            'merchant_ref' => $merchantRef,
            'amount' => $amount,
            'customer_name' => $account['name'],
            'customer_email' => $account['email'],
            'order_items' => [
                [
                    'sku' => 'CAT-DAILY',
                    'name' => 'Subscription Daily',
                    'price' => 2000,
                    'quantity' => $day,
                ],
            ],
            'return_url' => env('ACCOUNT_URL'),
            'expired_time' => (time() + (24 * 60 * 60)), // 24 jam
            'signature' => hash_hmac('sha256', $merchantCode . $merchantRef . $amount, $privateKey)
        ];

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_FRESH_CONNECT => true,
            CURLOPT_URL => env('TRIPAY_URL'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $apiKey],
            CURLOPT_FAILONERROR => false,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
        ]);

        $response = curl_exec($curl);
        $response = json_decode($response);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($httpCode == 200) {
            \App\Models\Payment::create([
                'user_id' => $account['uuid'],
                'reference' => $response->data->reference,
                'payment_name' => $response->data->payment_name,
                'checkout_url' => $response->data->checkout_url,
                'status' => $response->data->status,
                'expired_time' => $response->data->expired_time,
                'day' => $day,
            ]);
        }

        curl_close($curl);

        return response()->json([
            'message' => 'Success',
            'data' => $response
        ]);
    }

    public function tripayCallback(Request $request)
    {
        $callbackSignature = $request->server('HTTP_X_CALLBACK_SIGNATURE');
        $json = $request->getContent();
        $signature = hash_hmac('sha256', $json, $this->privateKey);

        // log request callback tripay
        Log::info('Receive tripay callback: ' . $json);
        
        if ($signature !== (string) $callbackSignature) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid signature',
            ]);
        }

        if ('payment_status' !== (string) $request->server('HTTP_X_CALLBACK_EVENT')) {
            return response()->json([
                'success' => false,
                'message' => 'Unrecognized callback event, no action was taken',
            ]);
        }

        $data = (array) json_decode($json);

        $reference = $data["reference"];
        $status = $data["status"];

        if ($status == 'PAID') {
            $user = \App\Models\Payment::where('reference', $reference)->pluck('user_id', 'day');

            \App\Models\Payment::where('uuid', $user->user_id)
                ->update([
                    'active_until' => DB::raw("DATE_ADD(active_until, INTERVAL ? DAY)", [$user->day])
                ]);
        }

        return response()->json([
            'message' => 'Success',
        ]);
    }
}
