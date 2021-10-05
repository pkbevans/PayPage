<?php require_once 'PeRestlib/RestRequest.php';
///////////////////////////FUNCTIONS
/////////////////////////END FUNCTIONS
$customerToken = "";
$paymentInstruments = new stdClass();

if(isset($_REQUEST['customerToken'])){
//    $customerToken = filter_input(INPUT_POST, 'customerToken', FILTER_SANITIZE_SPECIAL_CHARS);
    $customerToken = $_REQUEST['customerToken'];
    if($customerToken){
        try {
            // Get Payment Instruments
            $api = str_replace('{customerId}', $customerToken, API_TMS_V2_CUSTOMER_PAYMENT_INSTRUMENTS);

            // $strResponse = API::sendRequest(API::TEST_URL,API::GET,$api, "peportfolio","{}",null,null,"pemid03" );
            $result = ProcessRequest("peportfolio", $api , METHOD_GET, "", "pemid03", AUTH_TYPE_SIGNATURE );
            // echo("<BR> BODY<PRE>" .json_encode($result, JSON_PRETTY_PRINT). "</PRE><BR>");
            if($result->responseCode === 200){
                if(isset($result->response->_embedded->paymentInstruments)){
                    $paymentInstruments = $result->response->_embedded->paymentInstruments;
                    $jsonPaymentInstruments = json_encode($paymentInstruments);
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
