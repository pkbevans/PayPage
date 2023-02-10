<?php
$incoming = json_decode(file_get_contents('php://input'));
$orderDetails = $incoming->orderDetails;
$itemIndex = 0;
foreach ($orderDetails->orderItems as $orderItem):?>
<div class="row">
    <div class="col-3 col-lg-2" >
        <input id="quantity_<?php echo $itemIndex?>" type="number" class="form-control" onchange="onQtyChange(<?php echo $itemIndex?>)" value="<?php echo $orderItem->quantity?>" tabindex="1"  maxlength="2" min="1" required>
    </div>
    <div class="col-4 col-lg-4">
        <?php echo $orderItem->description?>
    </div>
    <div class="col-2 col-lg-3 d-flex justify-content-end">
        <?php echo "£" . number_format($orderItem->unitPrice,2);?>
    </div>
    <div class="col-3 col-lg-3 d-flex justify-content-end">
        <?php echo "£" . number_format($orderItem->quantity*$orderItem->unitPrice,2);?>
    </div>
</div>
<?php 
++$itemIndex;
endforeach?>
<BR>
<div class="row">
    <div class="col-3 col-lg-7"></div>
    <div class="col-6 col-lg-2">
        VAT
    </div>
    <div class="col-3 col-lg-3 d-flex justify-content-end">
        <input id="amount" type="hidden" name="amount" value="<?php echo $orderDetails->totalAmount?>"/>
        £<?php echo number_format($orderDetails->vat,2)?>
    </div>
</div>
<div class="row">
    <div class="col-3 col-lg-7"></div>
    <div class="col-6 col-lg-2">
        Delivery
    </div>
    <div class="col-3 col-lg-3 d-flex justify-content-end">
        <input id="amount" type="hidden" name="amount" value="<?php echo $orderDetails->totalAmount?>"/>
        £<?php echo number_format($orderDetails->delivery,2)?>
    </div>
</div>
<div class="row">
    <div class="col-3 col-lg-7"></div>
    <div class="col-6 col-lg-2">
        Total
    </div>
    <div class="col-3 col-lg-3 d-flex justify-content-end">
        <input id="amount" type="hidden" name="amount" value="<?php echo $orderDetails->totalAmount?>"/>
        £<?php echo number_format($orderDetails->totalAmount,2)?>
    </div>
</div>
