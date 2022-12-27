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
$jsonData = json_decode($response);
if(!$jsonData = json_decode($response)) {
    echo $response;
    exit;
}
$orders = new stdClass();
if($jsonData->statusCode == 200){
    $orders = $jsonData->data->orders;
    include '../view/listOrders.php';
}else{
    echo '<BR><pre>.' . json_encode($jsonData, JSON_PRETTY_PRINT) . '</pre>';
}