<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/payPage/common/cybsApi/RestRequest.php';
$incoming = json_decode(file_get_contents('php://input'));
$targetOrigin = "https://" . $_SERVER['HTTP_HOST'];

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
