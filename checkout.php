<?php
include_once 'card_types.php';
include_once 'countries.php';
include 'generate_capture_context.php';
include 'rest_get_customer.php';
$shippingAddressRequired = true;
$defaultEmail="";
if(isset($_REQUEST['email']) && !empty($_REQUEST['email'])) {
    $defaultEmail = $_REQUEST['email'];
}else if($paymentInstrumentCount>0){
    $defaultEmail = $defaultPaymentInstrument->billTo->email;
}
?>
<!doctype html>
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    ​
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css/styles.css"/>
    <title>Payment Page</title>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
    <form id="iframe_form" method="POST" target="shippingAddress_iframe" action="">
        <input id="customerToken" type="hidden" name="customerToken" value="">
    </form>
    <!--Cardinal device data collection code START-->
    <iframe id="cardinal_collection_iframe" name="collectionIframe" height="1" width="1" style="display: none;"></iframe>
    <form id="cardinal_collection_form" method="POST" target="collectionIframe" action="">
        <input id="cardinal_collection_form_input" type="hidden" name="JWT" value=""/>
    </form>
    </<!--Cardinal device data collection code END-->
    <div id="overlay">
        <iframe id="step_up_iframe" style="border: none; margin-left: auto; margin-right: auto; display: none" height="50%" width="100%" name="stepUpIframe" ></iframe>
    </div>
    <form id="step_up_form" name="stepup" method="POST" target="stepUpIframe" action="">
        <input id="step_up_form_jwt_input" type="hidden" name="JWT" value=""/>
        <input id="MD" type="hidden" name="MD" value="HELLO MUM. GET THE KETTLE ON"/>
    </form>
    <div class="container">

        <div id="iframeSection" style="display: none">
            <iframe id="shippingAddressIframe" name="shippingAddress_iframe" src="about:blank" class="responsive-iframe" style="overflow: hidden; display: block; border:none; height:100vh; width:100%" ></iframe>
        </div>
        <div id="paymentDetailsSection">
            <form id="emailForm" class="needs-validation" novalidate>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group form-floating mb-3">
                        <input id="bill_to_email" type="email" class="form-control form-control-sm" <?php if(!empty($defaultEmail)) echo "readonly";?> value="<?php echo $defaultEmail;?>" placeholder="Enter email" required>
                        <label for="bill_to_email" class="form-label">Email*</label>
                    </div>
                </div>
            </div>
            </form>
<?php if($shippingAddressRequired): ?>
            <form id="shippingForm" class="needs-validation" novalidate>
                <div id="shippingDetailsSection" class="form-group">
                    <h5>Delivery Address</h5>
<?php if($shippingAddressAvailable): ?>
                    <div class="row">
                        <div class="col-sm-6">
                            <span id="ship_to_text" class="form-control form-control-sm" disabled><?php echo $shipToText;?></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <button type="button" class="btn btn-link" onclick="editShippingAddress()">Use a different address</button>
                        </div>
                    </div>
<?php else: ?>     <!--$shippingAddressAvailable -->
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="form-group form-floating mb-3">
                                <input id="ship_to_forename" type="text" class="form-control form-control-sm" value="<?php if($shippingAddressAvailable){echo $defaultShippingAddress->shipTo->firstName;}?>" placeholder="First name" required>
                                <label for="ship_to_forename" class="form-label">First name*</label>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group form-floating mb-3">
                                <input id="ship_to_surname" type="text" class="form-control form-control-sm" value="<?php if($shippingAddressAvailable){echo $defaultShippingAddress->shipTo->lastName;}?>" placeholder="Last Name" required>
                                <label for="ship_to_surname" class="form-label">Surname*</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="form-group form-floating mb-3">
                                <input id="ship_to_address_line1" type="text" class="form-control form-control-sm" value="<?php if($shippingAddressAvailable){echo $defaultShippingAddress->shipTo->address1;}?>" placeholder="1st line of address" required>
                                <label for="ship_to_address_line1" class="form-label">Address line 1*</label>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group form-floating mb-3">
                                <input id="ship_to_address_line2" type="text" class="form-control form-control-sm" value="<?php if($shippingAddressAvailable){echo (isset($defaultShippingAddress->shipTo->address2)?$defaultShippingAddress->shipTo->address2:"");}?>" placeholder="2nd line of address">
                                    <label for="ship_to_address_line2" class="form-label">Address line 2</label>
                            </div>
                        </div>
                    </div>
                        <div class="row">
                            <div class="col-sm-3">
                            <div class="form-group form-floating mb-3">
                                <input id="ship_to_address_city" type="text" class="form-control form-control-sm" value="<?php if($shippingAddressAvailable){echo $defaultShippingAddress->shipTo->locality;}?>" placeholder="City/County" required>
                                <label for="ship_to_address_city" class="form-label">City/County*</label>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group form-floating mb-3">
                                <input id="ship_to_postcode" type="text" class="form-control form-control-sm" value="<?php if($shippingAddressAvailable){echo $defaultShippingAddress->shipTo->postalCode;}?>" placeholder="Postcode" required>
                                <label for="ship_to_postcode" class="form-label">PostCode*</label>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <div class="form-floating">
                                <select id="ship_to_address_country" class="form-control form-control-sm">
<?php
foreach ($countries as $key => $value) {
    echo "<option value=\"". $key ."\"" . ($shippingAddressAvailable && $defaultShippingAddress->shipTo->country == $key? "selected": "") . ">" . $value . "</option>\n";
}
?>
                                </select>
                                <label for="ship_to_address_country" class="form-label">Country*</label>
                            </div>
                        </div>
                    </div>
<?php endif ?> <!--$shippingAddressAvailable -->
                </div>
            </form>
<?php endif ?> <!--$shippingAddressRequired -->
<?php if ($paymentInstrumentCount): ?>
            <div id="storedCardSection">
                <h5>Stored Card Details</h5>
                <div class="row">
                    <div class="col-sm-1">
                        <img id="storedCardImg" src="images/<?php echo $cardTypes[$defaultPaymentInstrument->card->type]['image'];?>" class="img-fluid" alt="<?php echo $cardTypes[$defaultPaymentInstrument->card->type]['alt'];?>">
                    </div>
                    <div class="col-sm-1">
                        <ul class="list-unstyled">
                            <li><strong><?php echo $defaultPaymentInstrument->_embedded->instrumentIdentifier->card->number;?></strong></li>
                            <li><small>Expires:&nbsp;<?php echo $defaultPaymentInstrument->card->expirationMonth . "/" . $defaultPaymentInstrument->card->expirationYear;?></small></li>
                        </ul>
                    </div>
                </div>
                <div class="row">
                    <h5>Card Billing Address</h5>
                    <div class="col-sm-6">
                        <span id="bill_to_text" class="form-control form-control-sm" disabled><?php echo $billToText;?></span>
                    </div>
                </div>
            </div>
<?php endif ?>
            <div id="cardDetailsSection">
                <div class="row mb-1">
                    <div class="col-sm-3">
                        <label class="form-check-label" for="number-container">Card Number</label>
                        <div id="number-container" class="form-control form-control-sm"></div>
                    </div>
                    <div class="col-sm-3">
                        <!-- <div class="exp-wrapper"> -->
                        <label class="form-check-label ms-3" for="expiry">Expires</label>
                        <div class="expiry input-group ms-3">
                            <input autocomplete="off" class="form-control exp" id="card_expirationMonth" name="card_expirationMonth" maxlength="2" pattern="[0-9]*" inputmode="numerical" placeholder="MM" type="text" data-pattern-validate />
                            <input autocomplete="off" class="form-control exp" id="card_expirationYear" name="card_expirationYear" maxlength="2" pattern="[0-9]*" inputmode="numerical" placeholder="YY" type="text" data-pattern-validate />
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-1">
                <div class="col-sm-2">
                    <label id="securityCodeLabel" class="form-check-label" for="securityCode-container">Security Code</label>
                    <div id="securityCode-container" class="form-control form-control-sm"></div>
                </div>
            </div>
            <br>
            <div id="storeCardSection" class="row">
                <div class="col-sm-5">
                    <input type="checkbox" class="form-check-input" id="storeCard" name="storeCard" value="1">
                    <label for="storeCard" class="form-check-label">Store my details for future use</label>
                </div>
            </div>
<?php if($shippingAddressRequired && !$paymentInstrumentCount): ?>
            <div id="useShippingAsBilling" class="row">
                <div class="col-sm-5">
                    <input type="checkbox" class="form-check-input" onchange="useSameAddressChanged()" id="useShipAsBill" name="useShipAsBill" value="1" checked="checked">
                    <label for="useShipAsBill" class="form-check-label">Use Delivery Address as Billing Address</label>
                </div>
            </div>
<?php endif ?>
            <!--<div class="form-group">-->
<?php if($paymentInstrumentCount): ?>
            <div id="defaultBillingSection">
                <div class="row">
                    <div class="col-sm-6">
<?php if($paymentInstrumentCount>1): ?>
                        <button type="button" class="btn btn-link" onclick="editCard()">Use a different Stored Card</button>
<?php endif ?>
                        <button type="button" class="btn btn-link" onclick="newCard()">Use New Card</button>
                    </div>
                </div>
            </div>
<?php endif ?>
            <form id="billingForm" class="needs-validation" novalidate style="display: none">
                <div id="billingSection">
                    <h5>Card Billing Address</h5>
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="form-group form-floating mb-3">
                                <input id="bill_to_forename" type="text" class="form-control form-control-sm" value="<?php if($paymentInstrumentCount){echo $defaultPaymentInstrument->billTo->firstName;}?>" placeholder="First name" required>
                                <label for="bill_to_forename" class="form-label">First name*</label>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group form-floating mb-3">
                                <input id="bill_to_surname" type="text" class="form-control form-control-sm" value="<?php if($paymentInstrumentCount){echo $defaultPaymentInstrument->billTo->lastName;}?>" placeholder="Last Name" required>
                                <label for="bill_to_surname" class="form-label">Surname*</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="form-group form-floating mb-3">
                                <input id="bill_to_address_line1" type="text" class="form-control form-control-sm" value="<?php if($paymentInstrumentCount){echo $defaultPaymentInstrument->billTo->address1;}?>" placeholder="1st line of address" required>
                                <label for="bill_to_address_line1" class="form-label">Address line 1*</label>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group form-floating mb-3">
                                <input id="bill_to_address_line2" type="text" class="form-control form-control-sm" value="<?php if($paymentInstrumentCount){echo $defaultPaymentInstrument->billTo->address2;}?>" placeholder="2nd line of address">
                                <label for="bill_to_address_line2" class="form-label">Address line 2</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="form-group form-floating mb-3">
                                <input id="bill_to_address_city" type="text" class="form-control form-control-sm" value="<?php if($paymentInstrumentCount){echo $defaultPaymentInstrument->billTo->locality;}?>" placeholder="City/County" required>
                                <label for="bill_to_address_city" class="form-label">City/County*</label>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group form-floating mb-3">
                                <input id="bill_to_postcode" type="text" class="form-control form-control-sm" value="<?php if($paymentInstrumentCount){echo $defaultPaymentInstrument->billTo->postalCode;}?>" placeholder="Postcode" required>
                                <label for="bill_to_postcode" class="form-label">PostCode*</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group form-floating mb-3">
                                <select id="bill_to_address_country" class="form-control form-control-sm">
<?php
foreach ($countries as $key => $value) {
    echo "<option value=\"". $key ."\"" . ($paymentInstrumentCount && $defaultPaymentInstrument->billTo->country == $key? "selected": "") . ">" . $value . "</option>\n";
}
?>
                                </select>
                                <label for="bill_to_address_country" class="form-label">Country*</label>
                            </div>
                        </div>
                    </div>
<?php if($paymentInstrumentCount): ?>
                    <div class="row">
                        <div class="col-sm-6">
                            <button type="button" class="btn btn-link" onclick="cancelNewCard()">Use a Stored Card</button>
                        </div>
                    </div>
<?php endif ?>
                </div>

            </form>
            <!--</div>-->
            <BR><button type="button" id="payButton" class="btn btn-primary" disabled="true">Pay</button><BR><BR>
            <button type="button" class="btn btn-secondary" onclick="cancel()">Cancel</button>
        </div>
        <div class="card card-body" id="resultSection" style="display: none">
            <h5 class="card-title">Result</h5>
            <div id="progressSpinner"  class="spinner-border text-info"></div>
            <p id="result" class="card-text"></p>
            <button type="button" id="newPaymentButton" class="btn btn-primary" onclick="window.location.href='index.php'" style="display: none">New Payment</button>
            <button type="button" id="retryButton" class="btn btn-primary" onclick="window.location.reload(true)" style="display: none">Try again</button>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
</body>
<script src="https://flex.cybersource.com/cybersource/assets/microform/0.11/flex-microform.min.js"></script>
<script src="js/expiryDate.js"></script>
<script>
// the capture context that was requested server-side for this transaction
var captureContext = "<?php echo $captureContext ?>";
<?php
if (isset($_REQUEST['standAlone'])){echo "var standAlone = true;\n";}else{echo "var standAlone = false;\n";}
if (isset($_REQUEST['local']) && $_REQUEST['local'] === "true") {echo "var local = true;\n";}else{echo "var local = false;\n";}
echo "var customerId " . ($paymentInstrumentCount?"='".$customerToken."'":"") . ";\n";
echo "var shippingAddressRequired = " . ($shippingAddressRequired?"true":"false") . ";\n"
?>
var paymentInstrumentId = "<?php echo ($paymentInstrumentCount?$defaultPaymentInstrument->id:"");?>";
var oldPaymentInstrumentId;
var maskedPan = "<?php echo ($paymentInstrumentCount?$defaultPaymentInstrument->_embedded->instrumentIdentifier->card->number:"");?>";
var shippingAddressId = "<?php echo ($shippingAddressAvailable?$defaultShippingAddress->id:"");?>";
var flexToken;
var pan;
var storeCard = false;
// Order details Object. Store details submitted on index.php, for use in the various Steps.
let orderDetails = {
    referenceNumber: <?php echo '"' . $_REQUEST['reference_number'] . '"'; ?>,
    amount: <?php echo '"' . $_REQUEST['amount'] . '"'; ?>,
    currency: <?php echo '"' . $_REQUEST['currency'] . '"'; ?>,
    shippingAddressRequired: shippingAddressRequired,
    useShippingAsBilling: false,
    ship_to: {
        ship_to_forename: "",
        ship_to_surname: "",
        ship_to_address_line1: "",
        ship_to_address_line2: "",
        ship_to_address_city: "",
        ship_to_postcode: "",
        ship_to_address_country: ""
    },
    bill_to: {
        bill_to_forename: "",
        bill_to_surname: "",
        bill_to_email: "",
        bill_to_address_line1: "",
        bill_to_address_line2: "",
        bill_to_address_city: "",
        bill_to_postcode: "",
        bill_to_address_country: ""
    }
};
// custom styles that will be applied to each field we create using Microform
var myStyles = {
    'input': {
        'font-family': '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol"',
        'font-size': '1rem',
        'line-height': '1.5',
        'color': '#495057'
    },
    '::placeholder': {'color': 'grey'},
    ':focus': {'color': 'blue'},
    ':hover': {'font-style': 'italic'},
    ':disabled': {'cursor': 'not-allowed'},
    'valid': {'color': 'green'},
    'invalid': {'color': 'red'}
};
var flex;
var microform;
var number;
var securityCode;
var payButton;
var panValid;
var cvnValid;
var secCodeLbl;
var form;
var numberContainer;
<?php
if ($paymentInstrumentCount) {
    echo "var xxx =  '" . json_encode($storedCards) . "';\n";
    echo "var paymentInstruments = JSON.parse(xxx);\n";
} else {
    echo "var paymentInstruments\n;";
}
?>
console.log(paymentInstruments);
document.addEventListener("DOMContentLoaded", function (e) {
    flex = new Flex(captureContext);
    microform = flex.microform({styles: myStyles});
    number = microform.createField('number', {placeholder: 'Card number'});
<?php
    if($paymentInstrumentCount && $defaultPaymentInstrument->card->type == "003"){
        $placeHolder = "'••••'";
        $cvvLength=4;
    }else{
        $placeHolder = "'•••'";
        $cvvLength=3;
    }
    echo "\tsecurityCode = microform.createField('securityCode', {placeholder: ". $placeHolder . ", maxLength: " . $cvvLength ."});\n";
?>
    payButton = document.querySelector('#payButton');
    numberContainer = document.querySelector('#number-container');
    panValid = false;
    cvnValid = false;

    securityCode.load('#securityCode-container');
    secCodeLbl = document.querySelector('#securityCodeLabel');

    if (paymentInstrumentId === "") {
        showPanField(true);
    } else {
        showPanField(false);
        // We have a default Payment Instrument
    }

   console.log("\ncaptureContext:\n" + captureContext);

    form = document.querySelector('#payment_form');
    number.on('change', function (data) {
        console.log(data);
        // Set "CVV" text with name based on scheme
        secCodeLbl.textContent = (data.card && data.card.length > 0) ? data.card[0].securityCode.name : 'CVN';
        if(data.card && data.card.length > 0){
            updateSecurityCodeField(data.card[0].cybsCardType);
        }
        panValid = data.valid;
        fieldsValid(panValid);
    });
    number.on('autocomplete', function (data) {
        console.log(data);
        if (data.expirationMonth) {
            document.getElementById('card_expirationMonth').value = data.expirationMonth;
        }
        if (data.expirationYear) {
            document.getElementById('card_expirationYear').value = parseInt(data.expirationYear) - 2000;
        }
    });
    securityCode.on('change', function (data) {
        console.log(data);
        cvnValid = data.valid;
        fieldsValid(cvnValid);
    });
    payButton.addEventListener('click', (event) => {
        if (formsValidated()) {
            payNow();
        }
    });
});
function updateSecurityCodeField(type){
    // If Amex, CVVis 4 digits, else 3
    if(type === "003"){
        securityCode.update({placeholder: "••••", maxLength: 4});
    }else{
        securityCode.update({placeholder: "•••", maxLength: 3});
    }
}
function cancel() {
    onFinish("CANCELLED", 0, false, false, "n/a", "User Cancelled", "");
    window.location.assign("index.php");
}
function formsValidated() {
    form = document.getElementById('emailForm');
    ret = validateForm(form);
//    ret = true;
    if(ret && shippingAddressRequired && shippingAddressId === ""){
        orderDetails.useShippingAsBilling = shipAsBill();
        form = document.getElementById('shippingForm');
        ret = validateForm(form);
    }
    if(ret && paymentInstrumentId === "" && (!shippingAddressRequired || !orderDetails.useShippingAsBilling)){
        form = document.getElementById('billingForm');
        ret = validateForm(form);
    }
    return ret;
}
function shipAsBill(){
    usb = document.querySelector('#useShipAsBill');
    if(usb){
        return usb.checked;
    }
    return false;
}
function validateForm(form){
    if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
        form.classList.add('was-validated');
        return false;
    }
    return true;
}
function payNow() {
    if (paymentInstrumentId !== "") {
        var options = {
        };
    } else {
        var options = {
            expirationMonth: getMonth(),
            expirationYear: 20 + document.getElementById('card_expirationYear').value
        };
    }
    microform.createToken(options, function (err, jwt) {
        if (err) {
            // handle error
            console.log(err);
            onFinish("FLEXERROR", "", false, false, "", err.reason, "");
        } else {
            // At this point you may pass the token back to your server as you wish.
//          console.log( "\nGot Token:\n" + jwt);
            if (paymentInstrumentId === "") {
                maskedPan = getPAN(jwt);
            }
//          console.log( "\nGot PAN:" + pan);
            flexToken = getJTI(jwt);
            // IF storeCard checked, we will create a Token
            let sc = document.getElementById('storeCard');
            if (sc.checked) {
                storeCard = true;
            }
            setUpPayerAuth();
        }
        document.getElementById("resultSection").style.display = "block";
        document.getElementById('paymentDetailsSection').style.display = "none";
    });
}
function getMonth() {
    months = ["01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12"];
    m = parseInt(document.getElementById('card_expirationMonth').value);
    // turn 1,2,3,4 into "01","02","03", etc
    return(months[m - 1]);
}
function fieldsValid(valid) {
    if (!valid || !expiryDateValid() || !(panValid && cvnValid)) {
        // Check PAN and CVN both Populated and valid
        payButton.disabled = true;
        return false;
    }
    payButton.disabled = false;
    return true;
}
function getJTI(jwt) {
    jti = getPayload(jwt).jti;
//  console.log("JTI:" + jti);
    return (jti);
}
function getPAN(jwt) {
    pan = getPayload(jwt).data.number;
    // console.log("PAN:" + pan);
    return (pan);
}
function getPayload(jwt) {
    jwtArray = jwt.split(".");
    payloadB64 = jwtArray[1];
    payload = window.atob(payloadB64);
    payloadJ = JSON.parse(payload);
//  console.log(payloadJ);
    return payloadJ;
}
function useSameAddressChanged() {
    orderDetails.useShippingAsBilling = shipAsBill();
    if (orderDetails.useShippingAsBilling) {
        // Hide Billing fields
        document.getElementById('billingForm').style.display = "none";
    }else{
        document.getElementById('billingForm').style.display = "block";
    }
}
function showPanField(show) {
    if (show) {
        panValid = false;
        document.getElementById('cardDetailsSection').style.display = "block";
        document.getElementById('storeCardSection').style.display = "block";
        if (!number._loaded) {
            number.load('#number-container');
        }
    } else {
        panValid = true;
        document.getElementById('cardDetailsSection').style.display = "none";
        document.getElementById('storeCardSection').style.display = "none";
        if (number._loaded) {
            number.unload();
        }
    }
}
function setOrderDetails() {
    if(shippingAddressRequired && shippingAddressId === ""){
        orderDetails.useShippingAsBilling = shipAsBill();

        orderDetails.ship_to.ship_to_forename = document.getElementById('ship_to_forename').value;
        orderDetails.ship_to.ship_to_surname = document.getElementById('ship_to_surname').value;
        orderDetails.ship_to.ship_to_address_line1 = document.getElementById('ship_to_address_line1').value;
        orderDetails.ship_to.ship_to_address_line2 = document.getElementById('ship_to_address_line2').value;
        orderDetails.ship_to.ship_to_address_city = document.getElementById('ship_to_address_city').value;
        orderDetails.ship_to.ship_to_postcode = document.getElementById('ship_to_postcode').value;
        orderDetails.ship_to.ship_to_address_country = document.getElementById('ship_to_address_country').value;
    }
    if(paymentInstrumentId === ""){
        if(!orderDetails.useShippingAsBilling){
            orderDetails.bill_to.bill_to_forename = document.getElementById('bill_to_forename').value;
            orderDetails.bill_to.bill_to_surname = document.getElementById('bill_to_surname').value;
            orderDetails.bill_to.bill_to_address_line1 = document.getElementById('bill_to_address_line1').value;
            orderDetails.bill_to.bill_to_address_line2 = document.getElementById('bill_to_address_line2').value;
            orderDetails.bill_to.bill_to_address_city = document.getElementById('bill_to_address_city').value;
            orderDetails.bill_to.bill_to_postcode = document.getElementById('bill_to_postcode').value;
            orderDetails.bill_to.bill_to_address_country = document.getElementById('bill_to_address_country').value;
        }
        orderDetails.bill_to.bill_to_email = document.getElementById('bill_to_email').value;
    }
}
function setUpPayerAuth() {
    setOrderDetails();
    $.ajax({
        type: "POST",
        url: "rest_setup_payerAuth.php",
        data: JSON.stringify({
            "order": orderDetails,
            "customerId": customerId,
            "paymentInstrumentId": paymentInstrumentId,
            "transientToken": flexToken
        }),
        success: function (result) {
            res = JSON.parse(result);
            console.log("\nSetup Payer Auth:\n" + JSON.stringify(res, undefined, 2));
            // If OK, set up device collection
            let httpCode = res.httpCode;
            if (httpCode === "201") {
                // Set up device collection
                deviceDataCollectionURL = res.response.consumerAuthenticationInformation.deviceDataCollectionUrl;
                accessToken = res.response.consumerAuthenticationInformation.accessToken;
                doDeviceCollection(deviceDataCollectionURL, accessToken);
            } else {
                // 500 System error or anything else
                onFinish(status, "", false, false, httpCode, res.response.reason, res.response.message);
            }
        }
    });
}
function doDeviceCollection(url, accessTokenJwt) {
    console.log("\ndoDeviceCollection URL:" + url);
    document.getElementById('cardinal_collection_form').action = url;
    document.getElementById('cardinal_collection_form_input').value = accessTokenJwt;
    document.getElementById('cardinal_collection_form').submit();
}
window.addEventListener("message", (event) => {
    //{MessageType: "profile.completed", SessionId: "0_57f063fd-659a-4779-b45b-9e456fdb7935", Status: true}
    if (event.origin === "https://centinelapistag.cardinalcommerce.com") {
        console.log("\nMessage origin:" + event.origin);
        let data = JSON.parse(event.data);
        console.log("\nMessage data:" + JSON.stringify(event.data, undefined, 2));

        if (data !== undefined && data.Status) {
            console.log("\nSessionId:" + data.SessionId);
            authorizeWithPA(data.SessionId, "", "CONSUMER_AUTHENTICATION");
        }
    }
}, false);
/*
 * This function sends a combined enrollment + Authorization request message to Cybersource.
 * the enrollment request is performed first.  If it is successful (i.e ReasonCode=100 - Card is NOT enrolled), then
 * the authorization request is performed.  If the card IS enrolled then the reasonCode = 475 and the the authorization
 * request is NOT performed.  In the latter case the cardholder authentication step is performed and a combined
 * validation + Authorization request is generated.
 */
function authorizeWithPA(dfReferenceId, authenticationTransactionID, paAction) {
    console.log("\nChecking enrollment...\n");
    $.ajax({
        type: "POST",
        url: "rest_auth_with_pa.php",
        data: JSON.stringify({
            "local": local,
            "order": orderDetails,
            "storeCard": storeCard,
            "customerId": customerId,
            "paAction": paAction,
            "paymentInstrumentId": paymentInstrumentId,
            "shippingAddressId": shippingAddressId,
            "transientToken": flexToken,
            "referenceID": dfReferenceId,
            "authenticationTransactionID": authenticationTransactionID,
            "standAlone": standAlone
        }),
        success: function (result) {
            // Response is a json string - turn it into a javascript object
            let res = JSON.parse(result);
            console.log("\nEnrollment:\n" + JSON.stringify(res, undefined, 2));
            let httpCode = res.httpCode;
            let status = res.response.status;
            if (httpCode === "201") {
                customerCreated = false;
                paymentInstrumentCreated = false;
                shippingAddressCreated = false;
                let requestID = res.response.id;
                // Successfull response (but could be declined)
                if (status === "PENDING_AUTHENTICATION") {
                    // Card is enrolled - Kick off the cardholder authentication
                    showStepUpScreen(res.response.consumerAuthenticationInformation.stepUpUrl, res.response.consumerAuthenticationInformation.accessToken);
                } else if (status === "AUTHORIZED") {
                    if (storeCard) {
                        paymentInstrumentCreated = true;
                        paymentInstrumentId = res.response.tokenInformation.paymentInstrument.id;
                        if(shippingAddressRequired && shippingAddressId === ""){
                            // Not using an existing shippingAddress so must be creating a new one
                            shippingAddressId = res.response.tokenInformation.shippingAddress.id;
                        }
                        if (!customerId) {
                            // New Customer
                            customerCreated = true;
                            customerId = res.response.tokenInformation.customer.id;
                        }
                    }
                    onFinish(status, requestID, customerCreated, paymentInstrumentCreated, httpCode, "", "");
                } else {
                    onFinish(status, requestID, false, false, httpCode, res.response.reason, res.response.message);
                }
            } else {
                // 500 System error or anything else
                switch(httpCode){
                    case "202":
                        onFinish(status, "", false, false, httpCode, res.response.errorInformation.reason, res.response.errorInformation.message);
                        break;
                    case "400":
                        onFinish(status, "", false, false, httpCode, res.response.reason, res.response.details);
                        break;
                    default:
                        onFinish(status, "", false, false, httpCode, res.response.reason, res.response.message);
                }
            }
        }
    });
}
function showStepUpScreen(stepUpURL, jwt) {
    on();
    // console.log( "Challenge Screen:\n"+stepUpURL);
    document.getElementById('step_up_iframe').style.display = "block";
    document.getElementById('step_up_form').action = stepUpURL;
    document.getElementById('step_up_form_jwt_input').value = jwt;
    var stepUpForm = document.getElementById('step_up_form');
    if (stepUpForm)
        stepUpForm.submit();
}
function hideStepUpScreen(transactionId) {
    off();
    console.log("Challenge Complete TransactionId:\n" + transactionId);
    document.getElementById('step_up_iframe').style.display = "none";
    authorizeWithPA("", transactionId, "VALIDATE_CONSUMER_AUTHENTICATION");
}
function on() {
    document.getElementById("overlay").style.display = "block";
}
function off() {
    document.getElementById("overlay").style.display = "none";
}
function editShippingAddress(){
    document.getElementById('paymentDetailsSection').style.display = "none";
    document.getElementById('iframeSection').style.display = "block";
    document.getElementById('customerToken').value = customerId;
    var iframeForm = document.getElementById('iframe_form');
    if (iframeForm){
        iframeForm.action = "edit_addresses.php";
        iframeForm.submit();
    }
}
function onShippingAddressUpdated(id, shipToText) {
    shippingAddressId = id;
    console.log("onShippingAddressUpdated:\n" + id + "\n"+ shipToText);
    document.getElementById('ship_to_text').innerHTML = shipToText;
    document.getElementById('iframeSection').style.display = "none";
    document.getElementById('paymentDetailsSection').style.display = "block";
}
function onPaymentInstrumentUpdated(id, paymentInstrument) {
    paymentInstrumentId = id;
    maskedPan = paymentInstrument._embedded.instrumentIdentifier.card.number;
    console.log("onPaymentInstrumentUpdated:\n" + id + "\n"+ JSON.stringify(paymentInstrument, undefined, 2));
xxx = stylePaymentInstrument(paymentInstrument);
    document.getElementById('storedCardSection').innerHTML  = xxx;
    document.getElementById('iframeSection').style.display = "none";
    document.getElementById('paymentDetailsSection').style.display = "block";
    updateSecurityCodeField(paymentInstrument.card.type);
}
function stylePaymentInstrument(paymentInstrument){
    img = "";
    alt = "";
    if (paymentInstrument.card.type === "001") {
        img = "images/Visa.svg";
        alt = "Visa card logo";
    } else if (paymentInstrument.card.type === "002") {
        img = "images/Mastercard.svg";
        alt = "Mastercard logo";
    } else {
        img = "images/Amex.svg";
        alt = "Amex card logo";
    }
    xxx =   "<h5>Stored Card Details</h5>" +
            "<div class=\"row\">\n" +
                "<div class=\"col-sm-1\">\n"+
                    "<img  src=\"" + img + "\" class=\"img-fluid\" alt=\"" + alt + "\">"+
                "</div>\n" +
                "<div class=\"col-sm-1\">\n" +
                    "<ul class=\"list-unstyled\">" +
                        "<li><strong>" + paymentInstrument._embedded.instrumentIdentifier.card.number + "</strong></li>\n" +
                        "<li><small>Expires:&nbsp;" + paymentInstrument.card.expirationMonth + "/" + paymentInstrument.card.expirationYear + "</small></li>\n" +
                    "</ul>\n" +
                "</div>\n" +
                "<div class=\"row\">\n"+
                    "<h5>Card Billing Address</h5>" +
                    "<div class=\"col-sm-6\">" +
                        "<span id=\"billingText\" class=\"form-control form-control-sm\" disabled>" + concatinateNameAddress(paymentInstrument.billTo) + "</span>\n" +
                    "</div>\n" +
                "</div>\n";
    return xxx;
}
function concatinateNameAddress(nameAddress){
    // return name and address string
    if(!nameAddress.hasOwnProperty("address2")){
        nameAddress.address2 = "";
    }
    return xtrim(nameAddress.firstName, " ") +
            xtrim(nameAddress.lastName, "\n") +
            xtrim(nameAddress.address1, ", ") +
            xtrim(nameAddress.address2, ", ") +
            xtrim(nameAddress.locality, ", ") +
            xtrim(nameAddress.postalCode, ", ") +
            xtrim(nameAddress.country, ".");
}
function xtrim(xin, suffix){
    xout = xin.trim();
    return (xout===""? "" : xout + suffix) ;
}
function onIframeCancelled(){
    document.getElementById('iframeSection').style.display = "none";
    document.getElementById('paymentDetailsSection').style.display = "block";
}
function editCard(){
    document.getElementById('paymentDetailsSection').style.display = "none";
    document.getElementById('iframeSection').style.display = "block";
    document.getElementById('customerToken').value = customerId;
    var iframeForm = document.getElementById('iframe_form');
    if (iframeForm){
        iframeForm.action = "edit_cards.php";
        iframeForm.submit();
    }
}
function newCard(){
    oldPaymentSnstrumentId=paymentInstrumentId;
    paymentInstrumentId = "";
    showPanField(true);
    document.getElementById('billingForm').style.display = "block";
    document.getElementById('defaultBillingSection').style.display = "none";
    document.getElementById('storedCardSection').style.display = "none";
}
function cancelNewCard(){
    paymentInstrumentId = oldPaymentSnstrumentId;
    showPanField(false);
    document.getElementById('billingForm').style.display = "none";
    document.getElementById('defaultBillingSection').style.display = "block";
    document.getElementById('storedCardSection').style.display = "block";
}
function onFinish(status, requestId, newCustomer, paymentInstrumentCreated, httpResponseCode, errorReason, errorMessage) {
    finish = "onFinish: " + JSON.stringify({
        "referenceNumber": orderDetails.referenceNumber,
        "status": status,
        "httpResponseCode": httpResponseCode,
        "requestId": requestId,
        "amount": orderDetails.amount,
        "pan": maskedPan,
        "newCustomer": newCustomer,
        "newPaymentInstrument": paymentInstrumentCreated,
        "customerId": customerId,
        "paymentInstrumentId": paymentInstrumentId,
        "shippingAddressId": shippingAddressId,
        "errorReason": errorReason,
        "errorMessage": errorMessage
    }, undefined, 2);
    console.log(finish);
    if (status === "AUTHORIZED") {
        text = "Thank you.  Your payment has completed" + "<BR><PRE>" + finish +"</PRE>";
        document.getElementById("newPaymentButton").style.display = "block";
    } else {
        text = "Oh dear. Your payment was not approved.  You can try again or try a different payment method" + "<BR>" + finish;
        document.getElementById("retryButton").style.display = "block";
    }
    result = document.getElementById("result").innerHTML = text;
    result = document.getElementById("result").style.display = "block";
    document.getElementById("progressSpinner").style.display = "none";
    if(newCustomer && !paymentInstrumentId !== ""){
        // Write new Customer Token to cookie
        document.cookie = "customerId=" + customerId;
    }
}
</script>
