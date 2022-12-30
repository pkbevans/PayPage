<?php
include_once 'dbUtils.php';
$incoming = json_decode(file_get_contents('php://input'));
if (!$accessToken = refreshAccessToken()) {
    echo "Error: Refreshing token<BR>";
    // TODO - Login required
    exit;
}
// Access token refreshed - now get the payment
$url = 'http://'. $_SERVER['SERVER_NAME'] . '/payPage/v1/controller/payments.php?paymentId=' . $incoming->paymentId;
if(!$response = fetch(METHOD_GET, $url, $accessToken, null)){
    echo "Error: Fetching payment<BR>";
    exit;
}
$payment = new stdClass();
$payment = $response->payments[0];
// Get the order
$url = 'http://'. $_SERVER['SERVER_NAME'] . '/payPage/v1/controller/orders.php?orderId=' . $payment->orderId;
if(!$response = fetch(METHOD_GET, $url, $accessToken, null)){
    echo "Error: Fetching order<BR>";
    exit;
}
// var_dump($response);
$order = new stdClass();
$order = $response->orders[0];
