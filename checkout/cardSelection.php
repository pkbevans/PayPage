<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/payPage/common/PeRestLib/RestRequest.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/checkout/php/utils/countries.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/checkout/php/utils/cards.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/checkout/php/utils/addresses.php';
$count=0;
$incoming = json_decode(file_get_contents('php://input'));
$paymentInstruments = new stdClass();
try {
    // Get Payment Instruments
    $api = str_replace('{customerId}', $incoming->customerId, API_TMS_V2_CUSTOMER_PAYMENT_INSTRUMENTS);

    $result = ProcessRequest(MID, $api , METHOD_GET, "", CHILD_MID, AUTH_TYPE_SIGNATURE );
    // echo("<BR> BODY<PRE>" .json_encode($result, JSON_PRETTY_PRINT). "</PRE><BR>");
    if($result->responseCode === 200){
        $count = $result->response->count;
        if(isset($result->response->_embedded->paymentInstruments)){
            $paymentInstruments = $result->response->_embedded->paymentInstruments;
        }else{
            // IGNORE
        }
    }else{
        // IGNORE
    }
} catch (Exception $exception) {
    echo(json_encode($exception));
}?>
<div id="cardSelectionSection">
    <div class="row">
        <div class="col-12"><h5>Choose your payment method:</h5></div>
    </div>
<?php if ($count>0): ?>
    <div class="d-grid gap-2">
    <?php foreach ($paymentInstruments as $paymentInstrument): ?>
            <input type="hidden" id="<?php echo "pi_" . $paymentInstrument->id ;?>" value='<?php echo json_encode($paymentInstrument);?>'>
            <button type="button" class="<?php echo cardExpired($paymentInstrument)?'btn btn-warning':'btn btn-primary';?>" onclick="usePaymentInstrument('<?php echo $paymentInstrument->id;?>')">
                <div class="row">
                    <div class="col-3">
                        Pay with:&nbsp;
                    </div>
                    <div class="col-3">
                        <img src="../common/images/<?php echo $cardTypes[$paymentInstrument->card->type]['image']?>" class="img-fluid" alt="<?php echo $cardTypes[$paymentInstrument->card->type]['alt'];?>">
                    </div>
                    <div class="col-6 text-start">
                        <strong><?php echo $paymentInstrument->_embedded->instrumentIdentifier->card->number; ?></strong>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6"></div>
                    <div class="col-6 text-start">
                            <small>Expires:&nbsp;<?php echo $paymentInstrument->card->expirationMonth . "/" . $paymentInstrument->card->expirationYear;?></small>
                        <?php if(cardExpired($paymentInstrument)): ?>
                            <small>****EXPIRED CARD****</small>
                        <?php endif ?>
                    </div>
                </div>
            </button>
    <?php endforeach; ?>
        <button type="button" class="btn btn-secondary" onclick="usePaymentInstrument('NEW')">Pay with a new card</button>
    </div>
    <div class="row mt-2">
        <div class="col-12">
            <div id="googleContainer"></div>
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-12">
            <button type="button" class="btn btn-link" onclick="backButton('cardSelection')">Back</button>
            <button type="button" class="btn btn-link" onclick="cancel()">Cancel</button>
<?php if ($count>0): ?>
            <button type="button" class="btn btn-link" onclick="showManageIframe('CARDS')">Manage my cards</button>
<?php endif ?>
        </div>
    </div>
</div>
<?php endif?>
<div id="paymentDetailsSection" style="display: <?php echo ($count>0?"none":"block");?>">
    <div id="cardInputSection" style="width: 90vw">
        <div class="d-flex justify-content-center mb-3">
            <div id="cardInput">
                <form onsubmit="return false;">
                    <div class="card">
                        <div class="card-body" style="width: 90vw">
                            <div class="row">
                                <div id="cardError" class="alert alert-danger alert-dismissible fade show" role="alert" style="display:none">
                                    <strong>Something went wrong. Please try again.</strong>
                                </div>
                            </div>
                            <div id="cardNumber">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <img src="/payPage/common/images/Visa.svg" width="30">
                                        <img src="/payPage/common/images/Mastercard.svg" width="30">
                                        <img src="/payPage/common/images/Amex.svg" width="30">
                                    </div>
                                </div>
                                <div class="row mt-3 mb-3">
                                    <div class="col-12">
                                        <label class="form-check-label" for="number-container">Card Number</label>
                                        <div class="cardInput">
                                            <i class="fa fa-credit-card"></i>
                                            <div id="number-container" class="form-control flex-microform"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6" id="cardDate">
                                    <label class="form-check-label" for="expiryDate">Expiry Date</label>
                                    <div class="cardInput">
                                        <i class="fa fa-calendar"></i>
                                        <input class="form-control" id="expiryDate" type="text" placeholder="MM/YY" pattern="[0-1][0-9]/[2][1-9]" inputmode="numeric" autocomplete="cc-exp" autocorrect="off" spellcheck="off" aria-invalid="false" aria-placeholder="MM/YY" required>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label id="securityCodeLabel" class="form-check-label" for="securityCode-container">Security Code</label>
                                    <div class="cardInput">
                                        <i class="fa fa-lock"></i>
                                        <div id="securityCode-container" class="form-control flex-microform"></div>
                                    </div>
                                </div>
                            </div>
                            <div  id="sameAddressCheck" style="display: <?php echo ($count>0?"none":"block");?>">
                                <div class="row mt-2">
                                    <div class="col-1">
                                        <input type="checkbox" class="form-check-input" onchange="useSameAddressChanged()" id="useShipAsBill" name="useShipAsBill" value="1" checked="checked">
                                    </div>
                                    <div class="col-11">
                                        <label for="useShipAsBill" class="form-check-label">Use Delivery Address as the card billing address</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div id="billingAddressSection">
        <form id="billingForm" class="needs-validation" novalidate style="display: none">
            <div id="billingSection">
                <div class="row">
                    <div class="12">
                        <h5>Card billing address:</h5>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group form-floating mb-3">
                            <input id="bill_to_firstName" type="text" class="form-control form-control-sm" value="" tabindex="1" placeholder="First name" maxlength="60" required>
                            <label for="bill_to_firstName" class="form-label">First name*</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group form-floating mb-3">
                            <input id="bill_to_lastName" type="text" class="form-control form-control-sm" value="" tabindex="2" placeholder="Last Name" maxlength="60" required>
                            <label for="bill_to_lastName" class="form-label">Last name*</label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group form-floating mb-3">
                            <input id="bill_to_address1" type="text" class="form-control form-control-sm" value="" tabindex="3" placeholder="1st line of address" maxlength="60" required>
                            <label for="bill_to_address1" class="form-label">Address line 1*</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group form-floating mb-3">
                            <input id="bill_to_address2" type="text" class="form-control form-control-sm" value="" tabindex="4" placeholder="2nd line of address" maxlength="60">
                            <label for="bill_to_address2" class="form-label">Address line 2</label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group form-floating mb-3">
                            <input id="bill_to_address2_locality" type="text" class="form-control form-control-sm" value="" tabindex="5" placeholder="City/County" required maxlength="50">
                            <label for="bill_to_address2_locality" class="form-label">City/County*</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group form-floating mb-3">
                            <input id="bill_to_administrativeArea" type="text" class="form-control form-control-sm" value="" tabindex="6" placeholder="State" required maxlength="10">
                            <label for="bill_to_administrativeArea" class="form-label">PostCode*</label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group form-floating mb-3">
                            <input id="bill_to_postalCode" type="text" class="form-control form-control-sm" value="" tabindex="6" placeholder="Postcode" required maxlength="10">
                            <label for="bill_to_postalCode" class="form-label">PostCode*</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group form-floating mb-3">
                            <select id="bill_to_country" class="form-control form-control-sm" tabindex="7" >
<?php
foreach ($countries as $key => $value) {
echo "<option value=\"". $key ."\">" . $value . "</option>\n";
}
?>
                            </select>
                            <label for="bill_to_country" class="form-label">Country*</label>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="col-12">
        <div class="d-grid gap-2">
            <button type="button" id="payButton" onclick="nextButton('pay')" class="btn btn-primary" disabled="true">Pay</button>
        </div>
    </div>
<?php if ($count==0): ?>
    <div class="row mt-2">
        <div class="col-12">
            <div id="googleContainer"></div>
        </div>
    </div>
<?php endif?>
    <div class="row">
        <div class="col-12">
            <button type="button" class="btn btn-link" onclick="backButton('<?php echo ($count>0?"pay":"pay_new");?>')">Back</button>
            <button type="button" class="btn btn-link" onclick="cancel()">Cancel</button>
        </div>
    </div>
</div>
