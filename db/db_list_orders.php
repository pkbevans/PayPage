<?php
include_once '../../ppSecure/Credentials.php';
?>
<!DOCTYPE html>
<html lang="en-GB">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
        <title>View Orders</title>
    </head>
    <body>
        <div class="container">
            <h3>Orders</h3>
<?php
try {
    $conn = new PDO("mysql:host=$servername;dbname=paypage", $username, $password);
    $stmtOrders = $conn->query("select id, merchantReference, amount, currency, customerId, customerEmail, status from orders order by id desc;"); 
    $orders = $stmtOrders->fetchAll(PDO::FETCH_ASSOC);
    // iterate over rows
    foreach($orders as $order) {
        echo "<table class=\"table\"><thead><tr>" .
                "<th>Order Id</th>" .
                "<th>Reference</th>" .
                "<th>Amount</th>" .
                "<th>Currency</th>" .
                "<th>Customer</th>" .
                "<th>Email</th>" .
                "<th>Status</th>" .
                "</tr></thead>";
        echo "<tbody><tr>" . 
                "<td>" . $order['id'] . "</td>" . 
                "<td>" . $order['merchantReference'] . "</td>" . 
                "<td>" . $order['amount'] . "</td>" . 
                "<td>" . $order['currency'] . "</td>" . 
                "<td>" . $order['customerId'] . "</td>" . 
                "<td>" . $order['customerEmail'] . "</td>" .
                "<td>" . $order['status'] . "</td>" . 
                "</tr></tbody></table>";
        //
        echo "<div class=\"row\">" .
                "<div class=\"col-1\">" .
                "</div>" .
                "<div class=\"col-11\">";
        $stmtPayments = $conn->query("select id, orderId, cardNumber, cardType, status, dateTime from payments where orderId = ".$order['id']); 
        $payments = $stmtPayments->fetchAll(PDO::FETCH_ASSOC);
        // iterate over rows
        $firstPayment=true;
        foreach($payments as $payment) {
            if($firstPayment){
                $firstPayment=false;
                echo "<h5>Payments</h5><table class=\"table\"><thead><tr>" .
                        "<th>Order Id</th>" .
                        "<th>Id</th>" .
                        "<th>Card Number</th>" .
                        "<th>Card Type</th>" .
                        "<th>Status</th>" .
                        "<th>Timestamp</th>" .
                        "</tr></thead>";
            }
            echo "<tbody><tr>" . 
                    "<td>" . $payment['orderId'] . "</td>" . 
                    "<td>" . $payment['id'] . "</td>" . 
                    "<td>" . $payment['cardNumber'] . "</td>" . 
                    "<td>" . $payment['cardType'] . "</td>" . 
                    "<td>" . $payment['status'] . "</td>" .
                    "<td>" . $payment['dateTime'] . "</td>" .
                    "</tr></tbody>";
        }
        if(!$firstPayment){
            echo "</table><br>";
        }
        echo "</div>" .
             "</div>";
    }
    // Close connection
    unset($conn);
} catch(PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
}
?>
        </div>
    </body>
</html>