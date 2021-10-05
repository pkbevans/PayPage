<?php require_once 'PeRestlib/RestRequest.php';
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
            $api = API_TMS_V2_CUSTOMERS ."/" . $customerToken;
            // $strResponse = API::sendRequest(API::TEST_URL,API::GET,$api, "peportfolio","{}",null,null,"pemid03" );
            $result = ProcessRequest("peportfolio", $api , METHOD_GET, "", "pemid03", AUTH_TYPE_SIGNATURE );
            if($result->responseCode === 200){
             // echo("<BR> BODY<PRE>" .json_encode($result, JSON_PRETTY_PRINT). "</PRE><BR>");
            }

            // Get Payment Instruments
            $api = str_replace('{customerId}', $customerToken, API_TMS_V2_CUSTOMER_PAYMENT_INSTRUMENTS);

            // $strResponse = API::sendRequest(API::TEST_URL,API::GET,$api, "peportfolio","{}",null,null,"pemid03" );
            $result = ProcessRequest("peportfolio", $api , METHOD_GET, "", "pemid03", AUTH_TYPE_SIGNATURE );
            if($result->responseCode === 200){
                $paymentInstrumentCount = $result->response->count;
                // echo("<BR> PAYMENT INSTRUMENTS:<PRE>" .json_encode($result, JSON_PRETTY_PRINT). "</PRE><BR>");
                if(isset($result->response->_embedded->paymentInstruments)){
                    $storedCards = $result->response->_embedded->paymentInstruments;

                    foreach ($storedCards as $storedCard) {
                        // Add default address line 2 if non-existant
                        if(!isset($storedCard->address2)){
                            $storedCard->billTo->address2 = "";
                        }
                        // Find Default Payment Instrument - use this for the default Billing Details
                        if($storedCard->default){
                            $defaultPaymentInstrument = $storedCard;
                        }
                     // echo "<BR> GOT: ID" . $storedCard->id . " State:" . $storedCard->state . " Type: " . $storedCard->card->type . " Default: " .$storedCard->default .   "<BR>";
                    }
                    $billToText = concatinateNameAddress($defaultPaymentInstrument->billTo);
                }
            }

            // Get Shipping Addresses
            $api = str_replace('{customerId}', $customerToken, API_TMS_V2_CUSTOMER_SHIPPING_ADDRESSES);

            // $strResponse = API::sendRequest(API::TEST_URL,API::GET,$api, "peportfolio","{}",null,null,"pemid03" );
            $result = ProcessRequest("peportfolio", $api , METHOD_GET, "", "pemid03", AUTH_TYPE_SIGNATURE );
            if($result->responseCode === 200){
                // echo("<BR> BODY<PRE>" .json_encode($result, JSON_PRETTY_PRINT). "</PRE><BR>");
                if(isset($result->response->_embedded->shippingAddresses)){
                    $shippingAddresses = $result->response->_embedded->shippingAddresses;
                    foreach ($shippingAddresses as $shippingAddress) {
                        // Find Default Shipping address - use this for the default Shipping Details
                        if($shippingAddress->default){
                            $defaultShippingAddress = $shippingAddress;
                            $shippingAddressAvailable = true;
                        }
                     // echo "<BR> GOT: ID" . $storedCard->id . " State:" . $storedCard->state . " Type: " . $storedCard->card->type . " Default: " .$storedCard->default .   "<BR>";
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
        } catch (Exception $exception) {
            echo(json_encode($exception));
        }
    }
}
