<?php
require_once('../v1/controller/db.php');
function insertOrder($mrn, $amount, $currency, $customerId, $email, $status){

    $result= new stdClass();
    try{
        $conn = DB::connectWriteDB();
        $query = $conn->prepare('INSERT INTO orders (merchantReference, amount, currency, customerId, customerEmail, status) ' .
            'VALUES (:mrn, :amount, :currency, :customerId, :email, :status)');

        $query->bindParam(':mrn', $mrn, PDO::PARAM_STR);
        $query->bindParam(':amount', $amount, PDO::PARAM_STR);
        $query->bindParam(':currency', $currency, PDO::PARAM_STR);
        $query->bindParam(':customerId', $customerId, PDO::PARAM_STR);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->bindParam(':status', $status, PDO::PARAM_STR);
        $query->execute();

        $result->id=$conn->lastInsertId();
        $result->status="OK";
        unset($conn);
    }
    catch(PDOException $e){
        $result->status = "ERROR";
        $result->message = "Unable to insert order: " . $e->getMessage();
    }
    return $result;
}
