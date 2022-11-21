<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/payPage/PeRestLib/RestRequest.php';
$incoming = json_decode(file_get_contents('php://input'));
$echo=true;
if(property_exists($incoming, "noEcho")){
    $echo = false;
}
$result = new stdClass();
try {
    // Get Customer
    $api = API_TMS_V2_CUSTOMERS . "/" . $incoming->customerId;
    $result = ProcessRequest(MID, $api , METHOD_GET, "", CHILD_MID, AUTH_TYPE_SIGNATURE );
    if(!$echo){
        if($result->responseCode == 200){
            $customer= $result->response;
        }
    }
} catch (Exception $exception) {
    $result->responseCode = 500;
    $result->exception = $exception;
}
if($echo){
    echo(json_encode($result));
}
?>
