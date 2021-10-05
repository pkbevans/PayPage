<?php require_once 'PeRestlib/RestRequest.php';
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
            $api = str_replace('{customerId}', $customerToken, API_TMS_V2_CUSTOMER_SHIPPING_ADDRESSES);

            // $strResponse = API::sendRequest(API::TEST_URL,API::GET,$api, "peportfolio","{}",null,null,"pemid03" );
            $result = ProcessRequest("peportfolio", $api , METHOD_GET, "", "pemid03", AUTH_TYPE_SIGNATURE );
            if($result->responseCode === 200){
//                echo("<BR> BODY<BR>" .json_encode($jsonBody). "<BR><BR>");
                if(isset($result->response->_embedded->shippingAddresses)){
                    $shippingAddresses = $result->response->_embedded->shippingAddresses;
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
