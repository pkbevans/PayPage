<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/payPage/PeRestLib/RestRequest.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/db/paymentUtils.php';
$incoming = json_decode(file_get_contents('php://input'));

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
        if( !$dbResult = insertPayment($accessToken, "VOID", $incoming->orderId, 0, 0, "",
                $incoming->cardNumber,"n/a","n/a", $result->response->id, "SUBMITTED")){
            $dbError .= "DBError inserting Payment. ";
            $result->dberror = $dbError;
        }else{
            $result->insertPayment = $dbResult;
        }
        if( !$dbResult = updateOrder($accessToken, $incoming->orderId, "VOIDED")){
            $dbError .= "DBError updating Order";
            $result->dberror = $dbError;
        }else{
            $result->updateOrder = $dbResult;
        }
    }
} catch (Exception $exception) {
    $result->responseCode = 500;
    $result->exception = $exception;
}
echo(json_encode($result));
?>
