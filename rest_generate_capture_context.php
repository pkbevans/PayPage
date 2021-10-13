<?php require_once 'PeRestLib/RestRequest.php';
$incoming = json_decode(file_get_contents('php://input'));
if($incoming->local == true){
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

$result = ProcessRequest("peportfolio", API_FLEX_V1_KEYS . "?format=JWT" , METHOD_POST, $requestBody, "pemid03", AUTH_TYPE_SIGNATURE );
echo(json_encode($result));
