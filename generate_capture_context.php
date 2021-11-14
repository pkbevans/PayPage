<?php require_once 'PeRestLib/RestRequest.php';
if(isset($_REQUEST['local']) && $_REQUEST['local'] === "true"){
    $targetOrigin = "http://localhost";
}else{
    $targetOrigin = TARGET_ORIGIN;
}

$request = [
        "encryptionType"=> "RsaOaep",
        "targetOrigin" => $targetOrigin
];
$requestQuery = '{"format":"JWT"}';

$requestBody = json_encode($request);

$result = ProcessRequest(PORTFOLIO, API_FLEX_V1_KEYS . "?format=JWT" , METHOD_POST, $requestBody, MID, AUTH_TYPE_SIGNATURE );
//echo(json_encode($result));
if($result->responseCode == 200){
    $captureContext = $result->response->keyId;
}else{
    $captureContext = "";
}
