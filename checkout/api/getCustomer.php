<?php
if(!isset($_COOKIE['accessToken'])|| !isset($_REQUEST['customerId'])){
    $response = new Response(401, false, "Access denied", null);
    $response->send();
    exit;
}
$accessToken = $_COOKIE['accessToken'];
require_once $_SERVER['DOCUMENT_ROOT'].'/payPage/common/cybsApi/RestRequest.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/common/v1/controller/validation.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/common/v1/model/Response.php';

// $incoming = json_decode(file_get_contents('php://input'));
$response = checkPermission($accessToken, USERTYPE_CUSTOMER, false, $_REQUEST['customerId']);
if(!$response->success()){
    $response->send();
    exit;
}

$result = new stdClass();
// Get Customer
$api = API_TMS_V2_CUSTOMERS . "/" . $_REQUEST['customerId'];
$result = ProcessRequest(MID, $api , METHOD_GET, "", CHILD_MID, AUTH_TYPE_SIGNATURE );
if($result->responseCode == 200){
    $response = new Response(200, true, "success", $result->response);
    $response->send();
    exit;
}else{
    $response = new Response($result->responseCode, false, "fail", $result->response);
    $response->send();
    exit;
}
?>
