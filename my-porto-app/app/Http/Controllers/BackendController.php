<?php

namespace App\Http\Controllers;
use App\Utils\DokuUtils;
use Illuminate\Http\Request;

class BackendController extends Controller
{
    public function DokuCheckout(Request $request)
    {
        $clientName = 'test';
        $clientId = env("DOKU_CLIENT_ID");
        $secretKey = env("DOKU_SECRET_KEY");
        $requestId = time();
        date_default_timezone_set('UTC');
        $path = '/checkout/v1/payment';
        $url = 'https://api-sandbox.doku.com' . $path;
        $timestamp      = date('Y-m-d\TH:i:s\Z');
        $invoice = 'ASHDDQ-' . time();
        $callback = 'http://localhost:8000/hasiltransaksi?invoice=' . $invoice;
        $Body = array(
            'order' =>
            array(
                'amount' => 100000,
                'invoice_number' => $invoice,
                'callback_url' => $callback,
            ),
            'payment' =>
            array(
                'payment_due_date' => 120
            ),
            'customer' =>
            array(
                'id' => 'customer',
                'name' => 'customer ashddq',
                'email' => 'customer@ashddq.online'
            )
        );
        $digest = base64_encode(hash('sha256', json_encode($Body), true));
        \Log::info(json_encode($Body));
        $abc = "Client-Id:" . $clientId . "\n" . "Request-Id:" . $requestId . "\n" . "Request-Timestamp:" . $timestamp . "\n" . "Request-Target:" . $path . "\n" . "Digest:" . $digest;
        \Log::info($abc);
        $signature = base64_encode(hash_hmac('sha256', $abc, $secretKey, true));
        \Log::info('HMACSHA256='.$signature);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($Body));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Client-Id:' . $clientId,
            'Request-Id:' . $requestId,
            'Request-Timestamp:' . $timestamp,
            'Signature:' . "HMACSHA256=" . $signature,
        ));

        $response = curl_exec($ch);
        curl_close($ch);
        \Log::info($response);
        var_dump($response);
    }

    public function generateCheckout()
    {
        var_dump(DokuUtils::generateCheckoutUrl('1234', 'alif', 'm.alif@doku.com', 10000));
    }
}
