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
    if(!$response = fetch(METHOD_POST, $url, $accessToken, json_encode($payload))){
        echo "Error: inserting payment<BR>";
        exit;
    }
    return $response;
}
function updateOrder($accessToken, $id, $status){
    $payload = new stdClass();
    $payload->status = $status;

    $url = 'http://'. $_SERVER['SERVER_NAME'] . '/payPage/v1/controller/orders.php?orderId='.$id.'&patch=';
    if(!$response = fetch(METHOD_POST, $url, $accessToken, json_encode($payload))){
        echo "Error: updating order<BR>";
        exit;
    }    
    return $response;
}    
function insertApiLog($orderId, $type, $status, $payload){

    $logSql = "INSERT INTO apilogs ("
                . "orderId, "
                . "type, "
                . "status, "
                . "payload) " .
            "VALUES (" .
                $orderId . ",'" .
                $type . "','" .
                $status . "','" .
                $payload . "')";

    $result = new stdClass();
    echo $logSql;
    $result->sql = $logSql;
    try{
        $conn = DB::connectWriteDB();
        $conn->exec($logSql);
        $result->status = "OK";
        unset($conn);
    } catch(PDOException $e){
        $result->status = "ERROR";
    }
    return $result;
}
