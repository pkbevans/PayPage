<?php include "../db/getOrder.php"; ?>
<div class="d-flex justify-content-center">
    <div class="card">
        <div class="card-body" style="width: 90vw">
            <h5 class="card-title">Your Order</h5>
<?php if($order): ?>
            <div class="row">
                <div class="col-3">
                    <h5>Order Id:</h5>
                </div>
                <div class="col-9">
                    <span><?php echo $order['id'];?></span>
                </div>
            </div>
            <div class="row">
                <div class="col-3">
                    <h5>Reference:</h5>
                </div>
                <div class="col-9">
                    <span><?php echo $order['merchantReference'];?></span>
                </div>
            </div>
            <div class="row">
                <div class="col-3">
                    <h5>Customer Id:</h5>
                </div>
                <div class="col-9">
                    <span><?php echo $order['customerId'];?></span>
                </div>
            </div>
            <div class="row">
                <div class="col-3">
                    <h5>Email:</h5>
                </div>
                <div class="col-9">
                    <span><?php echo $order['customerEmail'];?></span>
                </div>
            </div>
            <div class="row">
                <div class="col-3">
                    <h5>Amount:</h5>
                </div>
                <div class="col-9">
                    <span><?php echo $order['currency'] . " " . $order['amount'];?></span>
                </div>
            </div>
            <div class="row">
                <div class="col-3">
                    <h5>Status:</h5>
                </div>
                <div class="col-9">
                    <span><?php echo $order['status'];?></span>
                </div>
            </div>
<?php else: ?>
    UNABLE TO FIND THIS ORDER
<?php endif; ?>
        </div>
    </div>
</div>
<div class="d-flex justify-content-center">
    <div class="card">
        <div class="card-body" style="width: 90vw">
            <h5 class="card-title">Payments</h5>
            <table class="table">
                <thead><tr><th>Id</th><th>Type</th><th>Amount</th><th>Card Number</th><th>Card Type</th><th>Status</th><th>Captured</th><th>Auth Code</th><th>Request ID</th><th>Timestamp</th></tr></thead>
                <tbody>
<?php foreach($payments as $payment): ?>
                    <tr>
                        <td><?php echo $payment['id'];?></td>
                        <td><?php echo $payment['type'];?></td>
                        <td><?php echo $payment['amount'];?></td>
                        <td><?php echo $payment['cardNumber'];?></td>
                        <td><?php echo $payment['cardType'];?></td>
                        <td><?php echo $payment['status'];?></td>
                        <td><?php echo ($payment['captured']? "&#10004;":"");?></td>
                        <td><?php echo $payment['authCode'];?></td>
                        <td><button type="button" class="btn btn-link" onclick="showRequest('<?php echo $payment['gatewayRequestId'];?>')"><?php echo $payment['gatewayRequestId'];?></button></td>
                        <td><?php echo $payment['datetime'];?></td>
<?php if($order['status'] == "AUTHORIZED" && $payment['status'] == "AUTHORIZED" ): ?>
    <?php if($payment['captured'] == 1):?>
                        <td><button type="button" class="btn btn-outline-primary" onclick="showRefundPage('<?php echo $payment['id'];?>')">Refund...</button></td>
    <?php else:?>
                        <td><button type="button" class="btn btn-outline-primary" onclick="showReversalPage('<?php echo $payment['id'];?>')">Reverse...</button></td>
    <?php endif; ?>
<?php endif; ?>
                    </tr>
<?php endforeach; ?>
                </tbody>
            </table>
<?php if(!$payments): ?>
    THERE ARE NO PAYMENTS FOR THIS ORDER
<?php endif?>
        </div>
    </div>
</div>
