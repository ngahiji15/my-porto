<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ReproduceController extends Controller
{
    public function getPaymentUrl(Request $request)
    {
        $base_url = 'https://api-sandbox.doku.com';//'http://127.0.0.1:8000/';// // Replace with the actual base URL
        $endpoints = '/checkout/v1/payment';//'api/getBody';// // Add the specific endpoint path if needed
        $headerString = '';
        foreach($request->header() as $key => $value) {
            $headerString .= $key . ': ' . implode(', ', $value) . ', ';
        }
        $headerString = rtrim($headerString, ', ');
        $requestData = $request->json()->all();
        \Log::info('Request Header: ' . $headerString);
        \Log::info(json_encode($requestData));

        $response = Http::withHeaders([
            'Client-Id' => $request->header('Client-Id'),
            'Request-Id' => $request->header('Request-Id'),
            'Request-Timestamp' => $request->header('Request-Timestamp'),
            'Signature' => $request->header('Signature'),
            'content-type' => $request->header('content-type')
        ])->post($base_url . $endpoints, $request->json()->all());

        return $response->json();
    }

    public function getBody(Request $request)
    {
        $headerString = '';
        foreach($request->header() as $key => $value) {
            $headerString .= $key . ': ' . implode(', ', $value) . ', ';
        }
        $headerString = rtrim($headerString, ', ');
        \Log::info('============ ini getbody =========');
        \Log::info('Request Header: ' . $headerString);
        \Log::info($request->all());
        $response = 'ok';

        return $response;
    }

    public function inquirybni()
    {
        $notificationHeader = getallheaders();
        $notificationBody = file_get_contents('php://input');
        \Log::info('=== DIPC VA BNI NON SNAP ===');
        $bodynotif = json_decode($notificationBody, true);
        $vanum = $bodynotif['virtual_account_info']['virtual_account_number'];
        $merchantunique = $bodynotif['virtual_account_info']['merchant_unique_reference'];
        $binbilling = $bodynotif['virtual_account_info']['identifier'][0]['value'];
        $clientId = 'BRN-0201-1700032219790';
        $secretKey = 'SK-Ho5DslJCYCuwZMOYUZwj';
        date_default_timezone_set('UTC');
        $timestamp      = date('Y-m-d\TH:i:s\Z');
        $requestid = $notificationHeader['Request-Id'];

        $name = 'Alif';
        $amount = 150000;
        $inv = 'INV-' . time();;
        $path = '/api/inquiry-bni';

        $Body = array(
            'order' =>
            array(
                'invoice_number' => $inv,
                'amount' => $amount
            ),
            'virtual_account_info' =>
            array(
                'virtual_account_number' => $vanum,
                'merchant_unique_reference' => $merchantunique,
                'info' => 'Thanks',
                'identifier' => [
                    array(
                        'name' => 'BILLING_NUMBER',
                        'value' => $binbilling
                    )
                ]
            ),
            'virtual_account_inquiry' =>
            array(
                'status' => 'success'
            ),
            'customer' =>
            array(
                'name' => $name,
                'email' => '',
                'phone' => ''
            ),
            'additional_info' =>
            array(
                'addl_label_1' => '',
                'addl_label_2' => '',
                'addl_label_3' => '',
                'addl_value_1' => '',
                'addl_value_2' => '',
                'addl_value_3' => '',
                'addl_label_1_en' => '',
                'addl_label_2_en' => '',
                'addl_label_3_en' => '',
                'addl_value_1_en' => '',
                'addl_value_2_en' => '',
                'addl_value_3_en' => ''
            )
        );
        //$digest = base64_encode(hash('sha256', json_encode($Body, JSON_PRETTY_PRINT), true));
        $digest = base64_encode(hash('sha256', json_encode($Body), true));
        $abc = "Client-Id:" . $clientId . "\n" . "Request-Id:" . $requestid . "\n" . "Response-Timestamp:" . $timestamp . "\n" . "Request-Target:" . $path . "\n" . "Digest:" . $digest;
        \Log::info('=== SIGNATURE COMPONENT ===');
        $signature = base64_encode(hash_hmac('sha256', $abc, $secretKey, true));
        $finalsignature = "HMACSHA256=" . $signature;
        $log  = "Signature Generate: " . $finalsignature . PHP_EOL .
            "Digest: " . $digest . PHP_EOL .
            "target path: " . $path . PHP_EOL .
            "timestamp: " . $timestamp . PHP_EOL .
            "requestid: " . $inv . PHP_EOL .
            "Berhasil" . PHP_EOL .
            "-------------------------" . PHP_EOL;
        $response = response()->json($Body);
        $response ->header('Client-Id', $clientId)->header('Request-Id', $requestid)->header('Response-Timestamp', $timestamp)->header('Signature', $finalsignature);
        //$this->response->setJSON($notificationBody, 200);
        return $response;
    }
}
