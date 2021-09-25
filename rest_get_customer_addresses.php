<?php require_once 'restlib/API.php';
///////////////////////////FUNCTIONS
/////////////////////////END FUNCTIONS
$customerToken = "";
$shippingAddresses = new stdClass();

if(isset($_REQUEST['customerToken'])){
//    $customerToken = filter_input(INPUT_POST, 'customerToken', FILTER_SANITIZE_SPECIAL_CHARS);
    $customerToken = $_REQUEST['customerToken'];
    if($customerToken){
        try {
            // Get Shipping Addresses
            $api = str_replace('{customerId}', $customerToken, API::TMS_V2_CUSTOMERS_id_SHIPPINGADDRESSSES);

            $strResponse = API::sendRequest(API::TEST_URL,API::GET,$api, "peportfolio","{}",null,null,"pemid03" );
            $result = new stdClass();
            $objResponse = json_decode($strResponse);
            $result->httpCode = $objResponse->response->httpCode;
            if($result->httpCode == 200){
                $strResponseBody=$objResponse->response->body;
                $jsonBody = json_decode($strResponseBody);
//                echo("<BR> BODY<BR>" .json_encode($jsonBody). "<BR><BR>");
                if(isset($jsonBody->_embedded->shippingAddresses)){
                    $shippingAddresses = $jsonBody->_embedded->shippingAddresses;
                    $jsonShippingAddresses = json_encode($shippingAddresses);
                }else{
                    // ERROR
                }
            }else{
                // ERROR
            }
        } catch (Exception $exception) {
            echo(json_encode($exception));
        }
    }
}