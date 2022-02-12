<?php require_once 'PeRestLib/RestRequest.php';
$incoming = json_decode(file_get_contents('php://input'));
$reference_number = $incoming->order->referenceNumber;

$request = new stdClass();
$request->clientReferenceInformation = new stdClass();
$request->clientReferenceInformation->code = $reference_number;
if(!empty($incoming->order->paymentInstrumentId)){
    $paymentInformation = [
            "paymentInstrument" => [
                "id" => $incoming->order->paymentInstrumentId
            ]
    ];
    $request->paymentInformation = $paymentInformation;
} else{
    $tokenInformation = [
        "transientToken" => $incoming->order->flexToken
    ];
    $request->tokenInformation = $tokenInformation;
}

$requestBody = json_encode($request);

try{
    $result = ProcessRequest(PORTFOLIO, API_RISK_V1_AUTHENTICATION_SETUPS, METHOD_POST, $requestBody, MID, AUTH_TYPE_SIGNATURE );
    echo(json_encode($result));

} catch (Exception $exception) {
    echo "ERROR";
}
