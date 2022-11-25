<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/payPage/PeRestLib/RestRequest.php';
$incoming = json_decode(file_get_contents('php://input'));
$targetOrigin = "https://" . (strstr($_SERVER['HTTP_HOST'],LOCALHOST_TARGET_ORIGIN)?LOCALHOST_TARGET_ORIGIN:PRODUCTION_TARGET_ORIGIN);

$request = [
    "targetOrigins" => [$targetOrigin]
];

$requestBody = json_encode($request);

$result = ProcessRequest(MID, API_MICROFORM_SESSIONS, METHOD_POST, $requestBody, CHILD_MID, AUTH_TYPE_SIGNATURE );
if($result->responseCode == 201){
    header('HTTP/1.1 201 OK');
    echo $result->rawResponse;
}else{
    header('HTTP/1.1 404 ERROR');
}
