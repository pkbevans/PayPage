<?php require_once 'PeRestLib/RestRequest.php';
$incoming = json_decode(file_get_contents('php://input'));
if($incoming->local == true){
    $targetOrigin = "http://localhost";
}else{
    $targetOrigin = TARGET_ORIGIN;
}

$request = [
        "targetOrigins" => [$targetOrigin]
];

$requestBody = json_encode($request);

$result = ProcessRequest(PORTFOLIO, API_MICROFORM_SESSIONS, METHOD_POST, $requestBody, MID, AUTH_TYPE_SIGNATURE );
echo(json_encode($result));
