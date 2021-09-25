<?php require_once 'restlib/API.php';
$incoming = json_decode(file_get_contents('php://input'));
try {
    $request = [
        "default" => $incoming->default,
        "shipTo" => [
            "firstName" => $incoming->firstName,
            "lastName" => $incoming->lastName,
            "address1" => $incoming->address1,
            "address2" => $incoming->address2,
            "locality" => $incoming->locality,
            "administrativeArea" => $incoming->administrativeArea,
            "postalCode" => $incoming->postalCode,
            "country" => $incoming->country
//            "phoneNumber" => "321321"
        ]
    ];

    $requestBody = json_encode($request);

    $api = API::TMS_V2_CUSTOMERS . "/" .$incoming->customerId . "/shipping-addresses";

//    $strResponse = API::sendRequest(API::TEST_URL,API::PATCH, $api, "peportfolio", $requestBody, null, null, "pemid03" );
    $strResponse = API::sendRequest(API::TEST_URL, API::POST, $api, "pemid03", $requestBody, null, null, null );
    $result = new stdClass();
    $result->api = $api;
    $result->request = $request;
    $objResponse = json_decode($strResponse);
    $result->httpCode = $objResponse->response->httpCode;
    $strResponseBody=$objResponse->response->body;
    $jsonBody = json_decode($strResponseBody);
    $result->response = $jsonBody;
    echo(json_encode($result));

} catch (Exception $exception) {
    echo(json_encode($exception));
}
?>
