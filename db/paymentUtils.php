<?php
require_once('../v1/controller/db.php');

function insertPayment($type, $orderId, $amount, $captured, $currency,
                    $cardNumber, $cardType, $authCode, $requestId, $status){

    $paymentSql = "INSERT INTO payments ("
                . "orderId, "
                . "amount, "
                . "type, "
                . "captured, "
                . "currency, "
                . "cardNumber, "
                . "cardType, "
                . "authCode, "
                . "gatewayRequestId, "
                . "status) " .
            "VALUES (" .
                $orderId . "," .
                $amount . ",'" .
                $type . "'," .
                $captured . ",'" .
                $currency . "','" .
                $cardNumber . "','" .
                $cardType . "','" .
                $authCode . "','" .
                $requestId . "','" .
                $status . "')";

    $result = "OK";
    try{
        $conn = DB::connectWriteDB();
        $conn->exec($paymentSql);
        unset($conn);
    } catch(PDOException $e){
        $result = "ERROR";
    }
    return $result;
}

function updateOrder($id, $status){

    $orderSql = "UPDATE orders set " .
        "status = '" . $status . "' " .
        "WHERE id = " . $id .";";

    $result = "OK";
    try{
        $conn = DB::connectWriteDB();
        $conn->exec($orderSql);
        unset($conn);
    } catch(PDOException $e){
        $result = "ERROR";
    }
    return $result;
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
