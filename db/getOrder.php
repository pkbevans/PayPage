<?php
include_once 'dbUtils.php';
$incoming = json_decode(file_get_contents('php://input'));
if (!$accessToken = refreshAccessToken()) {
    echo "Error: Refreshing token<BR>";
    // TODO - Login required
    exit;
}
// Access token refreshed - now get the order
$url = 'http://'. $_SERVER['SERVER_NAME'] . '/payPage/v1/controller/orders.php?orderId=' . $incoming->orderId ;
if(!$response = fetch(METHOD_GET, $url, $accessToken, null)){
    echo "Error: Fetching order<BR>";
    exit;
}
$order = new stdClass();
$order = $response->orders[0];
$payments = $response->orders[0]->payments;
// var_dump($order);
include '../view/viewOrder.php';
function isDateToday($dateString){
    $today = date('Y-m-d');
    if(strncmp($dateString, $today, 10)){
        return false;
    }else{
        return true;
    }
}
?>
