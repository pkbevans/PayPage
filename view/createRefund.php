<?php include "../db/get_payment.php"; ?>
<div class="d-flex justify-content-center">
    <div class="card">
        <div class="card-body" style="width: 90vw">
            <h5 class="card-title">Refund</h5>
            <div class="row">
                <div class="col-3">
                    Original Amount
                </div>
                <div class="col-9">
                    <span><?php echo $payment['currency'] . " " . $payment['amount'];?></span>
                </div>
            </div>
            <div class="row">
                <div class="col-3">
                    Status
                </div>
                <div class="col-9">
                    <span><?php echo $payment['status'];?></span>
                </div>
            </div>
            <form class="needs-validation" id="refundForm" name="checkout" method="POST" target="" action="" novalidate >
                <div class="row">
                    <div class="col-3">
                        <label for="refundReference" class="form-label">Reference</label>
                    </div>
                    <div class="col-3">
                        <input id="refundReference" class="form-control" type="text" name="refundReference" value="" required />
                    </div>
                </div>
                <div class="row">
                    <div class="col-3">
                        <label for="refundAmount" class="form-label">Refund Amount</label>
                    </div>
                    <div class="col-3">
                        <input id="refundAmount" class="form-control" type="number" name="refundAmount" value="<?php echo $payment['amount'];?>" required step="0.01" min="1.00" max="<?php echo $payment['amount'];?>" />
                    </div>
                </div>
                <button id="refundButton" type="button" class="btn btn-primary" onclick="submitRefund('<?php echo $payment['gatewayRequestId'];?>','<?php echo $payment['currency'];?>',<?php echo $payment['amount'];?>, '<?php echo $payment['cardNumber'];?>')">Refund</button>
            </form>
        </div>
    </div>
</div>
