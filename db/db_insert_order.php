<?php
$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "../ppSecure/Credentials.php";
include_once($path);
   
function insertOrder($mrn, $amount, $currency, $customerToken, $email, $status){
    global $servername,$username,$password;
    
    $sql = "INSERT INTO orders (merchantReference, amount, currency, customerId, customerEmail, status)" .
            " VALUES ('" . $mrn . "'," . $amount . ",'" . $currency . "','" . $customerToken . "','" . $email . "','" . $status ."')";
    
    $result= new stdClass();
    try{
        $conn = new PDO("mysql:host=$servername;dbname=paypage", $username, $password);
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
