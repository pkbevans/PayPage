<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/common/db/dbUtils.php';

$incoming = json_decode(file_get_contents('php://input'));
$url = 'http://'. $_SERVER['SERVER_NAME'] . '/payPage/common/v1/controller/payments.php?paymentId=' . $incoming->paymentId;
[$responseCode, $response] = fetch($incoming->accessToken, METHOD_GET, $url, null);
if($responseCode != 200){
    http_response_code($responseCode);
    echo "Error getting Payment: ". $response;
    exit;
}
$payment = new stdClass();
$payment = $response->payments[0];
// Get the order
$url = 'http://'. $_SERVER['SERVER_NAME'] . '/payPage/common/v1/controller/orders.php?orderId=' . $payment->orderId;
[$responseCode, $response] = fetch($incoming->accessToken, METHOD_GET, $url, null);
if($responseCode != 200){
    http_response_code($responseCode);
    echo "Error getting order: ". $response;
    exit;
}
// var_dump($response);
$order = new stdClass();
$order = $response->orders[0];
http_response_code($responseCode);
include '../view/create' . $incoming->action. '.php';
