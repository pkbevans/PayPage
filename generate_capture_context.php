<?php
require_once 'restlib/API.php';
if(isset($_REQUEST['local']) && $_REQUEST['local'] === "true"){
    $targetOrigin = "http://localhost";
}else{
    $targetOrigin = "https://bondevans.com";
}

$request = [
        "encryptionType"=> "RsaOaep",
        "targetOrigin" => $targetOrigin
];
$requestQuery = '{"format":"JWT"}';

$requestBody = json_encode($request);

// peportfolio/pemid03
$strResponse = API::sendRequest(API::TEST_URL,API::POST,API::FLEX_V1_KEYS, "peportfolio",$requestBody,null,$requestQuery, "pemid03" );

$result = new stdClass();
$result->request = $request;
$objResponse = json_decode($strResponse);
$result->httpCode = $objResponse->response->httpCode;
$strResponseBody=$objResponse->response->body;
$jsonBody = json_decode($strResponseBody);
$result->response = $jsonBody;
// echo(json_encode($result));
$captureContext = $jsonBody->keyId;
