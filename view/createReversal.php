<?php include "../db/get_payment.php"; ?>
<div class="d-flex justify-content-center">
    <div class="card">
        <div class="card-body" style="width: 90vw">
            <h5 class="card-title">Reversal</h5>
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
            <form class="needs-validation" id="reversalForm" name="checkout" method="POST" target="" action="" novalidate >
                <div class="row">
                    <div class="col-3">
                        <label for="reversalReference" class="form-label">Reference</label>
                    </div>
                    <div class="col-3">
                        <input id="reversalReference" class="form-control" type="text" name="reversalReference" value="" required />
                    </div>
                </div>
                <div class="row">
                    <div class="col-3">
                        <label for="reversalAmount" class="form-label">Reversal Amount</label>
                    </div>
                    <div class="col-3">
                        <input id="reversalAmount" class="form-control" type="number" name="reversalAmount" value="<?php echo $payment['amount'];?>" required step="0.01" min="1.00" max="<?php echo $payment['amount'];?>" />
                    </div>
                </div>
                <button id="reversalButton" type="button" class="btn btn-primary" onclick="submitReversal('<?php echo $payment['gatewayRequestId'];?>','<?php echo $payment['currency'];?>',<?php echo $payment['amount'];?>, '<?php echo $payment['cardNumber'];?>')">Reverse</button>
            </form>
        </div>
    </div>
</div>
