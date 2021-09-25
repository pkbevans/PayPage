<?php
require_once 'Credentials.php';
final class JWT {
    private static function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    private static function base64url_decode($data) {
        return base64_decode(str_replace(array('-', '_'), array('+', '/'), $data));
    }
    private static function sign($input, $key) {
        $signature = '';
        $result = openssl_sign($input, $signature, $key, 'SHA256');
        if (!$result) {
            throw new Exception("OpenSSL unable to sign data");
        } else {
            return $signature;
        }
    }
    public static function generate($mid, $header, $payload){
        $payload['jti'] = uniqid('jti_',true);
        $payload['iat'] = time();
        $payload['iss'] = Credentials::MIDS[$mid]['apiIdentifier'];
        $payload['OrgUnitId'] = Credentials::MIDS[$mid]['orgUnitID'];

        $base64urlHeader = JWT::base64url_encode(json_encode($header));
        $base64urlPayload = JWT::base64url_encode(json_encode($payload));
        $text = $base64urlHeader.'.'.$base64urlPayload;
        $signature = JWT::base64url_encode(hash_hmac('sha256', $text, Credentials::MIDS[$mid]['apiKey'], true));
        return $text.'.'.$signature;
    }
    public static function generateBearer($header,$payload,$key){
        $base64urlHeader = JWT::base64url_encode($header);
        $base64urlPayload = JWT::base64url_encode($payload);
        $text = $base64urlHeader.'.'.$base64urlPayload;
        $signature = JWT::base64url_encode(JWT::sign($text, $key));
        return $text.'.'.$signature;
    }
}
