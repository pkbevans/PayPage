<?php
include_once($_SERVER['DOCUMENT_ROOT']."/ppSecure/Credentials.php");
$incoming = json_decode(file_get_contents('php://input'));

try {
    $conn = new PDO("mysql:host=$servername;dbname=".$dbName, $username, $password);
    $where = " where id=\"" . $incoming->orderId . "\" ";

    $stmt = "select id, merchantReference, amount, currency, " .
        "customerId, customerEmail, status, dateTime" .
        " from orders " . $where . ";";

    // echo "STMT=" . $stmt . "<BR>";
    $stmtOrder = $conn->query($stmt);
    $order = $stmtOrder->fetch(PDO::FETCH_ASSOC);

    $stmt = "select * from payments where orderId=" . $incoming->orderId . ";";

    // echo "STMT=" . $stmt . "<BR>";
    $stmtPayments = $conn->query($stmt);
    $payments = $stmtPayments->fetchAll(PDO::FETCH_ASSOC);

    // Close connection
    unset($conn);
} catch(PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
}
?>
