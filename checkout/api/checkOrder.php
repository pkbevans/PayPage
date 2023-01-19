<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/payPage/common/v1/controller/db.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/payPage/common/v1/controller/ordersFunctions.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/payPage/common/v1/model/Order.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/payPage/common/v1/model/Response.php';

function checkOrder($id, $hash)
{
    // attempt to set up connections to read and write db connections
    try {
        $readDB = DB::connectReadDB();
    } catch (PDOException $ex) {
        // log connection error for troubleshooting and return a json error response
        error_log("Connection Error: " . $ex, 0);
        return false;
    }
    $response = getOrders($readDB, $id, null, 0, 0, "");
    if($response->success()){
        $order = $response->getData();
        $orderHash=$order['orders'][0]['hash'];
        if($orderHash !== $hash){
            return false;
        }
        return $order['orders'][0];
    }else{
        return false;
    }
}