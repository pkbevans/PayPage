<div class="container">
    <div class="d-flex">
        <div class="card">
            <div class="card-body" style="width: 95vw">
                <h5 class="card-title">Capture</h5>
                <div class="row">
                    <div class="col-3">
                        Original Amount
                    </div>
                    <div class="col-9">
                        <span><?php echo $payment->currency . " " . $payment->amount;?></span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-3">
                        Status
                    </div>
                    <div class="col-9">
                        <span><?php echo $payment->status;?></span>
                    </div>
                </div>
                <form class="needs-validation" id="actionForm" name="checkout" method="POST" target="" action="" novalidate >
                    <input id="amount" type="hidden" name="amount" value="<?php echo $payment->amount;?>" />
                    <div class="row">
                        <div class="col-3">
                            <label for="reference" class="form-label">Reference</label>
                        </div>
                        <div class="col-3">
                            <input id="reference" class="form-control" type="text" name="reference" value="<?php echo $order->merchantReference;?>" required />
                        </div>
                    </div>
                    <button id="captureButton" type="button" class="btn btn-primary"
                        onclick="submitAction('Capture', '<?php echo $payment->orderId;?>', '<?php echo $payment->gatewayRequestId;?>', '<?php echo $payment->currency;?>', <?php echo $payment->amount;?>, '<?php echo $payment->cardNumber;?>')">Capture</button>
                </form>
            </div>
        </div>
    </div>
</div>