<?php
require_once('../v1/controller/db.php');
include_once 'dbUtils.php';
function insertPayment($accessToken, $type, $orderId, $amount, $captured, $currency,
            $cardNumber, $cardType, $authCode, $requestId, $status){

    $payload = new stdClass();
    $payload->type = $type;
    $payload->orderId = $orderId;
    $payload->amount = $amount;
    $payload->captured = $captured;
    $payload->currency = $currency;
    $payload->cardNumber = $cardNumber;
    $payload->cardType = $cardType;
    $payload->authCode = $authCode;
    $payload->gatewayRequestId = $requestId;
    $payload->status = $status;

    $url = 'http://'. $_SERVER['SERVER_NAME'] . '/payPage/v1/controller/payments.php';
    [$responseCode, $response] = fetch($accessToken, METHOD_POST, $url, json_encode($payload));
    if($responseCode != 201){
        http_response_code($responseCode);
        echo "Error: ". $response;
        exit;
    }
    return $response;
}
function updateOrder($accessToken, $id, $status){
    $payload = new stdClass();
    $payload->status = $status;

    $url = 'http://'. $_SERVER['SERVER_NAME'] . '/payPage/v1/controller/orders.php?orderId='.$id.'&patch=';
    [$responseCode, $response] = fetch($accessToken, METHOD_POST, $url, json_encode($payload));
    if($responseCode != 200){
        http_response_code($responseCode);
        echo "Error: ". $response;
        exit;
    }    
    return $response;
}