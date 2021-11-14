<?php require_once 'PeRestLib/RestRequest.php';
///////////////////////////FUNCTIONS
/////////////////////////END FUNCTIONS
//$paymentInstrumentCount = 0;
$shippingAddressAvailable = false;
$customerToken = "";
$defaultPaymentInstrument="";
$defaultShippingAddress;

if(isset($_REQUEST['customerToken'])){
//    $customerToken = filter_input(INPUT_POST, 'customerToken', FILTER_SANITIZE_SPECIAL_CHARS);
    $customerToken = $_REQUEST['customerToken'];
    if($customerToken){
        try {
            // Get Customer TODO - NOT REQUIRED
            $api = API_TMS_V2_CUSTOMERS ."/" . $customerToken;
            $result = ProcessRequest(PORTFOLIO, $api , METHOD_GET, "", MID, AUTH_TYPE_SIGNATURE );
            if($result->responseCode === 200){
//                echo("<BR> BODY<PRE>" .json_encode($result, JSON_PRETTY_PRINT). "</PRE><BR>");
                if(isset($result->response->_embedded->defaultPaymentInstrument)){
                    $defaultPaymentInstrument = $result->response->_embedded->defaultPaymentInstrument;
                }
                if(isset($result->response->_embedded->defaultShippingAddress)){
                    $defaultShippingAddress = $result->response->_embedded->defaultShippingAddress;
                }
            }
        } catch (Exception $exception) {
            echo(json_encode($exception));
        }
    }
}
