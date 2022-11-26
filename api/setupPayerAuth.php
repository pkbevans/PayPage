<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/payPage/PeRestLib/RestRequest.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/payPage/php/utils/logApi.php';

$incoming = json_decode(file_get_contents('php://input'));
$reference_number = $incoming->order->referenceNumber;

$request = new stdClass();
$request->clientReferenceInformation = new stdClass();
$request->clientReferenceInformation->code = $reference_number;

if($incoming->order->buyNow){
    // Buy now - use default Payment Instrument
    $paymentInformation = [
            "customer" => [
                "id" => $incoming->order->customerId
            ]
    ];
    $request->paymentInformation = $paymentInformation;
} else if(!empty($incoming->order->paymentInstrumentId)){
    // Specific Payment instrument selected
    $paymentInformation = [
            "paymentInstrument" => [
                "id" => $incoming->order->paymentInstrumentId
            ]
    ];
    $request->paymentInformation = $paymentInformation;
} else if(!empty($incoming->order->googlePayToken)){
    // Google Pay
    $processingInfo = [
        "paymentSolution" => "012"
    ];
    $request->processingInformation = $processingInfo;
    $request->paymentInformation['fluidData']['value'] = base64_encode($incoming->order->googlePayToken);
} else {
    // New Card
    $tokenInformation = [
        "transientToken" => $incoming->order->flexToken
    ];
    $request->tokenInformation = $tokenInformation;
}

$requestBody = json_encode($request);

$result = ProcessRequest(MID, API_RISK_V1_AUTHENTICATION_SETUPS, METHOD_POST, $requestBody, CHILD_MID, AUTH_TYPE_SIGNATURE );
$json = json_encode($result);
logApi($incoming->order->referenceNumber,
        "setupPA",                              // API Type
        $result->response->status,              // Status
        $incoming->order->amount,               // Amount
        false,                                  // Token created?
        $json);                                 // Complete request + response
if($result->responseCode == 201 && $result->response->status == "COMPLETED") {
    header('HTTP/1.1 201 OK');
    echo json_encode($result->response);
}else{
    header('HTTP/1.1 404 ERROR');
}
