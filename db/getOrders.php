<?php
include_once($_SERVER['DOCUMENT_ROOT']."/ppSecure/Credentials.php");
$incoming = json_decode(file_get_contents('php://input'));

try {
    $conn = new PDO("mysql:host=$servername;dbname=".$dbName, $username, $password);
    $where = "";
    if($incoming->email){
        $where = " where customerEmail=\"" . $incoming->email . "\" ";
        if($incoming->mrn){
            $where .= " and merchantReference=\"" . $incoming->mrn . "\" ";
        }
        if($incoming->customerId){
            $where .= " and customerId=\"" . $incoming->customerId . "\" ";
        }
        if($incoming->orderId){
            $where .= " and id=\"" . $incoming->orderId . "\" ";
        }
    }else if($incoming->mrn){
        $where .= " where merchantReference=\"" . $incoming->mrn . "\" ";
        if($incoming->customerId){
            $where .= " and customerId=\"" . $incoming->customerId . "\" ";
        }
        if($incoming->orderId){
            $where .= " and id=\"" . $incoming->orderId . "\" ";
        }
    }else if($incoming->customerId){
        $where .= " where customerId=\"" . $incoming->customerId . "\" ";
        if($incoming->orderId){
            $where .= " and id=\"" . $incoming->orderId . "\" ";
        }
    }else if($incoming->orderId){
        $where .= " where id=\"" . $incoming->orderId . "\" ";
    }


    $stmt = "select id, merchantReference, amount, currency, " .
        "customerId, customerEmail, status, dateTime" .
        " from orders " . $where . " order by id desc;";

    // echo "STMT=" . $stmt . "<BR>";
    $stmtOrders = $conn->query($stmt);
    $orders = $stmtOrders->fetchAll(PDO::FETCH_ASSOC);
    // Close connection
    unset($conn);
} catch(PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
}
