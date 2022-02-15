<?php require_once 'PeRestLib/RestRequest.php';
$incoming = json_decode(file_get_contents('php://input'));
///////////////////////////FUNCTIONS
/////////////////////////END FUNCTIONS
try {
    $api =  API_TMS_PAYMENT_INSTRUMENTS ."/" . $incoming->id;
    $result = ProcessRequest(PORTFOLIO, $api , METHOD_GET, "", MID, AUTH_TYPE_SIGNATURE );
    echo(json_encode($result));
} catch (Exception $exception) {
    echo(json_encode($exception));
}
