<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/payPage/common/v1/controller/db.php';

function insertPayment2($type, $orderDetails, $request){

    $authCode="";
    $requestId="";
    $cardType="";
    $captured=0;
    if(property_exists($request->response, "id")){
        $requestId = $request->response->id;
    }
    if($request->response->status === "AUTHORIZED"){
        $authCode = $request->response->processorInformation->approvalCode;
        if($orderDetails->capture){
            $captured = 1;
        }
    }
    if($request->responseCode === 201){
        if($type == "PAYMENT" && property_exists($request->response->paymentInformation, "card")){
          $cardType = $request->response->paymentInformation->card->type;
        }else{
          $cardType = "N/A";
        }
    }
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
                $orderDetails->orderId . "," .
                $orderDetails->amount . ",'" .
                $type . "'," .
                $captured . ",'" .
                $orderDetails->currency . "','" .
                $orderDetails->maskedPan . "','" .
                $cardType . "','" .
                $authCode . "','" .
                $requestId . "','" .
                $request->response->status . "')";

    // Only update Order Status for Payments - not Refunds
    if($type == "PAYMENT"){
        $orderSql = "UPDATE orders set " .
            "customerUserId = " . $orderDetails->customerUserId . "," .
            "customerId = '" . $orderDetails->customerId . "'," .
            "customerEmail = '" . $orderDetails->bill_to->email . "'," .
            "status = '" . $request->response->status . "' " .
            "WHERE id = " . $orderDetails->orderId .";";
    }

    $result= new stdClass();
    $result->paymentSql=$paymentSql;
    if($type == "PAYMENT"){
        $result->orderSql=$orderSql;
    }
    try{
        $conn = DB::connectReadDB();
        $conn->exec($paymentSql);
        $result->id=$conn->lastInsertId();
        if($type == "PAYMENT"){
            $conn->exec($orderSql);
        }
        // Update User with customerId if new customer token created for Guest user
        if($type == "PAYMENT" && $request->response->status === "AUTHORIZED" && $orderDetails->storeCard && empty($orderDetails->customerId)){
            $userSql = "UPDATE users set " .
                "customerId = '" . $request->response->tokenInformation->customer->id . "' " .
                "WHERE id = " . $orderDetails->customerUserId;
                $result->userSql=$userSql;
                $conn->exec($userSql);
        }
        $result->status="OK";
        unset($conn);
    } catch(PDOException $e){
        $result->status = "ERROR";
        $result->message = $e->getMessage();
    }
    return $result;
}
