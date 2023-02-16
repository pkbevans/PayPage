<div class="d-flex justify-content-center">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Gateway Log</h5>
            <div class="row">
                <div class="col-3">
                    <h5>Reference Number:</h5>
                </div>
                <div class="col-9">
                    <span><?php echo $referenceNumber;?></span>
                </div>
            </div>
            <div class="row">
                <div class="col-3">
                    <h5>Log:</h5>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <PRE><?php echo $prettyLog;?></PRE>
                </div>
            </div>
        </div>
    </div>
</div>
