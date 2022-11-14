<?php include "../db/get_orders.php"; ?>
<div class="container">
    <h3>Orders</h3>
    <table class="table">
        <thead><tr>
            <th>Order Id</th>
            <th>Reference</th>
            <th>Amount</th>
            <th>Currency</th>
            <th>Customer</th>
            <th>Email</th>
            <th>Status</th>
            <th>Timestamp</th>
        </tr></thead>
        <tbody>
<?php foreach($orders as $order): ?>
                <tr>
                    <td><?php echo $order['id'];?></td>
                    <td><button type="button" class="btn btn-link" onclick="getOrder(<?php echo $order['id'];?>)"><?php echo $order['merchantReference'];?></button></td>
                    <td><?php echo $order['amount'];?></td>
                    <td><?php echo $order['currency'];?></td>
                    <td><?php echo $order['customerId'];?></td>
                    <td><?php echo $order['customerEmail'];?></td>
                    <td><?php echo $order['status'];?></td>
                    <td><?php echo $order['dateTime'];?></td>
                </tr>
<?php endforeach; ?>
        </tbody>
    </table>
<?php if(!$orders): ?>
    THERE ARE NO ORDERS THAT MATCH YOUR SEARCH CRITERIA
<?php endif?>
</div>
