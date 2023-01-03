<?php
include_once 'dbUtils.php';
$incoming = json_decode(file_get_contents('php://input'));
$url = 'http://'. $_SERVER['SERVER_NAME'] . '/payPage/v1/controller/orders.php?orderId=' . $incoming->orderId ;
[$responseCode, $response] = fetch(METHOD_GET, $url, null);
if($responseCode != 200){
    http_response_code($responseCode);
    echo "Error: Fetching order<BR>";
    exit;
}
$order = new stdClass();
$order = $response->orders[0];
$payments = $response->orders[0]->payments;
// var_dump($order);
include '../view/viewOrder.php';
http_response_code($responseCode);

function isDateToday($dateString){
    $today = date('Y-m-d');
    if(strncmp($dateString, $today, 10)){
        return false;
    }else{
        return true;
    }
}
?>
