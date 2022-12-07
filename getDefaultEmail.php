<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/payPage/PeRestLib/RestRequest.php';

function getDefaultEmail($customerId){
    $result = new stdClass();
    // Get Customer and get email address for default payment instrument
    $api = API_TMS_V2_CUSTOMERS . "/" . $customerId;
    $result = ProcessRequest(MID, $api , METHOD_GET, "", CHILD_MID, AUTH_TYPE_SIGNATURE );
    if($result->responseCode == 200){
        return $result->response->_embedded->defaultPaymentInstrument->billTo->email;
    }else{
        return "";
    }
}
?>