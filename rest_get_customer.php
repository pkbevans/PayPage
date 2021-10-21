<?php require_once 'PeRestLib/RestRequest.php';
///////////////////////////FUNCTIONS
function concatinateNameAddress($nameAddress){
    // return name and address string
    if(!isset($nameAddress->address2)){
        $nameAddress->address2 = "";
    }
    return xtrim($nameAddress->firstName, " ") .
            xtrim($nameAddress->lastName, "\n") .
            xtrim($nameAddress->address1, ", ") .
            xtrim($nameAddress->address2, ", ") .
            xtrim($nameAddress->locality, ", ") .
            xtrim($nameAddress->postalCode, ", ") .
            xtrim($nameAddress->country, ".");
}

function xtrim($in, $suffix){
    $out = trim($in);
    return (empty($out)? "" : $out . $suffix );
}

function setDefaultShippingAddress(){
    global $defaultShippingAddress;
    $defaultShippingAddress = new stdClass();
    $defaultShippingAddress->firstName = "";
    $defaultShippingAddress->lastName = "";
    $defaultShippingAddress->address1 = "";
    $defaultShippingAddress->address2 = "";
    $defaultShippingAddress->locality = "";
    $defaultShippingAddress->postalCode = "";
    $defaultShippingAddress->country = "";
}
/////////////////////////END FUNCTIONS
//$paymentInstrumentCount = 0;
$shippingAddressAvailable = false;
$customerToken = "";
//$storedCards = new stdClass();
//$shippingAddresses = new stdClass();
$defaultPaymentInstrument="";
$defaultShippingAddress;
$billToText="";
$shipToText="";

if(isset($_REQUEST['customerToken'])){
//    $customerToken = filter_input(INPUT_POST, 'customerToken', FILTER_SANITIZE_SPECIAL_CHARS);
    $customerToken = $_REQUEST['customerToken'];
    if($customerToken){
        try {
            // Get Customer TODO - NOT REQUIRED
            $api = API_TMS_V2_CUSTOMERS ."/" . $customerToken;
            // $strResponse = API::sendRequest(API::TEST_URL,API::GET,$api, "peportfolio","{}",null,null,"pemid03" );
            $result = ProcessRequest("peportfolio", $api , METHOD_GET, "", "pemid03", AUTH_TYPE_SIGNATURE );
            if($result->responseCode === 200){
//                echo("<BR> BODY<PRE>" .json_encode($result, JSON_PRETTY_PRINT). "</PRE><BR>");
                if(isset($result->response->_embedded->defaultPaymentInstrument)){
                    $defaultPaymentInstrument = $result->response->_embedded->defaultPaymentInstrument;
                    $billToText = concatinateNameAddress($defaultPaymentInstrument->billTo);
                }
                if(isset($result->response->_embedded->defaultShippingAddress)){
                    $defaultShippingAddress = $result->response->_embedded->defaultShippingAddress;
                    $shipToText = concatinateNameAddress($defaultShippingAddress->shipTo);
                }
            }
        } catch (Exception $exception) {
            echo(json_encode($exception));
        }
    }
}
