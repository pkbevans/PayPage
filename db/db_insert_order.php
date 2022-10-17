<?php
include_once($_SERVER['DOCUMENT_ROOT']."/ppSecure/Credentials.php");

function insertOrder($mrn, $amount, $currency, $customerToken, $email, $status){
    global $servername,$username,$password, $dbName;

    $sql = "INSERT INTO orders (merchantReference, amount, currency, customerId, customerEmail, status)" .
            " VALUES ('" . $mrn . "'," . $amount . ",'" . $currency . "','" . $customerToken . "','" . $email . "','" . $status ."')";

    $result= new stdClass();
    try{
        $conn = new PDO("mysql:host=$servername;dbname=". $dbName, $username, $password);
        $conn->exec($sql);
        $result->id=$conn->lastInsertId();
        $result->status="OK";
        unset($conn);
    } catch(PDOException $e){
        $result->status = "ERROR";
        $result->message = "Could not able to execute $sql. " . $e->getMessage();
    }
    return $result;
}
