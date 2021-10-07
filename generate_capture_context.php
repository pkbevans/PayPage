<?php require_once 'PeRestLib/RestRequest.php';
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

$result = ProcessRequest("peportfolio", API_FLEX_V1_KEYS . "?format=JWT" , METHOD_POST, $requestBody, "pemid03", AUTH_TYPE_SIGNATURE );
// echo(json_encode($result));
$captureContext = $result->response->keyId;
