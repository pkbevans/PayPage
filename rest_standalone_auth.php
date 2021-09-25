<?php require_once 'restlib/API.php';
$incoming = json_decode(file_get_contents('php://input'));
$reference_number = $incoming->order->referenceNumber;
try {
    $request = new stdClass();

    $orderInformation = [
        "amountDetails"=>[
            "totalAmount"=>$incoming->order->amount,
            "currency"=>$incoming->order->currency],
        "billTo" => [
            "firstName" => $incoming->order->bill_to->bill_to_forename,
            "lastName" => $incoming->order->bill_to->bill_to_surname,
            "address1" => $incoming->order->bill_to->bill_to_address_line1,
            "locality" => $incoming->order->bill_to->bill_to_address_city,
            "postalCode" => $incoming->order->bill_to->bill_to_postcode,
            "country" => $incoming->order->bill_to->bill_to_address_country,
            "email" => $incoming->order->bill_to->bill_to_email,
        ],
    ];
    $request->orderInformation = $orderInformation;

    $useFlexToken = true;
    if($useFlexToken){
        $tokenInformation = [
            "jti" => $incoming->transientToken
        ];
        $request->tokenInformation = $tokenInformation;
    }
    else {
        $paymentInformation= [
            "card" => [
                "number"=> "4111111111111111",
                "expirationMonth"=> "12",
                "expirationYear"=> "2031",
                "securityCode"=> "123"
            ]
        ];
        $request->paymentInformation = $paymentInformation;
    }

    $request->clientReferenceInformation = new stdClass();
    $request->clientReferenceInformation->code = $reference_number;

    $consumerAuthenticationInformation = [
        "referenceId" => $incoming->referenceID,
        "returnUrl" => "http://localhost:8018/cybs/3DS2DirectRestMobile/redirect.php"
    ];
    $request->consumerAuthenticationInformation = $consumerAuthenticationInformation;

    $requestBody = json_encode($request);

    if($incoming->standAlone){
        $api = API::RISK_V1_AUTHENTICATIONS;
    }else{
        $api = API::PTS_V2_PAYMENTS;
    }
    $strResponse = API::sendRequest(API::TEST_URL,API::POST,$api, "peportfolio",$requestBody,null,null, "pemid03" );
    $result = new stdClass();
    $result->request = $request;
    $objResponse = json_decode($strResponse);
    $result->httpCode = $objResponse->response->httpCode;
    $strResponseBody=$objResponse->response->body;
    $jsonBody = json_decode($strResponseBody);
    $result->response = $jsonBody;
    echo(json_encode($result));

} catch (Exception $exception) {
//    var_dump(get_class($exception));
//    var_dump($exception);
    echo(json_encode($exception));
}
?>