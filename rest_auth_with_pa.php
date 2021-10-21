<?php require_once 'PeRestLib/RestRequest.php';
$incoming = json_decode(file_get_contents('php://input'));
$reference_number = $incoming->order->referenceNumber;
// Key incoming fields:
//  "storeCard" : If true create a token
//  "paymentInstrumentId" : if true, use supplied Payment Instrument
//  "shippingAddressId" : if not blank, use supplied Shipping Address
//  "customerId" : If not blank and storeCard is true, then add new payment Instrument to existing Customer.
//  "transientToken" :  Always use - will either contain PAN+CVV for new cards or just CVV for existing payment Instruments
try {
    $request = new stdClass();

    if(!$incoming->standAlone) {
        $processingInfo = new stdClass();
        // Dont capture a zero-value auth
        $processingInfo->capture = $incoming->order->amount>0?$incoming->capture:false;
        if($incoming->storeCard){
            if($incoming->paAction === "NO_PA"){
                $actionList = ["TOKEN_CREATE"];
            }else{
                $actionList = [$incoming->paAction, "TOKEN_CREATE"];
            }
            if(empty($incoming->customerId)){
                $buyerInformation = [
                    "merchantCustomerID" => "Your customer identifier",
                    "email" => $incoming->order->bill_to->email
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
            "firstName" => substr(($incoming->order->useShippingAsBilling?$incoming->order->ship_to->firstname :$incoming->order->bill_to->firstname), 0, MAXSIZE_NAME),
            "lastName" => substr(($incoming->order->useShippingAsBilling?$incoming->order->ship_to->lastname :$incoming->order->bill_to->lastname), 0, MAXSIZE_NAME),
            "address1" => substr(($incoming->order->useShippingAsBilling?$incoming->order->ship_to->address1:$incoming->order->bill_to->address1), 0, MAXSIZE_ADDRESS),
            "address2" => substr(($incoming->order->useShippingAsBilling?$incoming->order->ship_to->address2: $incoming->order->bill_to->address2), 0, MAXSIZE_ADDRESS),
            "locality" => substr(($incoming->order->useShippingAsBilling?$incoming->order->ship_to->locality: $incoming->order->bill_to->locality), 0, MAXSIZE_CITY),
            "postalCode" => substr(($incoming->order->useShippingAsBilling?$incoming->order->ship_to->postalCode: $incoming->order->bill_to->postalCode), 0, MAXSIZE_POSTCODE),
            "country" => substr(($incoming->order->useShippingAsBilling?$incoming->order->ship_to->country: $incoming->order->bill_to->country), 0, MAXSIZE_COUNTRY),
            "email" => $incoming->order->bill_to->email
        ];
        $orderInformation['billTo'] = $billTo;
    }

    if($incoming->order->shippingAddressRequired){
        if(empty($incoming->shippingAddressId)){
            $shipTo = [
                "firstName" => substr($incoming->order->ship_to->firstname, 0, MAXSIZE_NAME),
                "lastName" => substr($incoming->order->ship_to->lastname, 0, MAXSIZE_NAME),
                "address1" => substr($incoming->order->ship_to->address1, 0, MAXSIZE_ADDRESS),
                "address2" => substr($incoming->order->ship_to->address2, 0, MAXSIZE_ADDRESS),
                "locality" => substr($incoming->order->ship_to->locality, 0, MAXSIZE_CITY),
                "postalCode" => substr($incoming->order->ship_to->postalCode, 0, MAXSIZE_POSTCODE),
                "country" => substr($incoming->order->ship_to->country, 0, MAXSIZE_COUNTRY),
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
    }else if($incoming->paAction == "VALIDATE_CONSUMER_AUTHENTICATION"){
        // PA Validation
        $consumerAuthenticationInformation = [
            "authenticationTransactionId" => $incoming->authenticationTransactionID
        ];
    }else{
        $consumerAuthenticationInformation = [];    // empty
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
