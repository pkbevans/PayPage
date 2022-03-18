<?php
include_once($_SERVER['DOCUMENT_ROOT']."/ppSecure/Credentials.php");

function insertPayment($orderId, $customerId, $amount, $email, $cardNumber, $cardType, $status){
    global $servername,$username,$password,$dbName;
    
    $paymentSql = "INSERT INTO payments (orderId, amount, cardNumber, cardType, status)" .
            " VALUES (" . $orderId . "," . $amount . ",'" . $cardNumber . "','" . $cardType . "','" . $status ."')";
    
    $orderSql = "UPDATE orders set " .
            "customerId = '" . $customerId . "'," .
            "customerEmail = '" . $email . "'," .
            "status = '" . $status . "' " .
            "WHERE id = " . $orderId .";";
    
    $result= new stdClass();
    $result->paymentSql=$paymentSql;
    $result->orderSql=$orderSql;
    try{
        $conn = new PDO("mysql:host=$servername;dbname=".$dbName, $username, $password);
        $conn->exec($paymentSql);
        $result->id=$conn->lastInsertId();
        $conn->exec($orderSql);
        $result->status="OK";
        unset($conn);
    } catch(PDOException $e){
        $result->status = "ERROR";
        $result->message = $e->getMessage();
    }
    return $result;
}
