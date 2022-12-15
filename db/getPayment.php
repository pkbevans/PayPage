<?php
require_once('../v1/controller/db.php');
$incoming = json_decode(file_get_contents('php://input'));

try {
    $conn = DB::connectReadDB();

    $stmt = "select * from payments where id=" . $incoming->paymentId . ";";
    // echo "STMT=" . $stmt . "<BR>";
    $stmtPayment = $conn->query($stmt);
    $payment = $stmtPayment->fetch(PDO::FETCH_ASSOC);
    // Get the Order as well
    $stmt = "select * from orders where id=" . $payment['orderId'] . ";" ;
    // echo "STMT=" . $stmt . "<BR>";
    $stmtOrder = $conn->query($stmt);
    $order = $stmtOrder->fetch(PDO::FETCH_ASSOC);
    // Close connection
    unset($conn);
} catch(PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
}
?>
