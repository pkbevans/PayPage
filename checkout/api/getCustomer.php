<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/payPage/common/cybsApi/RestRequest.php';
$incoming = json_decode(file_get_contents('php://input'));
$echo=true;
if(property_exists($incoming, "noEcho")){
    $echo = false;
}
$result = new stdClass();
// Get Customer
$api = API_TMS_V2_CUSTOMERS . "/" . $incoming->customerId;
$result = ProcessRequest(MID, $api , METHOD_GET, "", CHILD_MID, AUTH_TYPE_SIGNATURE );
if($result->responseCode == 200){
    header('HTTP/1.1 200 OK');
    if($echo){
        echo(json_encode($result->response, JSON_UNESCAPED_SLASHES));
    }else{
        $customer = $result->response;
    }
}else{
    header('HTTP/1.1 ' . $result->responseCode . ' ERROR');
}
?>
