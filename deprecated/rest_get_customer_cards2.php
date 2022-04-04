<?php require_once 'PeRestLib/RestRequest.php';
///////////////////////////FUNCTIONS
/////////////////////////END FUNCTIONS
function getCustomerCards($customerId){
    $paymentInstruments = new stdClass();
    try {
        // Get Payment Instruments
        $api = str_replace('{customerId}', $customerId, API_TMS_V2_CUSTOMER_PAYMENT_INSTRUMENTS);

        $result = ProcessRequest(PORTFOLIO, $api , METHOD_GET, "", MID, AUTH_TYPE_SIGNATURE );
        // echo("<BR> BODY<PRE>" .json_encode($result, JSON_PRETTY_PRINT). "</PRE><BR>");
        if($result->responseCode === 200){
            if(isset($result->response->_embedded->paymentInstruments)){
                $paymentInstruments = $result->response->_embedded->paymentInstruments;
                $jsonPaymentInstruments = json_encode($paymentInstruments);
            }else{
                // IGNORE
            }
        }else{
            // IGNORE
        }
    } catch (Exception $exception) {
        echo(json_encode($exception));
    }
}
