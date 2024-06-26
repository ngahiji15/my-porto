<?php

namespace App\Utils;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class DokuUtils
{
    public static function generateTimestamp()
    {
        date_default_timezone_set('Asia/Jakarta');
        $timestamp = date("Y-m-d\TH:i:sP");
        return $timestamp;
    }

    public static function generateTimestampOld()
    {
        date_default_timezone_set('UTC');
        $timestamp = date('Y-m-d\TH:i:s\Z');
        return $timestamp;
    }

    public static function generateSignatureSymmetric($token, $digest, $timeStamp, $path)
    {
        \Log::info('----- Geenerate Signature Symmetric -----');
        $newToken = str_replace('Bearer ', '', $token);
        $stringToSign = 'POST' . ':' . $path . ':' . $newToken . ':' . $digest . ':' . $timeStamp;
        \Log::info('stringToSign : ' . $stringToSign);
        $secretKey = env('DOKU_SECRET_KEY');
        $signature = base64_encode(hash_hmac('sha512', $stringToSign, $secretKey, true));
        return $signature;
    }

    public static function generateDigest($body)
    {   
        \Log::info('----- Generate Digest -----');
        \Log::info($body);
        $newBody = hash('sha256', json_encode($body,JSON_UNESCAPED_SLASHES));
        \Log::info('Digest : ' . $newBody);
        return $newBody;
    }

    public static function generateDigestOld($body)
    {   
        \Log::info('----- Generate Digest -----');
        \Log::info($body);
        if (is_string($body) && is_array(json_decode($body, true)) && (json_last_error() == JSON_ERROR_NONE)) {
            \Log::info('Body is JSON: ' . $body);
            $digest = base64_encode(hash('sha256', $body, true));
        } else {
            \Log::info('Body array');
            $jsonBody = self::jsonEncodeWithTwoSpaceIndentation($body);
            \Log::info('JSON Body: ' . $jsonBody);
            $digest = base64_encode(hash('sha256', $jsonBody, true));
        }
        \Log::info('Digest : ' . $digest);
        return $digest;
    }
    
    private static function jsonEncodeWithTwoSpaceIndentation($data)
    {
        $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        return preg_replace_callback('/^( +)/m', function ($m) {
            return str_repeat(' ', (int)strlen($m[1]) / 4 * 2);
        }, $json);
    }

    public static function generateDigestOldGenerate($body)
    {
        \Log::info('----- Generate Digest -----');
        \Log::info($body);
        
        // Convert array body to JSON string if it's an array
        if (is_array($body)) {
            $jsonBody = json_encode($body, JSON_UNESCAPED_SLASHES);
        } else {
            $jsonBody = $body;
        }
    
        // Add backslashes before slashes
        $jsonBody = str_replace('/', '\\/', $jsonBody);
        \Log::info('JSON Body after adding backslashes: ' . $jsonBody);
    
        // Minify JSON
        $minifiedJsonBody = self::minifyJson($jsonBody);
        \Log::info('Minified JSON Body: ' . $minifiedJsonBody);
    
        // Generate SHA-256 digest
        $digest = base64_encode(hash('sha256', $minifiedJsonBody, true));
        \Log::info('Digest : ' . $digest);
    
        return $digest;
    }
    
    private static function minifyJson($json)
    {
        // Menggunakan regex untuk menghapus spasi yang tidak diperlukan, kecuali dalam value
        $tokens = preg_split('/(".*?"|\s*[:,{}\[\]])/', $json, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $result = '';
        foreach ($tokens as $token) {
            // Jangan menghapus whitespace dalam value string
            if (preg_match('/^".*?"$/', $token)) {
                $result .= $token;
            } else {
                $result .= trim($token);
            }
        }
        return $result;
    }

    public static function generateDigestJSON($body)
    {
        \Log::info('----- Generate Digest -----');
        if (is_array($body)) {
            if (isset($body['partnerServiceId'])) {
                \Log::info('Original partnerServiceId: ' . $body['partnerServiceId']);
                $body['partnerServiceId'] = "   " . $body['partnerServiceId'];
                \Log::info('Modified partnerServiceId: ' . $body['partnerServiceId']);
            }
            if (isset($body['virtualAccountNo'])) {
                \Log::info('Original virtualAccountNo: ' . $body['virtualAccountNo']);
                $body['virtualAccountNo'] = "   " . $body['virtualAccountNo'];
                \Log::info('Modified virtualAccountNo: ' . $body['virtualAccountNo']);
            }
            $jsonBody = json_encode($body, JSON_UNESCAPED_SLASHES);
            \Log::info('Modified Body:');
            \Log::info($jsonBody);
            \Log::info('JSON Body:');
            \Log::info($jsonBody);
            $digest = hash('sha256', $jsonBody);
            \Log::info('Digest: ' . $digest);
            return $digest;
        } else {
            \Log::error('Input body is not a valid JSON string or array.');
            return null;
        }
    }
       
    public static function generateRequestid()
    {
        $requestid = "ASHDDQ".date('YmdHis');
        return $requestid;
    }

    public static function validationSignatureAsymmetric($waktu, $signature)
    {
        \Log::info('----- Signature Asymmetric Validation -----');
        try {
            $publicKey = <<<EOD
            -----BEGIN PUBLIC KEY-----
            MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA3KhxblAJODF3TNsaWAIKOdJE4GF0zWJjRo3X0H8+lDqhfCHwQx01Znhv36IrJ8fHaqqPX3jQJdyCM88O4NcxbgNqhtbvyKqW7lza1zd/1eTtCBZ6q3qrr2N6h8EKI2nxz4e/GgcMnskkpGFSwjN89sGKWUxubn/1QSJwX7ET9JbJqNLiy1AXe3OglGqGHlqOurw820OaL88jfVdqlLo07Z2513/WJXOBU3WIp7bf9pKeewepxqdia0A+UTBBEyJNgg2wHj6csdXvDxDrDqkgT1gECRtxzZtGQ4+qfK9yjzD926LcA7waQvKZrHQO2ryrVppYNHZ5pOinWHewpjHW2wIDAQAB
            -----END PUBLIC KEY-----
            EOD;

            // StringToSign = client_ID+"|"+X-TIMESTAMP
            $StringToSign = env('DOKU_CLIENT_ID') . '|' . $waktu;
            \Log::info('StringToSign : ' . $StringToSign);
            $verifier = openssl_verify($StringToSign, base64_decode($signature), $publicKey, OPENSSL_ALGO_SHA256);
            \Log::info('Result : '.$verifier);
            if ($verifier === 1) {
            \Log::info('Result : Signature Match');
            \Log::info('----- Finish Signature Asymmetric Validation -----');
            $result = 'Signature Match';
            return $result;
            } elseif ($verifier === 0) {
            \Log::info('Result : Signature Unmatch');
            \Log::info('----- Finish Signature Asymmetric Validation -----');
            $result = 'Signature Unmatch';
            return $result; // signature tidak valid
            } else {
            \Log::info('Result : Failed Verification Signature.');
            \Log::info('----- Finish Signature Asymmetric Validation -----');
            throw new Exception('Failed Verification Signature.');
            }
        } catch (Exception $error) {
            echo 'Error in verifySignatureToken: ' . $error->getMessage();
            \Log::info('----- Finish Signature Asymmetric Validation -----');
            return false;
        }
    }

    public static function generateSignatureAsymmetric($timestamp)
    {
        \Log::info('----- Generate Signature Asymmetric -----');
        $stringToSign = env('DOKU_CLIENT_ID')."|".$timestamp;
        $algorithm = "SHA256";
        $binarySignature = "";
        $privateKey = <<<EOD
        -----BEGIN RSA PRIVATE KEY-----
        MIIEoQIBAAKCAQBWHUKEWQm0Vjl5603jnizD4y31F2OUxy1KtczI0sAe246ANltg
        iZyHYrXh9EpGj82+T8J53g6txDkOQqvdtMN2USOOu8GFST4zhEvn8Wemf4Bx/8ze
        c4MoWbq/rUe5McF+7NIpyf9A9H3+PvnDhjyj3aqB4t9c4aD1chBGuIGrGvOkxwEi
        8oKrUGIiNyTumm5I6h767BE7oRdmO41yAos2iFKpcTG62TLIcUfURO/xTVNn7Prk
        PPXEO4HiwxVX8+BdFzx/bTEVXSv0Kfmxg+o36jqAgzTEA1KxwvVHz5Q+OUXetxXA
        XPbiKIDvjiXTaqhPxykVhRDrw4Cc5tSiWgc3AgMBAAECggEAUZ1/qnG8udcyuDNk
        acNDCBDrQKv/LEWtzm4JfZgIj/Zk020xI4io+sN6QIHIV6H5TFLJrbjgzp33uWVF
        AGZPDncOLTwTyKBHPIo5asWoB+w1r1XSNE7kUrzgOsQfAw1+Jy6KbSLOMiDGvM2w
        6Df0hxYSgPGl4qDRbW7CsFQ0SRayv+IKiW6S8x0SbvTQn0jQ9HF0JbltgRcJQOSH
        ggdjswjbFc5vEXa/1TsOBbRpQ+YX8x3nMTyPnlslfyfOVEOA9+skHLLLqT7uIkQp
        Y7MQc06trNVCKv9tQYNDn+/euaJvElQm+9sBIjpYXmqFyAB35glDexdEICn7Xs/o
        94qaaQKBgQChc/EHsQZ3nUgVXLrwF50nBODondW/8xcJkOIdhudEELvfE/dIPAQQ
        kSSkpAhRuLT444oq7lhvHD61L7In/79ccnTom09WK1hVxNMVdJP1eNwE0oCeY7AB
        88aGJpzthZPzKVFOjEqvmcRmMOcy6cBVONcipkeySMuvGVJIeZRSZQKBgQCIiwDR
        6uzg1uDOIzlZTW+Zoh7Qn4cgb+S40a+smfIs1HqPffovyuIzp3zUUFZoEj4u/0P+
        lSQY0FvXrIDftdSNkDMBW7k4/NfzpQ2N/NAWe8miym6w1ds87AeQ1atAoQ7D4NZk
        ra1J89tPCiFHZtny+146dHRZkLrYuCDk+NhLawKBgACj+RzSsvfeg96x03wIW/M/
        rbS+i46LZFgBXyRG2LwIZPZpmd2Lf2ihasfMbswEM5OZM38gGvG15vnJCqfl99hi
        C9ywYQwyd9M/SKcZI00iAZ1zSFdYheY8FVmK5ax7jy6zx0LMg69WqNTO9Nva2Yx9
        AT1982LdrxEuxIjNEq2RAoGADrtosD13p16nzLXyNxdqxhm+12WO78oC1IoTOT02
        6u1V9+twtf1e4JHenw239Oya9vklve8bgO3iKuf606hLsaZwSmI6HtLw/eG+D6bK
        UNK0U7MhtESurekNe+wB2SxHaoz0tNIkU0lTTTjblFedhmDmrsnnz84UytM7AVl0
        BNcCgYAjV2JR8w2x73PDZZ0AAwJ0nRi1oKjnpdCbEQwEO78LpWLfFBN5tt9F5EPl
        OTmug6hzBmFlljyazCWMQ5MSmbgwb/6oWMjkTqoB127mt+de/ZEuTGiCY5hPTSNF
        bsIDhM4NOoJ4NPgCoNKozMjdJsES9I98qxuW1iPJcvuhnOnbVw==
        -----END RSA PRIVATE KEY-----
        EOD;
        \Log::info('StringToSign : ' . $stringToSign);
        openssl_sign($stringToSign, $binarySignature, $privateKey, $algorithm);
        $signature = base64_encode($binarySignature);
        \Log::info('Signature : ' . $signature);
        \Log::info('----- Finish Generate Signature Asymmetric -----');
        return $signature;
    }

    public static function generateTokenB2B($timestamp, $signature)
    {
        $param = array (
            'grantType' => 'client_credentials'
            );
        $header = array (
            "X-CLIENT-KEY:" . env('DOKU_CLIENT_ID'),
            "X-SIGNATURE:" . $signature,
            "X-TIMESTAMP:" . $timestamp
        );
        $targetPath = '/authorization/v1/access-token/b2b';
        $domainApi = 'https://api-sandbox.doku.com';
        $urlApi = $domainApi . $targetPath;
        $ch = curl_init($urlApi);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($param));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    
        $responseJson = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
        curl_close($ch);
        $bodyResponse = json_decode($responseJson, true);
        return $bodyResponse;
    }

    public static function generateSignatureOld($timestamp, $requestid, $digest, $path)
    {
        $clientid = env('DOKU_CLIENT_ID');
        $sharedkey = env('DOKU_SECRET_KEY');
        \Log::info($sharedkey);
        $abc = "Client-Id:" . $clientid . "\n" . "Request-Id:" . $requestid . "\n" . "Request-Timestamp:" . $timestamp . "\n" . "Request-Target:" . $path . "\n" . "Digest:" . $digest;
        \Log::info($abc);
        $signature = base64_encode(hash_hmac('sha256', $abc, $sharedkey, true));
        \Log::info('HMACSHA256='.$signature);
        return $signature;
    }
    
    public static function generateCheckoutUrl($sessionid, $name, $email, $amount, $type, $orderType)
    {
        $clientId = env("DOKU_CLIENT_ID");
        \Log::info('SessionId : ' . $sessionid);
        $requestId = DokuUtils::generateRequestid();
        $currentDateTime = new \DateTime();
        $currentDateTime->modify('+7 hours +120 minutes');
        $formattedDateTime = $currentDateTime->format('Y-m-d H:i:s');        
        $path = '/checkout/v1/payment';
        switch ($type) {
            case 'demo':
                $domain = 'https://api-sandbox.doku.com';
                break;
            case 'production':
                $domain = 'https://api.doku.com';
                break;
            default:
                throw new InvalidArgumentException('Tipe lingkungan tidak valid: ' . $type);
        }

        $user = User::where('name', $name)->first();
        if ($user) {
            $user_id = $user->id;
        } else {
            $newUser = new User();
            $newUser->name = $name;
            $newUser->email = $email;
            $newUser->password = '1234';
            $newUser->save();
            $user_id = $newUser->id;
        }
        
        $url = $domain . $path;
        $timestamp = DokuUtils::generateTimestampOld();
        $invoice = DokuUtils::generateRequestid();
        $callback = URL::to('/payment');
        $Body = [
            'order' => [
                'amount' => $amount,
                'invoice_number' => $invoice,
                'session_id' => $sessionid,
                'callback_url' => $callback,
            ],
            'payment' => [
                'payment_due_date' => 120,
            ],
            'customer' => [
                'id' => 'ashddq' . $name,
                'name' => $name,
                'email' => $email,
            ],
        ];

        $digest = DokuUtils::generateDigestOldGenerate($Body);
        \Log::info(json_encode($Body));
        $signature = DokuUtils::generateSignatureOld($timestamp, $requestId, $digest, $path);

        // Inisialisasi CURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($Body));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Client-Id: ' . $clientId,
            'Request-Id: ' . $requestId,
            'Request-Timestamp: ' . $timestamp,
            'Signature: HMACSHA256=' . $signature,
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $responseArr = json_decode($response, true);
        $urlCheckout = $responseArr['response']['payment']['url'] ?? null;

        if ($httpcode === 200) {
            $message = $responseArr['message'] ?? null;
            \Log::info($response);
            $existingPayment = Payment::where('session_id', $sessionid)->first();
            if ($existingPayment) {
                $existingPayment->invoice_number = $invoice;
                $existingPayment->payment_channel = 'Doku Checkout';
                $existingPayment->user_id = $user_id;
                $existingPayment->status = 'PENDING';
                $existingPayment->type = $type;
                $existingPayment->order_type = $orderType;
                $existingPayment->expired_date = $formattedDateTime;
                $existingPayment->payment_code = $urlCheckout;
                $existingPayment->save();
            } else {
                \Log::error('Payment record not found for session_id: ' . $sessionid);
                return [
                    "httpCode" => 404,
                    "message" => "Payment record not found",
                ];
            }

            $data = [
                "httpCode" => $httpcode,
                "message" => $message,
                "urlCheckout" => $urlCheckout,
            ];
            return $data;
        } else {
            $message = $responseArr['error']['message'] ?? null;
            \Log::info($response);
            $data = [
                "httpCode" => $httpcode,
                "message" => $message,
            ];
            return $data;
        }
    }
}
