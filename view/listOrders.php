<?php include "../db/getOrders.php"; ?>
<div class="container">
    <div class="row">
        <div class="col-6">
            <h3>Orders</h3>
        </div>
        <div class="col-6 d-flex justify-content-end">
            <button type="button" class="btn btn-primary" onclick="refresh()">Refresh</button>
        </div>
    </div>
    <table class="table table-hover">
        <thead><tr>
            <th>Timestamp</th>
            <th>Order Id</th>
            <th>Reference</th>
            <th>Amount</th>
            <th>Currency</th>
            <th>Customer</th>
            <th>Email</th>
            <th>Status</th>
        </tr></thead>
        <tbody>
<?php foreach($orders as $order): ?>
                <tr>
                    <td><?php echo $order['dateTime'];?></td>
                    <td><?php echo $order['id'];?></td>
                    <td><button type="button" class="btn btn-link" onclick="getOrder(<?php echo $order['id'];?>)"><?php echo $order['merchantReference'];?></button></td>
                    <td><?php echo $order['amount'];?></td>
                    <td><?php echo $order['currency'];?></td>
                    <td><?php echo $order['customerId'];?></td>
                    <td><?php echo $order['customerEmail'];?></td>
                    <td><?php echo $order['status'];?></td>
                </tr>
<?php endforeach; ?>
        </tbody>
    </table>
<?php if(!$orders): ?>
    THERE ARE NO ORDERS THAT MATCH YOUR SEARCH CRITERIA
<?php endif?>
</div>
