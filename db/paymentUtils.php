<?php
include_once($_SERVER['DOCUMENT_ROOT']."/ppSecure/Credentials.php");

function insertPayment($type, $orderId, $amount, $captured, $currency,
                    $cardNumber, $cardType, $authCode, $requestId, $status){
    global $servername,$username,$password,$dbName;

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
        $conn = new PDO("mysql:host=$servername;dbname=".$dbName, $username, $password);
        $conn->exec($paymentSql);
        unset($conn);
    } catch(PDOException $e){
        $result = "ERROR";
    }
    return $result;
}

function updateOrder($id, $status){
    global $servername,$username,$password,$dbName;

    $orderSql = "UPDATE orders set " .
        "status = '" . $status . "' " .
        "WHERE id = " . $id .";";

    $result = "OK";
    try{
        $conn = new PDO("mysql:host=$servername;dbname=".$dbName, $username, $password);
        $conn->exec($orderSql);
        unset($conn);
    } catch(PDOException $e){
        $result = "ERROR";
    }
    return $result;
}
function insertApiLog($orderId, $type, $status, $payload){
    global $servername,$username,$password,$dbName;

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
        $conn = new PDO("mysql:host=$servername;dbname=".$dbName, $username, $password);
        $conn->exec($logSql);
        $result->status = "OK";
        unset($conn);
    } catch(PDOException $e){
        $result->status = "ERROR";
    }
    return $result;
}