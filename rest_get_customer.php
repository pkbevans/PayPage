<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/payPage/PeRestLib/RestRequest.php';
$incoming = json_decode(file_get_contents('php://input'));
try {
    // Get Customer
    $api = API_TMS_V2_CUSTOMERS ."/" . $incoming->customerId;
    $result = ProcessRequest(PORTFOLIO, $api , METHOD_GET, "", MID, AUTH_TYPE_SIGNATURE );
    echo(json_encode($result));
} catch (Exception $exception) {
    echo(json_encode($exception));
}
?>
