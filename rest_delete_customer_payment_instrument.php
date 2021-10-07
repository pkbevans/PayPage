<?php require_once 'PeRestLib/RestRequest.php';
$incoming = json_decode(file_get_contents('php://input'));
try {
    $api = API_TMS_V2_CUSTOMERS . "/" .$incoming->customerId . "/payment-instruments/" . $incoming->paymentInstrumentId;
    $result = ProcessRequest("peportfolio", $api , METHOD_DELETE, "", "pemid03", AUTH_TYPE_SIGNATURE );
    echo(json_encode($result));
} catch (Exception $exception) {
    echo(json_encode($exception));
}
?>
