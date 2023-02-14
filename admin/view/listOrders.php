<?php
$buttonWidth="48px";
?>
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
        <th class="col-1">Date/Time</th>
        <th class="col-1">Order Id</th>
        <th class="col-1">Reference</th>
        <th class="col-1">Currency</th>
        <th class="col-1">Amount</th>
        <th class="col-1">Customer Email</th>
        <th class="col-1">User ID</th>
        <th class="col-1">Status</th>
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
            <td><?php echo $order->status;?></td>
        </tr>
<?php endforeach; ?>
    </tbody>
</table>
</div>
<nav aria-label="Page navigation">
    <ul class="pagination">
        <li class="page-item <?php echo ($hasPrev ? '' : ' disabled');?>" style="width: <?php echo $buttonWidth?>"><a class="page-link" <?php echo ($hasPrev ? 'onclick="getOrders('.(1).')"' : 'tabindex="-1" aria-disabled="true"');?>><<</a></li>
        <li class="page-item <?php echo ($hasPrev ? '' : ' disabled');?>" style="width: <?php echo $buttonWidth?>"><a class="page-link" <?php echo ($hasPrev ? 'onclick="getOrders('.($currentPage-1).')"' : 'tabindex="-1" aria-disabled="true"');?>><</a></li>
<?php
    $start = ($currentPage-10)<1?1:$currentPage-10;
    $x = $start;
    while($x < ($start+15) && $x <= $totalPages){
        echo '<li class="page-item'. ($x==$currentPage?' active':'').'" style="width: <?php echo $buttonWidth?>"><a class="page-link" onclick="getOrders('.$x.')">'.$x.'</a></li>';
        ++$x;
    }
?>
        <li class="page-item <?php echo ($hasNext ? '' : ' disabled');?>" style="width: <?php echo $buttonWidth?>"><a class="page-link" <?php echo ($hasNext ? 'onclick="getOrders('.($currentPage+1).')"' : 'tabindex="-1" aria-disabled="true"');?>>></a></li>
        <li class="page-item <?php echo ($hasNext ? '' : ' disabled');?>" style="width: <?php echo $buttonWidth?>"><a class="page-link" <?php echo ($hasNext ? 'onclick="getOrders('.($totalPages).')"' : 'tabindex="-1" aria-disabled="true"');?>>>></a></li>
    </ul>
</nav>
<?php if (!$orders): ?>
THERE ARE NO ORDERS THAT MATCH YOUR SEARCH CRITERIA
<?php endif?>
