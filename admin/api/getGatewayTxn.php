<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/common/PeRestLib/RestRequest.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/common/v1/controller/validation.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/admin/view/addresses.php';

$incoming = json_decode(file_get_contents('php://input'));
$response = checkPermission($incoming->accessToken, USERTYPE_INTERNAL, true);
if(!$response->success()){
    $response->send();
    exit;
}
$result = new stdClass();
$txn = new stdClass();
try {
    // Get Customer
    $api = API_TSS_V2_TRANSACTIONS . "/" . $incoming->requestId;
    $result = ProcessRequest(MID, $api , METHOD_GET, "", CHILD_MID, AUTH_TYPE_SIGNATURE );
    if($result->responseCode == 200){
        $txn = $result->response;
    }else{
        return $result->response;
    }
} catch (Exception $exception) {
    $result->responseCode = 500;
    $result->exception = $exception;
    return $result;
}
// OK if it gets here
include $_SERVER['DOCUMENT_ROOT'].'/payPage/admin/view/'. $incoming->view;

