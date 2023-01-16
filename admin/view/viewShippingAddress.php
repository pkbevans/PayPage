<?php if(property_exists($txn, "id")):?>
    <div class="d-flex justify-content-center">
        <div class="card">
            <div class="card-body" style="width: 90vw">
                <h5 class="card-title">Shipping Address</h5>
                <div class="row">
                    <div class="col-3">
                        <h5>Address:</h5>
                    </div>
                    <div class="col-9">
                        <span><?php echo concatinateNameAddress($txn->orderInformation->shipTo);?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php else:?>
    UNABLE TO RETRIEVE ADDRESS DETAILS. PLEASE TRY AGAIN LATER
<?php endif?>