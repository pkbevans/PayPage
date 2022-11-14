<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/payPage/PeRestLib/RestRequest.php';
$incoming = json_decode(file_get_contents('php://input'));
$result = new stdClass();
try {
    // Get Customer
    $api = API_TSS_V2_TRANSACTIONS . "/" . $incoming->requestId;
    $result = ProcessRequest(MID, $api , METHOD_GET, "", CHILD_MID, AUTH_TYPE_SIGNATURE );
    if($result->responseCode == 200){
        $txn= $result->response;
    }
} catch (Exception $exception) {
    $result->responseCode = 500;
    $result->exception = $exception;
}
?>
