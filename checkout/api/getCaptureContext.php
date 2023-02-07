<?php
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
require '../../vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/payPage/common/cybsApi/RestRequest.php';

$incoming = json_decode(file_get_contents('php://input'));
$targetOrigin = "https://" . $_SERVER['HTTP_HOST'];

$request = [
    "targetOrigins" => [$targetOrigin]
];

$requestBody = json_encode($request);

$result = ProcessRequest(MID, API_MICROFORM_SESSIONS, METHOD_POST, $requestBody, CHILD_MID, AUTH_TYPE_SIGNATURE );
if($result->responseCode == 201){
    try{
        verifyToken($result->rawResponse);
        header('HTTP/1.1 201 OK');
        echo $result->rawResponse;
    }catch(Exception $e){
        header('HTTP/1.1 500 ERROR');
        echo $e->getMessage();
    }
}else{
    header('HTTP/1.1 404 ERROR');
}

function verifyToken($jwt)
{
    // Decode JWT header to get value of kid
    [$rawHeader, $rawPayload] = explode('.', $jwt, 2);
    $header = json_decode(base64_decode($rawHeader));
    // echo "HEADER: " . json_encode($header) . "<BR><BR>";
    $kid = $header->kid;
    $alg = $header->alg;
    // echo "KID: " . $kid . "<BR><BR>";
    try {
        // See if we have this key in cache
        $key = getKeyFromCache($kid);
    }catch(NotInCacheException $e){
        // Get public key with given kid from Cybs
        $key = file_get_contents(PUBLIC_KEYS_URL . $kid);
        writeKeyToCache($kid, $key);
    }
    // echo "KEY: " . $key . "<BR><BR>";
    // Turn this key into a PEM
    $jwks = [];
    $pk = json_decode($key, true);
    $pk['alg'] = $alg;
    $jwks['keys'][0] = $pk;
    // echo '<pre>' . json_encode($jwks, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . '</pre>';
    // Verify the JWT against the given public key
    JWT::$leeway = 60; // $leeway in seconds
    JWT::decode($jwt, JWK::parseKeySet($jwks));
    // echo '<pre>' . json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . '</pre>';
}
function getKeyFromCache($kid){
    $cacheFileName = $_SERVER["DOCUMENT_ROOT"] . CACHE_LOCATION . $kid;
    if(file_exists($cacheFileName) && ($key = file_get_contents($cacheFileName))){
        return $key;
    }else{
        $err = "Key: " . $kid . " not found in cache";
        error_log($err);
        throw new NotInCacheException($err);
    }
}
function writeKeyToCache($kid, $contents){
    if(!file_put_contents($_SERVER["DOCUMENT_ROOT"] . CACHE_LOCATION . $kid, $contents)){
        error_log("Failed to write to cache: " . $kid);
    }
}
class NotInCacheException extends Exception {}
