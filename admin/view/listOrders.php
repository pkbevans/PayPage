<div class="container">
    <div class="row">
        <div class="col-6">
            <h3>Orders</h3>
        </div>
        <div class="col-6 d-flex justify-content-end">
            <button type="button" class="btn btn-primary" onclick="refresh()">Refresh</button>
        </div>
    </div>
    <div class="row">
    <table class="table table-hover table-fixed">
        <thead><tr>
            <th>Timestamp</th>
            <th>Order Id</th>
            <th>Reference</th>
            <th>Currency</th>
            <th>Amount</th>
            <th>Customer Email</th>
            <th>User ID</th>
            <th>Customer Token</th>
            <th>Status</th>
        </tr></thead>
        <tbody>
<?php foreach($orders as $order): ?>
                <tr>
                    <td><?php echo $order->datetime;?></td>
                    <td><?php echo $order->id;?></td>
                    <td><button type="button" class="btn btn-link" onclick="getOrder(<?php echo $order->id;?>)"><?php echo $order->merchantReference;?></button></td>
                    <td><?php echo $order->currency;?></td>
                    <td><?php echo $order->amount;?></td>
                    <td><?php echo $order->customerEmail;?></td>
                    <td><?php echo $order->customerUserId;?></td>
                    <td><?php echo $order->customerId;?></td>
                    <td><?php echo $order->status;?></td>
                </tr>
<?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <nav aria-label="Page navigation">
    <ul class="pagination">
        <li class="page-item <?php echo ($hasPrev ? '' : ' disabled');?>"><a class="page-link" <?php echo ($hasPrev ? 'onclick="getOrders('.(1).')"' : 'tabindex="-1" aria-disabled="true"');?>><<</a></li>
        <li class="page-item <?php echo ($hasPrev ? '' : ' disabled');?>"><a class="page-link" <?php echo ($hasPrev ? 'onclick="getOrders('.($currentPage-1).')"' : 'tabindex="-1" aria-disabled="true"');?>><</a></li>
<?php
$start = ($currentPage-10)<1?1:$currentPage-10;
$x = $start;
while($x < ($start+15) && $x <= $totalPages){
    echo '<li class="page-item'. ($x==$currentPage?' active':'').'"><a class="page-link" onclick="getOrders('.$x.')">'.$x.'</a></li>';
    ++$x;
}
?>
        <li class="page-item <?php echo ($hasNext ? '' : ' disabled');?>"><a class="page-link" <?php echo ($hasNext ? 'onclick="getOrders('.($currentPage+1).')"' : 'tabindex="-1" aria-disabled="true"');?>>></a></li>
        <li class="page-item <?php echo ($hasNext ? '' : ' disabled');?>"><a class="page-link" <?php echo ($hasNext ? 'onclick="getOrders('.($totalPages).')"' : 'tabindex="-1" aria-disabled="true"');?>>>></a></li>
    </ul>
    </nav>
<?php if (!$orders): ?>
    THERE ARE NO ORDERS THAT MATCH YOUR SEARCH CRITERIA
<?php endif?>
</div>
