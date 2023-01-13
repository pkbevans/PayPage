<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/common/db/dbUtils.php';

$incoming = json_decode(file_get_contents('php://input'));
$url = 'http://'. $_SERVER['SERVER_NAME'] . '/payPage/common/v1/controller/orders.php?email='. $incoming->email. 
        '&customerId=' . $incoming->customerId . '&mrn=' . $incoming->mrn .'&id=' . $incoming->orderId .
        '&status=' . $incoming->status . '&page='.$incoming->page . '&rows=' . $incoming->rows;
[$responseCode, $response] = fetch($incoming->accessToken, METHOD_GET, $url, null);
if($responseCode != 200){
    http_response_code($responseCode);
    echo "Error: ". $response;
    exit;
}
$orders = new stdClass();
$orders = $response->orders;
$currentPage = $incoming->page;
$hasNext = $response->has_next_page;
$hasPrev = $response->has_prev_page;
$totalPages = $response->total_pages;
include '../view/listOrders.php';
http_response_code($responseCode);

