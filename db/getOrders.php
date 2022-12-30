<?php
include_once 'dbUtils.php';
$incoming = json_decode(file_get_contents('php://input'));
if (!$accessToken = refreshAccessToken()) {
    echo "Error: Refreshing token<BR>";
    // TODO - Login required
    exit;
}
// Access token refreshed - now get the orders
$url = 'http://'. $_SERVER['SERVER_NAME'] . '/payPage/v1/controller/orders.php?email='. $incoming->email. '&customerId=' . $incoming->customerId . '&mrn=' . $incoming->mrn .'&id=' . $incoming->orderId .'&status=' . $incoming->status;
if(!$response = fetch(METHOD_GET, $url, $accessToken, null)){
    echo "Error: Fetching orders<BR>";
    exit;
}
$orders = new stdClass();
$orders = $response->orders;
include '../view/listOrders.php';
