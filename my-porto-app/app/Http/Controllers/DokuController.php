<?php

namespace App\Http\Controllers;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Utils\DokuUtils;
use Illuminate\Support\Facades\Validator;

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
        $grantType = $requestData['grantType'] ?? null;
        $additionalInfo = $requestData['additionalInfo'] ?? null;
        $accessToken = $this->generateCustomAccessToken();
        $newSignature = DokuUtils::generateSignatureAsymmetric($requestTimestamp);
        $newtimestamp = DokuUtils::generateTimestamp();
        $clientId = env('DOKU_CLIENT_ID');
        \Log::info('-- Signature Validation --');
        \Log::info('X-SIGNATURE local : '. $newSignature);
        \Log::info('X-SIGNATURE request : '. $requestSignature);

        $validator = Validator::make([
            'X-TIMESTAMP' => $requestTimestamp,
            'X-CLIENT-KEY' => $requestClientKey,
            'X-SIGNATURE' => $requestSignature
        ], [
            'X-TIMESTAMP' => 'required|date_format:Y-m-d\TH:i:sP',
            'X-CLIENT-KEY' => 'required|in:'.env('DOKU_CLIENT_ID'),
            'X-SIGNATURE' => 'required|in:'.$requestSignature
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


    public function inquiryDanamon(Request $request)
    {
        $requestSignature = $request->header('X-SIGNATURE');
        $requestTimestamp = $request->header('X-TIMESTAMP');
        $requestClientId = $request->header('X-PARTNER-ID');
        $requestAuthorization = $request->header('Authorization');
        $requestExternalId = $request->header('X-EXTERNAL-ID');
        $requestData = $request->json()->all();
        $prefixNumber = $requestData['partnerServiceId'];
        $accountNumber = $requestData['customerNo'];
        $virtualAccountNumber = $prefixNumber . $accountNumber;
        //prepare body
        $responseBody = array(
            'responseCode' => '2002400',
            'responseMessage' => 'Successful',
            'virtualAccountData' => array(
                'partnerServiceId' => '        ' . $prefixNumber,
                'customerNo' => $accountNumber,
                'virtualAccountNo' => $virtualAccountNumber,
                'virtualAccountName' => 'Ashddq Customer',
                'virtualAccountEmail' => 'customer@ashddq.xyz',
                'virtualAccountPhone'=> '08123456789',
                'trxId' => DokuUtils::generateRequestid(),
                'virtualAccountTrxType' => '2',
                'additionalInfo' => array(
                'virtualAccountConfig' => array(
                    'reusableStatus' => true
                    )
                ),
                'totalAmount' => array(
                'value' => '0.00',
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
                            'value' => '1000000',
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
                ) 
            ),
            'totalAmount' => array(
                'value' => '0.00',
                'currency' => 'IDR'
            )
        );
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
        $responseBody = array(
            'responseCode' => '2002500',
            'responseMessage' => 'Success',
            'virtualAccountData' => array(
                'partnerServiceId' => '   77777',
                'customerNo' => '0000000000001',
                'virtualAccountNo' => '   777770000000000001',
                'virtualAccountName' => 'Toru Yamashita',
                'paymentRequestId' => '12839218738127830'
                )
            );
        $response = response()->json($responseBody);
        return $response;
    }

}
