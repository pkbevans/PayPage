<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/common/cybsApi/RestRequest.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/common/v1/controller/validation.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/common/v1/model/Response.php';

$incoming = json_decode(file_get_contents('php://input'));
$response = checkPermission($incoming->accessToken, USERTYPE_INTERNAL, true);
if(!$response->success()){
    $response->send();
    exit;
}

$result = new stdClass();
// Get Customer
$api = API_TMS_V2_CUSTOMERS . "/" . $incoming->customerId;
$result = ProcessRequest(MID, $api , METHOD_GET, "", CHILD_MID, AUTH_TYPE_SIGNATURE );
if($result->responseCode == 200){
    header('HTTP/1.1 200 OK');
    $customer = $result->response;
    include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/admin/view/viewGatewayCustomer.php';
}else{
    header('HTTP/1.1 ' . $result->responseCode . ' ERROR');
    $response = new Response($result->responseCode, false, 'fail', null);
    $response->send();
    exit;
}
?>
