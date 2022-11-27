<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/payPage/PeRestLib/RestRequest.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/payPage/php/utils/logApi.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/db/db_insert_payment.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/payPage/php/utils/addresses.php';

$incoming = json_decode(file_get_contents('php://input'));
// Key incoming fields:
//  "storeCard" : If true create a token
//  "buyNow" : If true use Customer Token only (i.e. default paymentInstrument and ShippingId)
//  "paymentInstrumentId" : if true, use supplied Payment Instrument
//  "shippingAddressId" : if not blank, use supplied Shipping Address
//  "customerId" : If not blank and storeCard is true, then add new payment Instrument to existing Customer.
//  "flexToken" :  Always use - will either contain PAN+CVV for new cards or just CVV for existing payment Instruments
try {
    $request = new stdClass();

    $processingInfo = new stdClass();
    // Dont capture a zero-value auth
    $processingInfo->capture = $incoming->order->amount>0?$incoming->order->capture:false;
    if($incoming->order->storeCard || $incoming->order->storeAddress){
        if($incoming->paAction === "NO_PA"){
            $actionList = ["TOKEN_CREATE"];
        }else{
            $actionList = [$incoming->paAction, "TOKEN_CREATE"];
        }
        if(empty($incoming->order->customerId)){
            // NEW CUSTOMER
            $buyerInformation = [
                "merchantCustomerID" => "Your customer identifier",
                "email" => $incoming->order->bill_to->email
            ];
            $request->buyerInformation = $buyerInformation;
            $processingInfo->actionTokenTypes = ["customer"];
            if($incoming->order->storeAddress){
                array_push($processingInfo->actionTokenTypes, "shippingAddress");
            }
            if($incoming->order->storeCard){
                array_push($processingInfo->actionTokenTypes, "paymentInstrument");
            }
        } else{
            // EXISTING CUSTOMER - Add Payment Instrument and/or Shipping Address to existing Customer
            if($incoming->order->storeAddress){
                $processingInfo->actionTokenTypes = ["shippingAddress"];
                if($incoming->order->storeCard){
                    array_push($processingInfo->actionTokenTypes, "paymentInstrument");
                }
            }else{
                $processingInfo->actionTokenTypes = ["paymentInstrument"];
            }
        }
    } else {
        $actionList = [$incoming->paAction];
    }
    $processingInfo->actionList = $actionList;
    $processingInfo->commerceIndicator = "internet";
    if(!empty($incoming->order->googlePayToken)){
        $processingInfo->paymentSolution = "012";
    }
    $request->processingInformation = $processingInfo;

    $orderInformation = [
        "amountDetails"=>[
            "totalAmount"=>$incoming->order->amount,
            "currency"=>$incoming->order->currency
        ]
    ];
    if(!$incoming->order->buyNow){
        if(empty($incoming->order->paymentInstrumentId)){
            $billTo = [
                "firstName" => substr(ppTrim(($incoming->order->useShippingAsBilling?$incoming->order->ship_to->firstName :$incoming->order->bill_to->firstName)), 0, MAXSIZE_NAME),
                "lastName" => substr(ppTrim(($incoming->order->useShippingAsBilling?$incoming->order->ship_to->lastName :$incoming->order->bill_to->lastName)), 0, MAXSIZE_NAME),
                "address1" => substr(ppTrim(($incoming->order->useShippingAsBilling?$incoming->order->ship_to->address1:$incoming->order->bill_to->address1)), 0, MAXSIZE_ADDRESS),
                "address2" => substr(ppTrim(($incoming->order->useShippingAsBilling?$incoming->order->ship_to->address2: $incoming->order->bill_to->address2)), 0, MAXSIZE_ADDRESS),
                "locality" => substr(ppTrim(($incoming->order->useShippingAsBilling?$incoming->order->ship_to->locality: $incoming->order->bill_to->locality)), 0, MAXSIZE_CITY),
                "administrativeArea" => substr(ppTrim(($incoming->order->useShippingAsBilling?$incoming->order->ship_to->administrativeArea: $incoming->order->bill_to->administrativeArea)), 0, MAXSIZE_CITY),
                "postalCode" => substr(ppTrim(($incoming->order->useShippingAsBilling?$incoming->order->ship_to->postalCode: $incoming->order->bill_to->postalCode)), 0, MAXSIZE_POSTCODE),
                "country" => substr(ppTrim(($incoming->order->useShippingAsBilling?$incoming->order->ship_to->country: $incoming->order->bill_to->country)), 0, MAXSIZE_COUNTRY),
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
                    "firstName" => substr(ppTrim($incoming->order->ship_to->firstName), 0, MAXSIZE_NAME),
                    "lastName" => substr(ppTrim($incoming->order->ship_to->lastName), 0, MAXSIZE_NAME),
                    "address1" => substr(ppTrim($incoming->order->ship_to->address1), 0, MAXSIZE_ADDRESS),
                    "address2" => substr(ppTrim($incoming->order->ship_to->address2), 0, MAXSIZE_ADDRESS),
                    "locality" => substr(ppTrim($incoming->order->ship_to->locality), 0, MAXSIZE_CITY),
                    "administrativeArea" => substr(ppTrim($incoming->order->ship_to->administrativeArea), 0, MAXSIZE_CITY),
                    "postalCode" => substr(ppTrim($incoming->order->ship_to->postalCode), 0, MAXSIZE_POSTCODE),
                    "country" => substr(ppTrim($incoming->order->ship_to->country), 0, MAXSIZE_COUNTRY),
                ];
                $orderInformation['shipTo'] = $shipTo;
            }
        }
    }
    $request->orderInformation = $orderInformation;

    if( $incoming->order->buyNow ||
            !empty($incoming->order->paymentInstrumentId) ||
            !empty($incoming->order->shippingAddressId) ||
            (($incoming->order->storeCard || $incoming->order->storeAddress)  && !empty($incoming->order->customerId))){
        $paymentInformation = [
            "customer" => [
                "id" => $incoming->order->customerId
            ]
        ];
        $request->paymentInformation = $paymentInformation;
    }
    if( !$incoming->order->buyNow ){
        if(!empty($incoming->order->shippingAddressId)){
            $request->paymentInformation['shippingAddress']['id'] = $incoming->order->shippingAddressId;
        }

        if(!empty($incoming->order->paymentInstrumentId)){
            $request->paymentInformation['paymentInstrument']['id'] = $incoming->order->paymentInstrumentId;
        }
        // Always meed the transient token - unless its a Google Pay token
        if(!empty($incoming->order->googlePayToken)){
            $request->paymentInformation['fluidData']['value'] = base64_encode($incoming->order->googlePayToken);
        }else{
            $tokenInformation = [
                "jti" => $incoming->order->flexToken
            ];
            $request->tokenInformation = $tokenInformation;
        }
    }

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
        $returnUrl = "https://" . (strstr($_SERVER['HTTP_HOST'],LOCALHOST_TARGET_ORIGIN)?LOCALHOST_TARGET_ORIGIN:PRODUCTION_TARGET_ORIGIN) . "/payPage/php/utils/redirect.php";
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

    $result = ProcessRequest(MID, API_PAYMENTS, METHOD_POST, $requestBody, CHILD_MID, AUTH_TYPE_SIGNATURE );

    try{
        // Update DB
        $dbResult=insertPayment2("PAYMENT", $incoming->order, $result);
        $result->payment = $dbResult;
    }catch(Exception $exception){
        $result->payment = "DB ERROR";
    }

    $json = json_encode($result);
    logApi($incoming->order->referenceNumber,
            "auth-". $incoming->paAction,           // API Type
            $result->response->status,              // Status
            $incoming->order->amount,               // Amount
            $incoming->order->storeCard,            // Token created?
            $json);                                 // Complete request + response

    if($result->responseCode == 201 || $result->responseCode == 202) {
        header('HTTP/1.1 ' . $result->responseCode . ' OK');
        echo json_encode($result->response);
    }else{
        header('HTTP/1.1 ' . $result->responseCode . ' ERROR');
    }
} catch (Exception $exception) {
    // echo(json_encode($exception));
    echo('{"status":"ERROR"}');
}