<?php 
require_once 'PeRestLib/RestRequest.php';
require_once 'php/utils/logApi.php';
include_once 'db/db_insert_payment.php';


$incoming = json_decode(file_get_contents('php://input'));
// Key incoming fields:
//  "storeCard" : If true create a token
//  "paymentInstrumentId" : if true, use supplied Payment Instrument
//  "shippingAddressId" : if not blank, use supplied Shipping Address
//  "customerId" : If not blank and storeCard is true, then add new payment Instrument to existing Customer.
//  "flexToken" :  Always use - will either contain PAN+CVV for new cards or just CVV for existing payment Instruments
try {
    $request = new stdClass();

    $processingInfo = new stdClass();
    // Dont capture a zero-value auth
    $processingInfo->capture = $incoming->order->amount>0?$incoming->order->capture:false;
    if($incoming->order->storeCard){
        if($incoming->paAction === "NO_PA"){
            $actionList = ["TOKEN_CREATE"];
        }else{
            $actionList = [$incoming->paAction, "TOKEN_CREATE"];
        }
        if(empty($incoming->order->customerId)){
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
            if($incoming->order->shippingAddressRequired && empty($incoming->order->shippingAddressId)){
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

    $orderInformation = [
        "amountDetails"=>[
            "totalAmount"=>$incoming->order->amount,
            "currency"=>$incoming->order->currency
        ]
    ];
    if(empty($incoming->order->paymentInstrumentId)){
        $billTo = [
            "firstName" => substr(($incoming->order->useShippingAsBilling?$incoming->order->ship_to->firstName :$incoming->order->bill_to->firstName), 0, MAXSIZE_NAME),
            "lastName" => substr(($incoming->order->useShippingAsBilling?$incoming->order->ship_to->lastName :$incoming->order->bill_to->lastName), 0, MAXSIZE_NAME),
            "address1" => substr(($incoming->order->useShippingAsBilling?$incoming->order->ship_to->address1:$incoming->order->bill_to->address1), 0, MAXSIZE_ADDRESS),
            "address2" => substr(($incoming->order->useShippingAsBilling?$incoming->order->ship_to->address2: $incoming->order->bill_to->address2), 0, MAXSIZE_ADDRESS),
            "locality" => substr(($incoming->order->useShippingAsBilling?$incoming->order->ship_to->locality: $incoming->order->bill_to->locality), 0, MAXSIZE_CITY),
            "postalCode" => substr(($incoming->order->useShippingAsBilling?$incoming->order->ship_to->postalCode: $incoming->order->bill_to->postalCode), 0, MAXSIZE_POSTCODE),
            "country" => substr(($incoming->order->useShippingAsBilling?$incoming->order->ship_to->country: $incoming->order->bill_to->country), 0, MAXSIZE_COUNTRY),
            "email" => $incoming->order->bill_to->email
        ];
    }else{
        $billTo = [
            "email" => $incoming->order->bill_to->email     // Email may have been updated/changed
        ];
    }
    $orderInformation['billTo'] = $billTo;

    if($incoming->order->shippingAddressRequired){
        if(empty($incoming->order->shippingAddressId)){
            $shipTo = [
                "firstName" => substr($incoming->order->ship_to->firstName, 0, MAXSIZE_NAME),
                "lastName" => substr($incoming->order->ship_to->lastName, 0, MAXSIZE_NAME),
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

    if(!empty($incoming->order->paymentInstrumentId) ||
            !empty($incoming->order->shippingAddressId) ||
            ($incoming->order->storeCard && !empty($incoming->order->customerId))){
        $paymentInformation = [
            "customer" => [
                "id" => $incoming->order->customerId
            ]
        ];
        $request->paymentInformation = $paymentInformation;
    }
    if(!empty($incoming->order->shippingAddressId)){
        $request->paymentInformation['shippingAddress']['id'] = $incoming->order->shippingAddressId;
    }

    if(!empty($incoming->order->paymentInstrumentId)){
        $request->paymentInformation['paymentInstrument']['id'] = $incoming->order->paymentInstrumentId;
    }
    // Always meed the transient token
    $tokenInformation = [
        "jti" => $incoming->order->flexToken
    ];

    $request->tokenInformation = $tokenInformation;

    $request->clientReferenceInformation = new stdClass();
    $request->clientReferenceInformation->code = $incoming->order->referenceNumber;

    if($incoming->paAction == "CONSUMER_AUTHENTICATION"){
        // PA Enrollment check
        if($incoming->order->storeCard){
            // If we are storing the card, then request a challenge
            $challengeCode = "04";
        }else{
            $challengeCode = "01";
        }
        if($incoming->order->local){
            $returnUrl = "http://localhost/payPage/redirect.php";
        }else{
            $returnUrl = TARGET_ORIGIN . "/payPage/redirect.php";
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

    $result = ProcessRequest(PORTFOLIO, API_PAYMENTS, METHOD_POST, $requestBody, MID, AUTH_TYPE_SIGNATURE );
    // Update DB
    $dbResult=insertPayment($incoming->order->orderId, 
            $incoming->order->customerId, 
            $incoming->order->amount, 
            $incoming->order->bill_to->email, 
            $incoming->order->maskedPan, 
            $result->response->paymentInformation->card->type, 
            $result->response->status);
    $result->payment = $dbResult;

    $json = json_encode($result);
    
    logApi($incoming->order->referenceNumber, 
            "auth-". $incoming->paAction,           // API Type
            $result->response->status,              // Status
            $incoming->order->amount,               // Amount
            $incoming->order->storeCard,            // Token created? 
            $json);                                 // Complete request + response
    echo($json);
} catch (Exception $exception) {
    echo(json_encode($exception));
}
