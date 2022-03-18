<?php
$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "../ppSecure/Credentials.php";
include_once($path);

function insertPayment($orderId, $customerId, $email, $cardNumber, $cardType, $status){
    global $servername,$username,$password;
    
    $paymentSql = "INSERT INTO payments (orderId, cardNumber, cardType, status)" .
            " VALUES (" . $orderId . ",'" . $cardNumber . "','" . $cardType . "','" . $status ."')";
    
    $orderSql = "UPDATE orders set " .
            "customerId = '" . $customerId . "'," .
            "customerEmail = '" . $email . "'," .
            "status = '" . $status . "' " .
            "WHERE id = " . $orderId .";";
    
    $result= new stdClass();
    $result->paymentSql=$paymentSql;
    $result->orderSql=$orderSql;
    try{
        $conn = new PDO("mysql:host=$servername;dbname=paypage", $username, $password);
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
