<?php include "../api/getGatewayTxn.php"; ?>
<?php if(property_exists($txn, "id")):?>
<div class="d-flex justify-content-center">
    <div class="card">
        <div class="card-body" style="width: 90vw">
            <h5 class="card-title">Gateway Request Details</h5>
            <div class="row">
                <div class="col-3">
                    <h5>Request Id:</h5>
                </div>
                <div class="col-9">
                    <span><?php echo $txn->id;?></span>
                </div>
            </div>
            <div class="row">
                <div class="col-3">
                    <h5>Reason Code:</h5>
                </div>
                <div class="col-9">
                    <span><?php echo $txn->applicationInformation->reasonCode;?></span>
                </div>
            </div>
            <div class="row">
                <div class="col-3">
                    <h5>JSON:</h5>
                </div>
            </div>
            <div class="row">
                <div class="col-9">
                    <PRE><?php echo json_encode($txn, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);?></PRE>
                </div>
            </div>
        </div>
    </div>
</div>
<?php else:?>
    UNABLE TO RETRIEVE TXN DETAILS. PLEASE TRY AGAIN LATER
<?php endif?>