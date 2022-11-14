<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/payPage/PeRestLib/RestRequest.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/db/db_insert_payment.php';
$incoming = json_decode(file_get_contents('php://input'));

$orderDetails = new stdClass();
$orderDetails->referenceNumber = $incoming->referenceNumber;
$orderDetails->orderId = $incoming->orderId;
$orderDetails->amount = $incoming->amount;
$orderDetails->currency = $incoming->currency;
$orderDetails->maskedPan = "TODO";
$orderDetails->capture = true;

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

    try{
        // Update DB
        $dbResult=insertPayment("REFUND", $orderDetails, $result);
        $result->payment = $dbResult;
    }catch(Exception $exception){
        $result->payment = "DB ERROR";
    }

} catch (Exception $exception) {
    $result->responseCode = 500;
    $result->exception = $exception;
}
echo(json_encode($result));
?>
