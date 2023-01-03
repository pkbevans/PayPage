<?php
include_once 'dbUtils.php';
$incoming = json_decode(file_get_contents('php://input'));
$url = 'http://'. $_SERVER['SERVER_NAME'] . '/payPage/v1/controller/orders.php?email='. $incoming->email. '&customerId=' . $incoming->customerId . '&mrn=' . $incoming->mrn .'&id=' . $incoming->orderId .'&status=' . $incoming->status;
[$responseCode, $response] = fetch(METHOD_GET, $url, null);
if($responseCode != 200){
    http_response_code($responseCode);
    echo "Error: ". $response;
    exit;
}
$orders = new stdClass();
$orders = $response->orders;
include '../view/listOrders.php';
http_response_code($responseCode);

