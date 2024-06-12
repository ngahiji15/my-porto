<?php

namespace App\Http\Controllers;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Utils\DokuUtils;
use Illuminate\Support\Facades\Validator;
use App\Models\Token;
use Carbon\Carbon;
use App\Utils\ControllerUtils;

class DokuController extends Controller
{
    private function generateCustomAccessToken()
    {
        $data = [
            'exp' => time() + 900, 
            'nbf' => time(), 
            'iss' => 'ASHDDQ', 
            'iat' => time()
        ];
        $token = base64_encode(json_encode($data));
        return $token;
    }

    public function generateAccessToken(Request $request)
    {   
        \Log::info('============ Start Generate Token ============');
        $requestSignature = $request->header('X-SIGNATURE');
        $requestTimestamp = $request->header('X-TIMESTAMP');
        $requestClientKey = $request->header('X-CLIENT-KEY');
        $headerString = '';
        foreach($request->header() as $key => $value) {
            $headerString .= $key . ': ' . implode(', ', $value) . ', ';
        }
        $headerString = rtrim($headerString, ', ');
        \Log::info('Request Header: ' . $headerString);
        $requestData = $request->json()->all();
        \Log::info('Request Body : ' . json_encode($requestData));
        $grantType = $requestData['grantType'] ?? null;
        $additionalInfo = $requestData['additionalInfo'] ?? null;
        $accessToken = $this->generateCustomAccessToken();
        $newSignature = DokuUtils::generateSignatureAsymmetric($requestTimestamp);
        $newtimestamp = DokuUtils::generateTimestamp();
        $clientId = env('DOKU_CLIENT_ID');
        $resultSignature = DokuUtils::validationSignatureAsymmetric($requestTimestamp, $requestSignature);
        if($resultSignature == 'Signature Match'){
            $expectedResult = $requestSignature;
        }else{
            $expectedResult = 'error';
        }
        

        $validator = Validator::make([
            'X-TIMESTAMP' => $requestTimestamp,
            'X-CLIENT-KEY' => $requestClientKey,
            'X-SIGNATURE' => $requestSignature
        ], [
            'X-TIMESTAMP' => 'required|date_format:Y-m-d\TH:i:sP',
            'X-CLIENT-KEY' => 'required|in:'.env('DOKU_CLIENT_ID'),
            'X-SIGNATURE' => 'required|in:'.$expectedResult
        ]);

        if ($validator->fails()) {
            $errorMessages = $validator->errors()->all();
            \Log::info('Error messages: ' . json_encode($errorMessages));
            \Log::info('X-CLIENT-KEY local : '. $clientId);

            $responseCode = '4017300';
            $responseMessage = 'Unauthorized. Unknown Client';
            if (in_array('The selected x- s i g n a t u r e is invalid.', $errorMessages)) {
                $responseMessage = 'Unauthorized. Signature';
            }
            $newTimestamp = DokuUtils::generateTimestamp();

            return response()->json([
                'responseCode' => $responseCode,
                'responseMessage' => $responseMessage,
            ], 401)
            ->header('X-CLIENT-KEY', $requestClientKey)
            ->header('X-TIMESTAMP', $newTimestamp);
            \Log::info('Response sent with status code 401', [
                'responseCode' => $responseCode,
                'responseMessage' => $responseMessage,
                'X-CLIENT-KEY' => $requestClientKey,
                'X-TIMESTAMP' => $newTimestamp,
            ]);
        }else{

        $response = response()->json([
            'responseCode' => '2007300',
            'responseMessage' => 'Successful',
            'accessToken' => $accessToken,
            'tokenType' => 'Bearer',
            'expiresIn' => 900,
            'additionalInfo' => ''
        ]);
        $response->header('X-CLIENT-KEY', $requestClientKey);
        $response->header('X-TIMESTAMP', $newtimestamp);

        \Log::info('Response prepared:', [
            'responseCode' => '2007300',
            'responseMessage' => 'Successful',
            'accessToken' => $accessToken,
            'tokenType' => 'Bearer',
            'expiresIn' => 900,
            'additionalInfo' => ''
        ]);

        return $response;
        }
    }

    public function signatureChecker(Request $request)
    {
        $requestSignature = $request->header('X-SIGNATURE');
        $requestTimestamp = $request->header('X-TIMESTAMP');
       
        //call signature function
        $resultSignature = DokuUtils::checkerSignatureAsymmetric($requestTimestamp, $requestSignature);
        echo $resultSignature;
    }

    public function inquiryDanamon(Request $request)
    {
        \Log::info('============ Start Inquiry Process ============');
        $requestSignature = $request->header('X-SIGNATURE');
        $requestTimestamp = $request->header('X-TIMESTAMP');
        $requestClientId = $request->header('X-PARTNER-ID');
        $requestAuthorization = $request->header('Authorization');
        $requestExternalId = $request->header('X-EXTERNAL-ID');
        $requestData = $request->json()->all();
        $prefixNumber = $requestData['partnerServiceId'];
        if (strlen($prefixNumber) < 8) {
            $prefixNumber = str_pad($prefixNumber, 8, ' ', STR_PAD_LEFT);
        }
        if (strpos($prefixNumber, '8922') !== false) {
            $amount = "0.00";
        } else {
            $amount = "10,000.00"; 
        }
        $accountNumber = $requestData['customerNo'];
        $virtualAccountNumber = $prefixNumber . $accountNumber;
        $headerString = '';
        foreach($request->header() as $key => $value) {
            $headerString .= $key . ': ' . implode(', ', $value) . ', ';
        }
        $headerString = rtrim($headerString, ', ');
        \Log::info('Request Header: ' . $headerString);
        \Log::info('Request Body : ' . json_encode($requestData));
        //prepare body
        $responseBody = array(
            'responseCode' => '2002400',
            'responseMessage' => 'Successful',
            'virtualAccountData' => array(
                'partnerServiceId' => $prefixNumber,
                'customerNo' => $accountNumber,
                'virtualAccountNo' => $virtualAccountNumber,
                'virtualAccountName' => 'Ashddq Customer',
                'virtualAccountEmail' => 'customer@ashddq.xyz',
                'virtualAccountPhone'=> '08123456789',
                'trxId' => DokuUtils::generateRequestid(),
                'inquiryRequestId' => DokuUtils::generateRequestid(),
                'virtualAccountTrxType' => '2',
                'additionalInfo' => array(
                'virtualAccountConfig' => array(
                    'reusableStatus' => true
                    )
                ),
                'totalAmount' => array(
                'value' => $amount,
                'currency' => 'IDR'
                )
            ),
            'billDetails' => [
                array(
                    'billCode' => '01',
                    'billNo' => 'REF' . DokuUtils::generateRequestid(),
                    'billName' => 'CUSTOMER ASHDDQ',
                    'billShortName' => 'CUSTOMER',
                    'billDescription' => array(
                        'english' => 'PAYMENT AT ASHDDQ',
                        'indonesia' => 'PEMBAYARAN DI ASHDDQ'
                    ),
                    'billSubCompany' => '00001',
                    'billAmount' => array(
                            'value' => $amount,
                            'currency' => 'IDR'
                    ),
                    'additionalInfo' => array(
                            'id' => '',
                            'en' => ''
                    )
                )
            ],
            'freeTexts' => [
                array(
                    'english' => 'Thank you.',
                    'indonesia' => 'Terima kasih.'
                )
            ],
            'feeAmount' => array(
                'value' => '0',
                'currency' => 'IDR'
            ),
            'inquiryStatus' => 'SUCCESS',
            'inquiryReason'=> array(
                'english' => 'Success',
                'indonesia' => 'Sukses'
            ),
            'inquiryRequestId' => DokuUtils::generateRequestid(),
            'subCompany' => '',
            'virtualAccountTrxType' => '2',
            'additionalInfo' => array(
                'virtualAccountConfig' => array(
                    'reusableStatus' => true
                ),
                'trxId' => DokuUtils::generateRequestid(), 
            ),
            'totalAmount' => array(
                'value' => $amount,
                'currency' => 'IDR'
            )
        );
        \Log::info('Response Body : ' . json_encode($responseBody));
        $newTimestamp = DokuUtils::generateTimestamp();
        $newExtId = DokuUtils::generateRequestid();
        $digest = DokuUtils::generateDigest($responseBody);
        $path = '/api/snap/danamon-inquiry';
        $ClientId = env('DOKU_CLIENT_ID');
        $signature = DokuUtils::generateSignatureSymmetric($requestAuthorization, $digest, $newTimestamp, $path);
        $response = response()->json($responseBody);
        $response ->header('X-PARTNER-ID', $ClientId)->header('X-EXTERNAL-ID', $newExtId)->header('X-TIMESTAMP', $newTimestamp)->header('X-SIGNATURE', $signature);

        return $response;
    }

    public function generateQRIS(Request $request)
    {   
        $timestamp = DokuUtils::generateTimestamp();
        $signature = DokuUtils::generateSignatureAsymmetric($timestamp);
        return response()->json([
            'responseCode' => DokuUtils::generateTokenB2B($timestamp, $signature)
        ], 200);
    }

    public function notificationSnap(Request $request)
    {   
        \Log::info('============ Start Notification Process ============');
        $requestData = $request->json()->all();
        $requestBody = $request->all();
        $notificationBody = file_get_contents('php://input');
         //prepare data for update status snap
        $partnerServiceId = $requestData['partnerServiceId'] ?? null;
        $customerNo = $requestData['partnerServiceId'] ?? null;
        $virtualAccountNo = $requestData['virtualAccountNo'] ?? null;
        $virtualAccountName = $requestData['virtualAccountName'] ?? null;
        $paymentRequestId = $requestData['paymentRequestId'] ?? null;
        $invoiceSnap = $requestData['trxId'] ?? null;
        $paymentChannelSnap = $requestData['additionalInfo']['channel'] ?? null;
        //prepare data for update status non snap
        $invoice = $requestData['order']['invoice_number'] ?? null;
        $paymentCode = $requestData['virtual_account_info']['virtual_account_number'] ?? 
               ($requestData['card_payment']['response_code'] ?? 
               ($requestData['online_to_offline_info']['payment_code'] ?? 
               ($requestData['shopeepay_payment']['identifier'][0]['value'] ?? 
               ($requestData['wallet']['token_id'] ?? 
               ($requestData['ovo_payment']['response_code'] ?? 
               ($requestData['refund']['response_code'] ?? null))))));
        $paymentChannel = $requestData['channel']['id'] ?? null;
        //signature validation
        $requestSignature = $request->header('X-SIGNATURE') ?? null;
        switch ($requestSignature) {
            case null:
                $requestSignatureOld = $request->header('Signature');
                $requestTimestamp = $request->header('Request-Timestamp');
                $requestId = $request->header('Request-Id');
                $digest = DokuUtils::generateDigestOld($requestData);
                $hour = substr($requestTimestamp, 11, 2);
                $hour = str_pad($hour - 7, 2, '0', STR_PAD_LEFT);
                $newTimestamp = substr_replace($requestTimestamp, $hour, 11, 2);
                $newTimestamp = substr($newTimestamp, 0, -6) . 'Z';
                $path = "/api/v1/transfer-va/payment";
                $localSignature = 'HMACSHA256='.DokuUtils::generateSignatureOld($requestTimestamp, $requestId, $digest, $path);
                $headerString = '';
                foreach($request->header() as $key => $value) {
                    $headerString .= $key . ': ' . implode(', ', $value) . ', ';
                }
                $headerString = rtrim($headerString, ', ');
                \Log::info('Request Header: ' . $headerString);
                \Log::info('Request Body : ' . json_encode($requestData));
                \Log::info('Local Signature : ' . $localSignature);
                $validator = Validator::make([
                    'Signature' => $requestSignatureOld
                ], [
                    'Signature' => 'required|in:'.$localSignature
                ]);

                if ($validator->fails()) {
                    $errorMessages = $validator->errors()->all();
                    \Log::info('Error messages: ' . json_encode($errorMessages));

                    $responseCode = '4017300';
                    $responseMessage = 'Unauthorized. Unknown Client';
                    if (in_array('The selected x- s i g n a t u r e is invalid.', $errorMessages)) {
                        $responseMessage = 'Unauthorized. Signature';
                    }
                    $newTimestamp = DokuUtils::generateTimestamp();

                    return response()->json([
                        'responseCode' => $responseCode,
                        'responseMessage' => $responseMessage,
                    ], 401)
                    ->header('X-TIMESTAMP', $newTimestamp);
                    \Log::info('Response sent with status code 401', [
                        'responseCode' => $responseCode,
                        'responseMessage' => $responseMessage
                    ]);
                }else{
                $responseBody = array(
                    'responseCode' => '2002500',
                    'responseMessage' => 'Success'
                    );
                ControllerUtils::updatePaymentStatus($invoice, $paymentCode, $paymentChannel);
                \Log::info('Response Body : ' . json_encode($responseBody,JSON_UNESCAPED_SLASHES));
                $response = response()->json($responseBody);
                return $response;
                }
                break;
            deafult:;
                $requestAuthorization = $request->header('Authorization');
                $digest = DokuUtils::generateDigestJSON($requestData);
                $requestTimestamp = $request->header('X-TIMESTAMP');
                $hour = substr($requestTimestamp, 11, 2);
                $hour = str_pad($hour - 7, 2, '0', STR_PAD_LEFT);
                $newTimestamp = substr_replace($requestTimestamp, $hour, 11, 2);
                $newTimestamp = substr($newTimestamp, 0, -6) . 'Z';
                $requestClientKey = $request->header('X-PARTNER-ID');
                $path = "/api/v1/transfer-va/payment";
                $localSignature = DokuUtils::generateSignatureSymmetric($requestAuthorization, $digest, $requestTimestamp, $path);
                $headerString = '';
                foreach($request->header() as $key => $value) {
                    $headerString .= $key . ': ' . implode(', ', $value) . ', ';
                }
                $headerString = rtrim($headerString, ', ');
                \Log::info('Request Header: ' . $headerString);
                \Log::info('Request Body : ' . json_encode($requestData));
                \Log::info('Local Signature : ' . $localSignature);
                $validator = Validator::make([
                    'X-SIGNATURE' => $requestSignature
                ], [
                    'X-SIGNATURE' => 'required|in:'.$localSignature
                ]);

                if ($validator->fails()) {
                    $errorMessages = $validator->errors()->all();
                    \Log::info('Error messages: ' . json_encode($errorMessages));

                    $responseCode = '4017300';
                    $responseMessage = 'Unauthorized. Unknown Client';
                    if (in_array('The selected x- s i g n a t u r e is invalid.', $errorMessages)) {
                        $responseMessage = 'Unauthorized. Signature';
                    }
                    $newTimestamp = DokuUtils::generateTimestamp();

                    return response()->json([
                        'responseCode' => $responseCode,
                        'responseMessage' => $responseMessage,
                    ], 401)
                    ->header('X-CLIENT-KEY', $requestClientKey)
                    ->header('X-TIMESTAMP', $newTimestamp);
                    \Log::info('Response sent with status code 401', [
                        'responseCode' => $responseCode,
                        'responseMessage' => $responseMessage
                    ]);
                }else{
                $responseBody = array(
                    'responseCode' => '2002500',
                    'responseMessage' => 'Success',
                    'virtualAccountData' => array(
                        'partnerServiceId' => $partnerServiceId,
                        'customerNo' => $customerNo,
                        'virtualAccountNo' => $virtualAccountNo,
                        'virtualAccountName' => $virtualAccountName,
                        'paymentRequestId' => $paymentRequestId
                        )
                    );
                ControllerUtils::updatePaymentStatus($invoiceSnap, $virtualAccountNo, $paymentChannelSnap);
                \Log::info('Response Body : ' . json_encode($responseBody,JSON_UNESCAPED_SLASHES));
                $response = response()->json($responseBody);
                return $response;
                }
                break;
        };
        
    }

    

}
