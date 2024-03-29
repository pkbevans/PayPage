<div class="row">
    <div class="col-6">
        <h3>Order Details</h3>
    </div>
</div>
<div class="d-flex">
    <div class="card">
        <div class="card-body" style="width: 95vw">
<?php if($order): ?>
            <div class="row">
                <div class="col-3">
                    <h5>Order Id:</h5>
                </div>
                <div class="col-2">
                    <span><?php echo $order->id;?></span>
                </div>
            </div>
            <div class="row">
                <div class="col-3">
                    <h5>Reference:</h5>
                </div>
                <div class="col-9">
                    <span><?php echo $order->merchantReference;?></span>
                </div>
            </div>
            <div class="row">
                <div class="col-3">
                    <h5>Amount:</h5>
                </div>
                <div class="col-9">
                    <span><?php echo $order->currency . " " . $order->amount;?></span>
                </div>
            </div>
            <div class="row">
                <div class="col-3">
                    <h5>Email:</h5>
                </div>
                <div class="col-9">
                    <span><?php echo $order->customerEmail;?></span>
                </div>
            </div>
            <div class="row">
                <div class="col-3">
                    <h5>Status:</h5>
                </div>
                <div class="col-9">
                    <span><?php echo $order->status;?></span>
                </div>
            </div>
            <div class="row">
                <div class="col-3">
                    <h5>Customer's User ID:</h5>
                </div>
                <div class="col-9">
                    <span><?php echo $order->customerUserId;?></span>
                </div>
            </div>
            <div class="row">
                <div class="col-3">
                    <h5>Customer Token ID:</h5>
                </div>
                <div class="col-9 px-0" >
                    <button type="button" class="btn btn-link" onclick="showCustomer('<?php echo $order->customerId;?>')"><?php echo $order->customerId;?></button>
                </div>
            </div>
            <div class="row">
                <div class="col-9 px-0">
                    <button type="button" class="btn btn-link" onclick="showLog('<?php echo $order->merchantReference;?>')">View Log</button>
                </div>
            </div>
<?php else: ?>
    UNABLE TO FIND THIS ORDER
<?php endif; ?>
        </div>
    </div>
</div>
<div class="d-flex">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Payments</h5>
            <table class="table">
                <thead><tr><th>Id</th><th>Type</th><th>Amount</th><th>Card Number</th><th>Card Type</th><th>Status</th><th>Captured</th><th>Auth Code</th><th>Request ID</th><th>Shipping</th><th>Timestamp</th><th>Action</th></tr></thead>
                <tbody>
<?php foreach($payments as $payment): ?>
                    <tr>
                        <td><?php echo $payment->id;?></td>
                        <td><?php echo $payment->type;?></td>
                        <td><?php echo $payment->amount;?></td>
                        <td><?php echo $payment->cardNumber;?></td>
                        <td><?php echo $payment->cardType;?></td>
                        <td><?php echo $payment->status;?></td>
                        <td><?php echo ($payment->captured? "&#10004;":"");?></td>
                        <td><?php echo $payment->authCode;?></td>
                        <td><button type="button" class="btn btn-link px-0" onclick="showRequest('<?php echo $payment->gatewayRequestId;?>')"><?php echo $payment->gatewayRequestId;?></button></td>
                        <td><button type="button" class="btn btn-link px-0" onclick="showShipping('<?php echo $payment->gatewayRequestId;?>')">Address</button></td>
                        <td><?php echo $payment->datetime;?></td>
    <?php if($order->status == "AUTHORIZED" && $payment->status == "AUTHORIZED" && $payment->type == "PAYMENT" ): ?>
        <?php if($payment->captured == 1):?>
            <?php if(isDateToday($payment->datetime)):?>
                        <td><button type="button" class="btn btn-outline-primary" onclick="showActionPage('<?php echo $payment->id;?>', 'Void')">Void...</button></td>
            <?php else:?>
                        <td><button type="button" class="btn btn-outline-primary" onclick="showActionPage('<?php echo $payment->id;?>', 'Refund')">Refund...</button></td>
            <?php endif?>
        <?php else:?>
                        <td><button type="button" class="btn btn-outline-primary" onclick="showActionPage('<?php echo $payment->id;?>', 'Capture')">Capture...</button></td>
                        <td><button type="button" class="btn btn-outline-primary" onclick="showActionPage('<?php echo $payment->id;?>', 'Reversal')">Reverse...</button></td>
        <?php endif; ?>
    <?php elseif($order->status == "VOIDED" && $payment->status == "AUTHORIZED" && $payment->type == "PAYMENT" ): ?>
                        <td><button type="button" class="btn btn-outline-primary" onclick="showActionPage('<?php echo $payment->id;?>', 'Reversal')">Reverse...</button></td>
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
