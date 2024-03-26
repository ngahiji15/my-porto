<?php

namespace App\Utils;

class DokuUtils
{
    public static function generateTimestamp()
    {
        date_default_timezone_set('Asia/Jakarta');
        $timestamp = date("Y-m-d\TH:i:sP");
        return $timestamp;;
    }

    public static function generateSignatureSymmetric($token, $digest, $timeStamp, $path)
    {
        $stringToSign = 'POST' . ':' . $path . ':' . $token . ':' . $digest . ':' . $timeStamp;
        $secretKey = env('DOKU_SECRET_KEY');
        $signature = base64_encode(hash_hmac('sha512', $stringToSign, $secretKey, true));
        return $signature;
    }

    public static function generateDigest($body)
    {
        $newBody = hash('sha256', json_encode($body,JSON_UNESCAPED_SLASHES));
        return $newBody;
    }

    public static function generateRequestid()
    {
        $requestid = "ASHDDQ".date('YmdHis');
        return $requestid;
    }

    public static function generateSignatureAsymmetric($timestamp)
    {
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
}
