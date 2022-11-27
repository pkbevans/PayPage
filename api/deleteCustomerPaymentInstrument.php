<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/payPage/PeRestLib/RestRequest.php';
$incoming = json_decode(file_get_contents('php://input'));
try {
    $api = API_TMS_V2_CUSTOMERS . "/" .$incoming->customerId . "/payment-instruments/" . $incoming->paymentInstrumentId;
    $result = ProcessRequest(MID, $api , METHOD_DELETE, "", CHILD_MID, AUTH_TYPE_SIGNATURE );
    echo(json_encode($result));
} catch (Exception $exception) {
    echo(json_encode($exception));
}
?>