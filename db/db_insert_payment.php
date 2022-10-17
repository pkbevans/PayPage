<?php
include_once($_SERVER['DOCUMENT_ROOT']."/ppSecure/Credentials.php");

function insertPayment($orderDetails, $request){
    global $servername,$username,$password,$dbName;

    $authCode="";
    $requestId="";
    $cardType="";
    if($request->response->status === "AUTHORIZED"){
        $authCode = $request->response->processorInformation->approvalCode;
    }
    if($request->responseCode === 201){
        $requestId = $request->response->id;
        if(property_exists($request->response->paymentInformation, "card")){
          $cardType = $request->response->paymentInformation->card->type;
        }else{
          $cardType = "N/A";
        }
    }
    $paymentSql = "INSERT INTO payments ("
                . "orderId, "
                . "amount, "
                . "cardNumber, "
                . "cardType, "
                . "authCode, "
                . "gatewayRequestId, "
                . "status) " .
            "VALUES (" .
                $orderDetails->orderId . "," .
                $orderDetails->amount . ",'" .
                $orderDetails->maskedPan . "','" .
                $cardType . "','" .
                $authCode . "','" .
                $requestId . "','" .
                $request->response->status . "')";

    $orderSql = "UPDATE orders set " .
            "customerId = '" . $orderDetails->customerId . "'," .
            "customerEmail = '" . $orderDetails->bill_to->email . "'," .
            "status = '" . $request->response->status . "' " .
            "WHERE id = " . $orderDetails->orderId .";";

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
