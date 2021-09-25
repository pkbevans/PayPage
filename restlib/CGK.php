<?php
require_once 'Credentials.php';
require_once 'JWT.php';
final class CGK {
    public static function generateBearerTokenForRequestAndMid($request, $mid, $child=null) {
        if (file_exists(Credentials::MIDS[$mid]['p12FilePath'])) {
            $certificateStore = file_get_contents(Credentials::MIDS[$mid]['p12FilePath']);
        } else {
            echo 'There is no file at '. Credentials::MIDS[$mid]['p12FilePath'];
            exit();
        }
        if (openssl_pkcs12_read($certificateStore, $certificateInfo, Credentials::MIDS[$mid]['p12Pass'])) {
            $privateKey = $certificateInfo['pkey'];
            $publicKey = CGK::pemToDer($certificateInfo['cert']);
            $x5cArray = array($publicKey);

            $bearerJwtHeader = array();

            if ($child !== null) { $bearerJwtHeader['v-c-merchant-id'] = $child; } else { $bearerJwtHeader['v-c-merchant-id'] = Credentials::MIDS[$mid]['p12Alias']; }

            $bearerJwtHeader['x5c'] = $x5cArray;
            $bearerJwtHeader['typ'] = "JWT";
            $bearerJwtHeader['alg'] = "RS256";
            $bearerJwtHeader = json_encode($bearerJwtHeader);

            $bearerJwtBody = array(
                'iat' => date(DATE_RFC1123,time())
            );
            switch ($request->method) {
                case 'POST':
                case 'PATCH':
                case 'PUT':
                    $bearerJwtBody['digest'] = base64_encode(hash('sha256', utf8_encode($request->body), true));
                    $bearerJwtBody['digestAlgorithm'] = 'SHA-256';
                    break;
            }
            $bearerJwtBody = json_encode($bearerJwtBody);
            $request->headers['Authorization'] = 'Bearer '.JWT::generateBearer($bearerJwtHeader,$bearerJwtBody,$privateKey);
        } else {
            echo 'Unable to parse a PKCS#12 Certificate Store into an array';
            exit();
        }
    }
    public static function generateHttpSignatureForRequest($request, $child=null) {
        switch ($request->method) {
            case 'GET':
            case 'DELETE':
                $signatureString =
                    "host: ".$request->headers['host'].
                    "\ndate: ".$request->headers['date'].
                    "\n(request-target): ".$request->target.
                    "\nv-c-merchant-id: ".$request->headers['v-c-merchant-id'];
                $request->headers['signature'] =
                    "keyid=\"".Credentials::MIDS[$request->headers['v-c-merchant-id']]['key']."\", ".
                    "algorithm=\"HmacSHA256\", ".
                    "headers=\"host date (request-target) v-c-merchant-id\", ".
                    "signature=\"" . CGK::sign($signatureString, $request) . "\"";
                break;
            case 'POST':
            case 'PATCH':
            case 'PUT':
                $signatureString =
                    "host: ".$request->headers['host'].
                    "\ndate: ".$request->headers['date'].
                    "\n(request-target): ".$request->target.
                    "\ndigest: ".$request->headers['digest'].
                    "\nv-c-merchant-id: ".$request->headers['v-c-merchant-id'];
                $request->headers['signature'] =
                    "keyid=\"".Credentials::MIDS[$request->headers['v-c-merchant-id']]['key']."\", ".
                    "algorithm=\"HmacSHA256\", ".
                    "headers=\"host date (request-target) digest v-c-merchant-id\", ".
                    "signature=\"" . CGK::sign($signatureString, $request) . "\"";
                break;
        }
        if ($child !== null) { $request->headers['v-c-merchant-id'] = $child; }
    }
    private static function sign($string, $request) {
        return base64_encode(hash_hmac('sha256', $string, base64_decode(Credentials::MIDS[$request->headers['v-c-merchant-id']]['secret']), true));
    }
    private static function pemToDer($pem){
        $lines = explode("\n", trim($pem));
        unset($lines[count($lines)-1]);
        unset($lines[0]);
        return implode("\n", $lines);
    }
}
