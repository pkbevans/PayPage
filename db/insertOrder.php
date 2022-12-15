<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/db/db_insert_order.php';

$incoming = json_decode(file_get_contents('php://input'));
$result=insertOrder(
        $incoming->mrn, 
        $incoming->amount, 
        $incoming->currency,
        $incoming->customerId,
        $incoming->email,
        "NEW");
echo json_encode($result);
