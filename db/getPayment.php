<?php
include_once 'dbUtils.php';
$incoming = json_decode(file_get_contents('php://input'));
$url = 'http://'. $_SERVER['SERVER_NAME'] . '/payPage/v1/controller/payments.php?paymentId=' . $incoming->paymentId;
[$responseCode, $response] = fetch(METHOD_GET, $url, null);
if($responseCode != 200){
    http_response_code($responseCode);
    echo "Error: Fetching payment<BR>";
    exit;
}
$payment = new stdClass();
$payment = $response->payments[0];
// Get the order
$url = 'http://'. $_SERVER['SERVER_NAME'] . '/payPage/v1/controller/orders.php?orderId=' . $payment->orderId;
[$responseCode, $response] = fetch(METHOD_GET, $url, null);
if($responseCode != 200){
    http_response_code($responseCode);
    echo "Error: Fetching order<BR>";
    exit;
}
// var_dump($response);
$order = new stdClass();
$order = $response->orders[0];
http_response_code($responseCode);
