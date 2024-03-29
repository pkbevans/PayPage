<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/common/cybsApi/RestRequest.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/common/db/paymentUtils.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/common/v1/controller/db.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/common/v1/controller/validation.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/common/v1/model/Response.php';

$incoming = json_decode(file_get_contents('php://input'));
$response = checkPermission($incoming->accessToken, USERTYPE_INTERNAL, true);
if(!$response->success()){
    $response->send();
    exit;
}

$result = new stdClass();
try {
    $request = [
        "clientReferenceInformation" => [
            "code"  =>  $incoming->referenceNumber
        ],
        "reason" => $incoming->reason
    ];
    $requestBody = json_encode($request);
    // Get Customer
    $api = API_PAYMENTS . "/" . $incoming->requestId . "/voids";
    $result = ProcessRequest(MID, $api , METHOD_POST, $requestBody, CHILD_MID, AUTH_TYPE_SIGNATURE );
    $dbError = "";
    if($result->responseCode == 201){
        if( !$dbResult = insertPayment($incoming->accessToken, "VOID", $incoming->orderId, 0, 0, "",
                $incoming->cardNumber,"n/a","n/a", $result->response->id, "SUBMITTED")){
            $dbError .= "DBError inserting Payment. ";
            $result->dberror = $dbError;
        }else{
            $result->insertPayment = $dbResult;
        }
        if( !$dbResult = updateOrder($incoming->accessToken, $incoming->orderId, "VOIDED")){
            $dbError .= "DBError updating Order";
            $result->dberror = $dbError;
        }else{
            $result->updateOrder = $dbResult;
        }
    }else{
        $response = new Response($result->responseCode, false, "error", $result);
        $response->send();
        exit;
    }    
} catch (Exception $exception) {
    $result->exception = $exception;
    $response = new Response(500, false, "error", $result);
    $response->send();
    exit;
}
// OK if it gets here
$response = new Response(200, true, "success", $result);
$response->send();
?>
