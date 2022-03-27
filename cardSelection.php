<?php
require_once 'PeRestLib/RestRequest.php';
include_once 'countries.php';
include_once 'card_types.php';
////////////////////////////////////FUNCTIONS
function concatinateNameAddress($nameAddress){
    // return name and address string
    if(!isset($nameAddress->address2)){
        $nameAddress->address2 = "";
    }
    return xtrim($nameAddress->firstName, " ") .
            xtrim($nameAddress->lastName, "<BR>") .
            xtrim($nameAddress->address1, ", ") .
            xtrim($nameAddress->address2, ", ") .
            xtrim($nameAddress->locality, ", ") .
            xtrim($nameAddress->postalCode, ", ") .
            xtrim($nameAddress->country, ".");
}

function xtrim($in, $suffix){
    $out = trim($in);
    return (empty($out)? "" : $out . $suffix );
}
///////////////////////////////////END FUNCTIONS
///////////////////////////////////VARIABLES
$count=0;
$incoming = json_decode(file_get_contents('php://input'));
$paymentInstruments = new stdClass();
try {
    // Get Payment Instruments
    $api = str_replace('{customerId}', $incoming->customerId, API_TMS_V2_CUSTOMER_PAYMENT_INSTRUMENTS);

    $result = ProcessRequest(PORTFOLIO, $api , METHOD_GET, "", MID, AUTH_TYPE_SIGNATURE );
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
<?php if ($count>0): ?>
<?php foreach ($paymentInstruments as $paymentInstrument): ?>
<div class="row">
    <input type="hidden" id="<?php echo "pi_" . $paymentInstrument->id ;?>" value='<?php echo json_encode($paymentInstrument);?>'>
    <button type="button" class="btn btn-primary" onclick="usePaymentInstrument('<?php echo $paymentInstrument->id;?>')"> 
        <div class="col-10">
            <ul class="list-unstyled">
                <li>Pay with: <img src="images/<?php echo $cardTypes[$paymentInstrument->card->type]['image']?>" class="img-fluid" alt="<?php echo $cardTypes[$paymentInstrument->card->type]['alt'];?>"><strong><?php echo $paymentInstrument->_embedded->instrumentIdentifier->card->number; ?></strong></li>
                <li><small>Expires:&nbsp;<?php echo $paymentInstrument->card->expirationMonth . "/" . $paymentInstrument->card->expirationYear;?></small></li>
            </ul>
        </div>
    </button>
</div>
<?php endforeach; ?>
<div class="row">
    <button type="button" class="btn btn-primary" onclick="usePaymentInstrument('NEW')">Pay with a new card</button>
</div>
<?php endif?>
</div>
<div id="paymentDetailsSection" style="display: <?php echo ($count>0?"none":"block");?>">
    <div class="col-9">
        <div id="cardInputSection">
            <div class="d-flex mb-3">
                <div class="card">
                    <div class="card-body"> 
                        <div class="row"> 
                        <div id="cardError" class="alert alert-danger alert-dismissible fade show" role="alert" style="display:none">
                            <strong>Something went wrong. Please try again.</strong></div>
                        </div>
                       <div id="cardNumber">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <img src="images/Visa.svg" width="30">
                                    <img src="images/Mastercard.svg" width="30">
                                    <img src="images/Amex.svg" width="30">
                                </div>
                            </div>
                            <div class="row mt-3 mb-3">
                                <div class="col-12">
                                    <label class="form-check-label" for="number-container">Card Number</label>
                                    <div class="cardInput">
                                        <i class="fa fa-credit-card"></i>
                                        <div id="number-container" class="form-control"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6" id="cardDate">
                                <label class="form-check-label" for="expiryDate">Expiry Date</label>
                                <div class="cardInput">
                                    <i class="fa fa-calendar"></i>
                                    <input class="form-control" id="expiryDate" type="text" placeholder="MM/YY" pattern="[0-1][0-9]\/[2][1-9]" inputmode="numeric" autocomplete="cc-exp" autocorrect="off" spellcheck="off" aria-invalid="false" aria-placeholder="MM/YY" required>
                                </div>
                            </div>
                            <div class="col-6"> 
                                <label id="securityCodeLabel" class="form-check-label" for="securityCode-container">Security Code</label>
                                <div class="cardInput">
                                    <i class="fa fa-lock"></i>
                                    <div id="securityCode-container" class="form-control"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="billingAddressSection">
        <div  id="storeCardCheck" style="display:none">
            <div class="row">
                <div class="col-12">
                    <input type="checkbox" class="form-check-input" onchange="useSameAddressChanged()" id="useShipAsBill" name="useShipAsBill" value="1" checked="checked">
                    <label for="useShipAsBill" class="form-check-label">Use Delivery Address as Billing Address</label>
                </div>
            </div>                            
            <div class="row">
                <div class="col-12">
                    <input type="checkbox" class="form-check-input" id="storeCard" name="storeCard" value="1">
                    <label for="storeCard" class="form-check-label">Store my details for future use</label>
                </div>
            </div>
        </div>
        <form id="billingForm" class="needs-validation" novalidate style="display: none">
            <div id="billingSection">
                <div class="row">
                    <div class="col-3">
                        <h5>Card Billing:</h5>
                    </div>
                </div>
                <div class="row">
                    <div class="col-4">
                        <div class="form-group form-floating mb-3">
                            <input id="bill_to_forename" type="text" class="form-control form-control-sm" value="" tabindex="1" placeholder="First name" maxlength="60" required>
                            <label for="bill_to_forename" class="form-label">First name*</label>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group form-floating mb-3">
                            <input id="bill_to_surname" type="text" class="form-control form-control-sm" value="" tabindex="2" placeholder="Last Name" maxlength="60" required>
                            <label for="bill_to_surname" class="form-label">Last name*</label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-4">
                        <div class="form-group form-floating mb-3">
                            <input id="bill_to_address_line1" type="text" class="form-control form-control-sm" value="" tabindex="3" placeholder="1st line of address" maxlength="60" required>
                            <label for="bill_to_address_line1" class="form-label">Address line 1*</label>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group form-floating mb-3">
                            <input id="bill_to_address_line2" type="text" class="form-control form-control-sm" value="" tabindex="4" placeholder="2nd line of address" maxlength="60">
                            <label for="bill_to_address_line2" class="form-label">Address line 2</label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-4">
                        <div class="form-group form-floating mb-3">
                            <input id="bill_to_address_city" type="text" class="form-control form-control-sm" value="" tabindex="6" placeholder="City/County" required maxlength="50">
                            <label for="bill_to_address_city" class="form-label">City/County*</label>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group form-floating mb-3">
                            <input id="bill_to_postcode" type="text" class="form-control form-control-sm" value="" tabindex="5" placeholder="Postcode" required maxlength="10">
                            <label for="bill_to_postcode" class="form-label">PostCode*</label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-9">
                        <div class="form-group form-floating mb-3">
                            <select id="bill_to_address_country" class="form-control form-control-sm" tabindex="7" >
<?php
foreach ($countries as $key => $value) {
echo "<option value=\"". $key ."\">" . $value . "</option>\n";
}
?>
                            </select>
                            <label for="bill_to_address_country" class="form-label">Country*</label>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div id="payButtonSection" class="row">
        <div class="col-12">
            <button type="button" id="payButton" onclick="nextButton('pay')" class="btn btn-primary" disabled="true">Pay</button>
            <button type="button" class="btn btn-secondary" onclick="backButton('pay')">Back</button>
        </div>
    </div>
</div>