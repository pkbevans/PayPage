<?php require_once 'restlib/API.php';
$incoming = json_decode(file_get_contents('php://input'));
try {
    $api = API::TMS_V2_CUSTOMERS . "/" .$incoming->customerId . "/shipping-addresses/" . $incoming->shippingAddressId;

    $strResponse = API::sendRequest(API::TEST_URL, API::DELETE, $api, "pemid03", "", null, null, null );
    $result = new stdClass();
    $result->api = $api;
    $objResponse = json_decode($strResponse);
    $result->httpCode = $objResponse->response->httpCode;
    echo(json_encode($result));
} catch (Exception $exception) {
    echo(json_encode($exception));
}
?>
