<?php require_once 'restlib/API.php';
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
$paymentInstrumentCount = 0;
$shippingAddressAvailable = false;
$customerToken = "";
$storedCards = new stdClass();
$shippingAddresses = new stdClass();
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
            $api = str_replace('{customerId}', $customerToken, API::TMS_V2_CUSTOMERS_id);
            $strResponse = API::sendRequest(API::TEST_URL,API::GET,$api, "peportfolio","{}",null,null,"pemid03" );
            $result = new stdClass();
            $objResponse = json_decode($strResponse);
            $result->httpCode = $objResponse->response->httpCode;
            if($result->httpCode == 200){
                $strResponseBody=$objResponse->response->body;
                $jsonBody = json_decode($strResponseBody);
//              echo("<BR> BODY<PRE>" .json_encode($jsonBody, JSON_PRETTY_PRINT). "</PRE><BR>");
            }
            $strResponse = API::sendRequest(API::TEST_URL,API::GET,$api, "peportfolio","{}",null,null,"pemid03" );
            $result = new stdClass();

            // Get Payment Instruments
            $api = str_replace('{customerId}', $customerToken, API::TMS_V2_CUSTOMERS_id_PAYMENTINSTRUMENTS);

            $strResponse = API::sendRequest(API::TEST_URL,API::GET,$api, "peportfolio","{}",null,null,"pemid03" );
            $result = new stdClass();
            $objResponse = json_decode($strResponse);
            $result->httpCode = $objResponse->response->httpCode;
            if($result->httpCode == 200){
                $strResponseBody=$objResponse->response->body;
                $jsonBody = json_decode($strResponseBody);
                $paymentInstrumentCount = $jsonBody->count;
//              echo("<BR> BODY<BR>" .json_encode($jsonBody). "<BR><BR>");
                if(isset($jsonBody->_embedded->paymentInstruments)){
                    $storedCards = $jsonBody->_embedded->paymentInstruments;
                    foreach ($storedCards as $storedCard) {
                        // Add default address line 2 if non-existant
                        if(!isset($storedCard->address2)){
                            $storedCard->billTo->address2 = "";
                        }
                        // Find Default Payment Instrument - use this for the default Billing Details
                        if($storedCard->default){
                            $defaultPaymentInstrument = $storedCard;
                        }
        //              echo "<BR> GOT: ID" . $storedCard->id . " State:" . $storedCard->state . " Type: " . $storedCard->card->type . " Default: " .$storedCard->default .   "<BR>";
                    }
                    $billToText = concatinateNameAddress($defaultPaymentInstrument->billTo);
                }
            }

//            if($paymentInstrumentCount){
            // Get Shipping Addresses
            $api = str_replace('{customerId}', $customerToken, API::TMS_V2_CUSTOMERS_id_SHIPPINGADDRESSSES);

            $strResponse = API::sendRequest(API::TEST_URL,API::GET,$api, "peportfolio","{}",null,null,"pemid03" );
            $result = new stdClass();
            $objResponse = json_decode($strResponse);
            $result->httpCode = $objResponse->response->httpCode;
            if($result->httpCode == 200){
                $strResponseBody=$objResponse->response->body;
                $jsonBody = json_decode($strResponseBody);
    //                  echo("<BR> BODY<BR>" .json_encode($jsonBody). "<BR><BR>");
                if(isset($jsonBody->_embedded->shippingAddresses)){
                    $shippingAddresses = $jsonBody->_embedded->shippingAddresses;
        //            $jsonStoredCards = json_encode($storedCards);
                    foreach ($shippingAddresses as $shippingAddress) {
                        // Find Default Shipping address - use this for the default Shipping Details
                        if($shippingAddress->default){
                            $defaultShippingAddress = $shippingAddress;
                            $shippingAddressAvailable = true;
                        }
        //              echo "<BR> GOT: ID" . $storedCard->id . " State:" . $storedCard->state . " Type: " . $storedCard->card->type . " Default: " .$storedCard->default .   "<BR>";
                    }
                    $shipToText = concatinateNameAddress($defaultShippingAddress->shipTo);
                }else{
                    // Somehow, no Shipping addresses
                    setDefaultShippingAddress();
                }
            }else{
                    // Somehow, no Shipping addresses
                setDefaultShippingAddress();
            }
    //            }
        } catch (Exception $exception) {
            echo(json_encode($exception));
        }
    }
}