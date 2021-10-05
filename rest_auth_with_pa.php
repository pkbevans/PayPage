<?php require_once 'PeRestlib/RestRequest.php';
$incoming = json_decode(file_get_contents('php://input'));
$reference_number = $incoming->order->referenceNumber;
// Key incoming fields:
//  "storeCard" : If true create a token
//  "paymentInstrumentId" : if true, use supplied Payment Instrument
//  "shippingAddressId" : if true, use supplied Shipping Address
//  "customerId" : If not blank and storeCard is true, then add new payment Instrument to existing Customer.
//  "transientToken" :  Always use - will either contain PAN+CVV for new cards or just CVV for existing payment Instruments
try {
    $request = new stdClass();

    if(!$incoming->standAlone) {
        $processingInfo = new stdClass();
        if($incoming->storeCard){
            $actionList = [$incoming->paAction, "TOKEN_CREATE"];
            if(empty($incoming->customerId)){
                $buyerInformation = [
                    "merchantCustomerID" => "Your customer identifier",
                    "email" => $incoming->order->bill_to->bill_to_email
                ];
                $request->buyerInformation = $buyerInformation;
                if($incoming->order->shippingAddressRequired){
                    $processingInfo->actionTokenTypes = ["customer", "paymentInstrument", "shippingAddress"];
                }else{
                    $processingInfo->actionTokenTypes = ["customer", "paymentInstrument"];
                }
            } else{
                // Add Payment Instrument and/or Shipping Address to existing Customer
                if($incoming->order->shippingAddressRequired && empty($incoming->shippingAddressId)){
                    $processingInfo->actionTokenTypes = ["paymentInstrument", "shippingAddress"];
                }else{
                    $processingInfo->actionTokenTypes = ["paymentInstrument"];
                }
            }
        } else {
            $actionList = [$incoming->paAction];
        }
        $processingInfo->capture = "true";
        $processingInfo->actionList = $actionList;
        $request->processingInformation = $processingInfo;
    }

    $orderInformation = [
        "amountDetails"=>[
            "totalAmount"=>$incoming->order->amount,
            "currency"=>$incoming->order->currency
        ]
    ];
    if(empty($incoming->paymentInstrumentId)){
        $billTo = [
            "firstName" => ($incoming->order->useShippingAsBilling?$incoming->order->ship_to->ship_to_forename :$incoming->order->bill_to->bill_to_forename),
            "lastName" => ($incoming->order->useShippingAsBilling?$incoming->order->ship_to->ship_to_surname :$incoming->order->bill_to->bill_to_surname),
            "address1" => ($incoming->order->useShippingAsBilling?$incoming->order->ship_to->ship_to_address_line1:$incoming->order->bill_to->bill_to_address_line1),
            "address2" => ($incoming->order->useShippingAsBilling?$incoming->order->ship_to->ship_to_address_line2: $incoming->order->bill_to->bill_to_address_line2),
            "locality" => ($incoming->order->useShippingAsBilling?$incoming->order->ship_to->ship_to_address_city: $incoming->order->bill_to->bill_to_address_city),
            "postalCode" => ($incoming->order->useShippingAsBilling?$incoming->order->ship_to->ship_to_postcode: $incoming->order->bill_to->bill_to_postcode),
            "country" => ($incoming->order->useShippingAsBilling?$incoming->order->ship_to->ship_to_address_country: $incoming->order->bill_to->bill_to_address_country),
            "email" => $incoming->order->bill_to->bill_to_email
        ];
        $orderInformation['billTo'] = $billTo;
    }

    if($incoming->order->shippingAddressRequired){
        if(empty($incoming->shippingAddressId)){
            $shipTo = [
                "firstName" => $incoming->order->ship_to->ship_to_forename,
                "lastName" => $incoming->order->ship_to->ship_to_surname,
                "address1" => $incoming->order->ship_to->ship_to_address_line1,
                "address2" => $incoming->order->ship_to->ship_to_address_line2,
                "locality" => $incoming->order->ship_to->ship_to_address_city,
                "postalCode" => $incoming->order->ship_to->ship_to_postcode,
                "country" => $incoming->order->ship_to->ship_to_address_country,
            ];
            $orderInformation['shipTo'] = $shipTo;
        }
    }
    $request->orderInformation = $orderInformation;

    if($incoming->standAlone) {
        $tokenInformation = [
            "transientToken" => $incoming->transientToken
        ];
    }else{
        if(!empty($incoming->paymentInstrumentId) ||
                !empty($incoming->shippingAddressId) ||
                ($incoming->storeCard && isset($incoming->customerId))){
            $paymentInformation = [
                "customer" => [
                    "id" => $incoming->customerId
                ]
            ];
            $request->paymentInformation = $paymentInformation;
        }
        if(!empty($incoming->shippingAddressId)){
            $request->paymentInformation['shippingAddress']['id'] = $incoming->shippingAddressId;
        }

        if(!empty($incoming->paymentInstrumentId)){
            $request->paymentInformation['paymentInstrument']['id'] = $incoming->paymentInstrumentId;
        }
        // Always meed the transient token
        $tokenInformation = [
            "jti" => $incoming->transientToken
        ];
    }
    $request->tokenInformation = $tokenInformation;

    $request->clientReferenceInformation = new stdClass();
    $request->clientReferenceInformation->code = $reference_number;

    if($incoming->paAction == "CONSUMER_AUTHENTICATION"){
        // PA Enrollment check
        if($incoming->storeCard){
            // If we are storing the card, then request a challenge
            $challengeCode = "04";
        }else{
            $challengeCode = "01";
        }
        if($incoming->local){
            $returnUrl = "http://localhost/payPage/redirect.php";
        }else{
            $returnUrl = "https://bondevans.com/payPage/redirect.php";
        }
        $consumerAuthenticationInformation = [
            "challengeCode"=> $challengeCode,
            "referenceId" => $incoming->referenceID,
            "returnUrl" => $returnUrl
        ];
    }else{
        // PA Validation
        $consumerAuthenticationInformation = [
            "authenticationTransactionId" => $incoming->authenticationTransactionID
        ];
    }
    $request->consumerAuthenticationInformation = $consumerAuthenticationInformation;

    $requestBody = json_encode($request);

    if($incoming->standAlone){
        if($incoming->paAction == "CONSUMER_AUTHENTICATION"){
            $api = API_RISK_V1_AUTHENTICATIONS;
        }else{
            $api = API_RISK_V1_AUTHENTICATION_RESULTS;
        }
    }else{
        $api = API_PAYMENTS;
    }
//   ProcessRequest($mid, $resource, $method, $payload, $child = null, $authentication = AUTH_TYPE_SIGNATURE )
    $result = ProcessRequest("peportfolio", $api , METHOD_POST, $requestBody, "pemid03", AUTH_TYPE_SIGNATURE );
    echo(json_encode($result));

} catch (Exception $exception) {
    echo(json_encode($exception));
}
