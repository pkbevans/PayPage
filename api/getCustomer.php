<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/payPage/PeRestLib/RestRequest.php';
$incoming = json_decode(file_get_contents('php://input'));
$result = new stdClass();
try {
    // Get Customer
    $api = API_TMS_V2_CUSTOMERS . "/" . $incoming->customerId;
    $result = ProcessRequest(MID, $api , METHOD_GET, "", CHILD_MID, AUTH_TYPE_SIGNATURE );
} catch (Exception $exception) {
    $result->responseCode = 500;
    $result->exception = $exception;
}
echo(json_encode($result));
?>
