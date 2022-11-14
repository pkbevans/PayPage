<?php
include_once($_SERVER['DOCUMENT_ROOT']."/ppSecure/Credentials.php");
$incoming = json_decode(file_get_contents('php://input'));

try {
    $conn = new PDO("mysql:host=$servername;dbname=".$dbName, $username, $password);

    $stmt = "select * from payments where id=" . $incoming->paymentId . ";";

    // echo "STMT=" . $stmt . "<BR>";
    $stmtPayment = $conn->query($stmt);
    $payment = $stmtPayment->fetch(PDO::FETCH_ASSOC);

    // Close connection
    unset($conn);
} catch(PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
}
?>
