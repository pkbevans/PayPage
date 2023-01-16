<div class="d-flex justify-content-center">
    <div class="card">
        <div class="card-body" style="width: 90vw">
            <h5 class="card-title">Gateway Customer Token Details</h5>
            <div class="row">
                <div class="col-3">
                    <h5>Customer Id:</h5>
                </div>
                <div class="col-9">
                    <span><?php echo $customer->id;?></span>
                </div>
            </div>
            <div class="row">
                <div class="col-3">
                    <h5>JSON:</h5>
                </div>
            </div>
            <div class="row">
                <div class="col-9">
                    <PRE><?php echo json_encode($customer, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);?></PRE>
                </div>
            </div>
        </div>
    </div>
</div>
