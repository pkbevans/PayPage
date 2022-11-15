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
        "orderInformation" => [
            "amountDetails" => [
                "totalAmount"  =>  $incoming->amount,
                "currency"  =>  $incoming->currency
            ]
        ]
    ];
    $requestBody = json_encode($request);
    // Get Customer
    $api = API_PAYMENTS . "/" . $incoming->requestId . "/refunds";
    $result = ProcessRequest(MID, $api , METHOD_POST, $requestBody, CHILD_MID, AUTH_TYPE_SIGNATURE );
    $dbError = "";
    if($result->responseCode == 201){
        if( !insertPayment("REFUND", $incoming->orderId, $incoming->amount, 0, $incoming->currency,
                "n/a","n/a","n/a", $result->response->id, "SUBMITTED")){
            $dbError .= "DBError inserting Payment. ";
            $result->dberror = $dbError;
        }
        if( !updateOrder($incoming->orderId, "REFUNDED")){
            $dbError .= "DBError updating Order";
            $result->dberror = $dbError;
        }
    }
} catch (Exception $exception) {
    $result->responseCode = 500;
    $result->exception = $exception;
}
echo(json_encode($result));
?>
