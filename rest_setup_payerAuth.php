<?php require_once 'restlib/API.php';
$incoming = json_decode(file_get_contents('php://input'));
$reference_number = $incoming->order->referenceNumber;

$request = new stdClass();
$request->clientReferenceInformation = new stdClass();
$request->clientReferenceInformation->code = $reference_number;
if(!empty($incoming->paymentInstrumentId)){
    $paymentInformation = [
            "paymentInstrument" => [
                "id" => $incoming->paymentInstrumentId
            ]
    ];
    $request->paymentInformation = $paymentInformation;
} else{
    $tokenInformation = [
        "transientToken" => $incoming->transientToken
    ];
    $request->tokenInformation = $tokenInformation;
}

$requestBody = json_encode($request);

try{
    // peportfolio/pemid03
    $strResponse = API::sendRequest(API::TEST_URL,API::POST,API::RISK_V1_AUTHENTICATION_SETUPS, "peportfolio",$requestBody,null,null, "pemid03" );
    $result = new stdClass();
    $result->request = $request;
    $objResponse = json_decode($strResponse);
    $result->httpCode = $objResponse->response->httpCode;
    $strResponseBody=$objResponse->response->body;
    $jsonBody = json_decode($strResponseBody);
    $result->response = $jsonBody;
    echo(json_encode($result));

} catch (Exception $exception) {
    echo "ERROR";
}
