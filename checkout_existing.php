<?php
include_once 'card_types.php';
include_once 'countries.php';
include 'rest_get_customer.php';
$defaultEmail="";
if(isset($_REQUEST['email']) && !empty($_REQUEST['email'])) {
    $defaultEmail = $_REQUEST['email'];
}else{
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
    <form id="iframe_form" method="POST" target="shippingAddress_iframe" action="">
        <input id="customerToken" type="hidden" name="customerToken" value="">
        <input id="currency" type="hidden" name="currency" value="<?php echo $_REQUEST['currency']?>">
        <input id="email" type="hidden" name="email" value="<?php echo $defaultEmail;?>">
        <input id="reference_number" type="hidden" name="reference_number" value="<?php echo $_REQUEST['reference_number'];?>">
    </form>
    <!--Cardinal device data collection code START-->
    <iframe id="cardinal_collection_iframe" name="collectionIframe" height="1" width="1" style="display: none;"></iframe>
    <form id="cardinal_collection_form" method="POST" target="collectionIframe" action="">
        <input id="cardinal_collection_form_input" type="hidden" name="JWT" value=""/>
    </form>
    </<!--Cardinal device data collection code END-->
    <form id="step_up_form" name="stepup" method="POST" target="stepUpIframe" action="">
        <input id="step_up_form_jwt_input" type="hidden" name="JWT" value=""/>
        <input id="MD" type="hidden" name="MD" value="HELLO MUM. GET THE KETTLE ON"/>
    </form>
    <div class="container">
        <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" role="dialog" aria-hidden="false">
          <div class="modal-dialog modal-tall">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">3DS</h5>
              </div>
              <div class="modal-body">
                <iframe style="overflow: hidden; display: block; border:none; height:75vh; width:100%" name="stepUpIframe" ></iframe>
              </div>
            </div>
          </div>
        </div>
        <div class="d-flex justify-content-center">
            <div id="mainSpinner" class="spinner-border" style="display: block;"></div>
        </div>
        <div id="iframeSection" style="display: none">
            <iframe id="shippingAddressIframe" name="shippingAddress_iframe" src="about:blank" class="responsive-iframe" style="overflow: hidden; display: block; border:none; height:100vh; width:100%" ></iframe>
        </div>
        <div id="summarySection">
            <div class="row">
                <h3>Your Order</h3>
            </div>
            <div class="row">
                <div class="col-3">
                    <h5>Total:</h5>
                </div>
                <div class="col-9">
                    <span><?php echo "£" . $_REQUEST['amount'];?></span>
                </div>
            </div>
            <div class="row">
                <div class="col-3">
                    <h5>Email:</h5>
                </div>
                <div class="col-9">
                    <?php echo $defaultEmail;?>
                </div>
            </div>
            <div class="row">
                <div class="col-3">
                    <h5>Delivery:</h5>
                </div>
                <div class="col-7" id="shipToText"></div>
                <div class="col-2" id="summaryEditAddress">
                    <button type="button" class="btn btn-link p-0" onclick="editShippingAddress()">Edit</button>
                </div>
            </div>
            <div class="row">
                <div class="col-3">
                </div>
            </div>
            <div id="storedCardSection">
                <h5>Card:</h5>
                <div class="row">
                    <div class="col-3">
                        <img src="images/<?php echo $cardTypes[$defaultPaymentInstrument->card->type]['image'];?>" class="img-fluid" alt="<?php echo $cardTypes[$defaultPaymentInstrument->card->type]['alt'];?>">
                    </div>
                    <div class="col-7">
                        <ul class="list-unstyled">
                            <li><strong><?php echo $defaultPaymentInstrument->_embedded->instrumentIdentifier->card->number;?></strong></li>
                            <li><small>Expires:&nbsp;<?php echo $defaultPaymentInstrument->card->expirationMonth . "/" . $defaultPaymentInstrument->card->expirationYear;?></small></li>
                        </ul>
                    </div>
                    <div class="col-2">
                        <button type="button" class="btn btn-link p-0 text-start" onclick="editCard()">Use a different Card</button>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-3">
                    <h5>Card Billing:</h5>
                </div>
                <div class="col-7" id="billToText"></div>
                <div class="col-2" id="summaryEditCard">
                    <button type="button" class="btn btn-link p-0" onclick="editCard()">Edit</button>
                </div>
            </div>
        </div>
        <div id="paymentDetailsSection">
            <input id="bill_to_email" type="hidden" value="<?php echo $defaultEmail;?>">
            <div id="cardSection" style="display: block">
                <form id="cardForm">
                    <div id="cardInputSection">
                    </div>
                </form>
                <div id="billingAddressSection" style="display: block">
                    <div id="defaultBillingSection">
                        <div class="row">
                        </div>
                    </div>
                    <div id="payButtonSection" class="row">
                        <div class="col-12">
                            <button type="button" id="payButton" onclick="payNow()" class="btn btn-primary" disabled="true">Pay</button>
                            <button type="button" class="btn btn-secondary" onclick="cancel()">Cancel</button>
                        </div>
                    </div>
                    <div class="row">
                    </div>
                </div>
            </div>
        </div>
        <div id="resultSection" style="display: none">
            <h3>Result</h3>
            <p id="result"></p>
            <div class="row">
                <div class="col-12">
                    <button type="button" class="btn btn-primary" onclick="window.location.href='index.php'">New Payment</button>
                    <button type="button" id="retryButton" class="btn btn-secondary" onclick="window.location.reload(true)">Try again</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
</body>
<script src="https://flex.cybersource.com/cybersource/assets/microform/0.11/flex-microform.min.js"></script>
<script src="js/newCard2.js"></script>
<script src="js/utils.js"></script>
<script>
var oldPaymentInstrumentId;
<?php
  echo "var defaultPaymentInstrumentJson = '" . json_encode($defaultPaymentInstrument) ."';\n";
  echo "var defaultShippingAddressJson = '" . json_encode($defaultShippingAddress) ."';\n";
?>
// Order details Object. Store details submitted on index.php, for use in the various Steps.
let orderDetails = {
        referenceNumber: "<?php echo $_REQUEST['reference_number'];?>",
        amount: "<?php echo $_REQUEST['amount'];?>",
        currency: "<?php echo $_REQUEST['currency'];?>",
        standAlone: <?php echo isset($_REQUEST['standAlone'])?"true":"false";?>,
        local: <?php echo isset($_REQUEST['local']) && $_REQUEST['local'] === "true"?"true":"false";?>,
        shippingAddressRequired: true,
        useShippingAsBilling: true,
        customerId: "<?php echo $customerToken;?>",
        paymentInstrumentId: "<?php echo $defaultPaymentInstrument->id;?>",
        maskedPan: "<?php echo $defaultPaymentInstrument->_embedded->instrumentIdentifier->card->number;?>",
        flexToken: "",
        shippingAddressId: "<?php echo $defaultShippingAddress->id;?>",
        storeCard: false,
        capture: true,           // TODO hardcoded capture
        ship_to: {
            firstName: "",
            lastName: "",
            address1: "",
            address2: "",
            locality: "",
            postalCode: "",
            country: ""
        },
        bill_to: {
            firstName: "",
            lastName: "",
            email: "",
            address1: "",
            address2: "",
            locality: "",
            postalCode: "",
            country: ""
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
var number;
var payButton;
var myModal = new bootstrap.Modal(document.getElementById('staticBackdrop'), {keyboard: false});

document.addEventListener("DOMContentLoaded", function (e) {
    let defPI = JSON.parse(defaultPaymentInstrumentJson);
    document.getElementById('billToText').innerHTML = formatNameAddress(defPI.billTo);
    let defSA = JSON.parse(defaultShippingAddressJson);
    document.getElementById('shipToText').innerHTML = formatNameAddress(defSA.shipTo);
    createCardInput("cardInputSection", "mainSpinner", "payButton", true, false, defPI.card.type);
});
function payNow(){
    document.getElementById("paymentDetailsSection").style.display = "none";
    document.getElementById("summaryEditAddress").style.display = "none";
    document.getElementById("summaryEditCard").style.display = "none";

    if(orderDetails.flexToken ==""){
        getToken(onTokenCreated);
    }else{
        authorise();
    }
//    document.getElementById("mainSpinner").style.display = "block";
}
function onTokenCreated(tokenDetails){
    console.log(tokenDetails);
    orderDetails.flexToken = tokenDetails.flexToken;
    authorise();
}
function cancel() {
    onFinish(orderDetails, "CANCELLED", 0, false, false, "n/a", "User Cancelled", "");
    window.location.assign("index.php");
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
function setOrderDetails() {
    if(orderDetails.shippingAddressId === ""){
        orderDetails.useShippingAsBilling = shipAsBill();

        orderDetails.ship_to.firstname = document.getElementById('ship_to_firstname').value;
        orderDetails.ship_to.lastname = document.getElementById('ship_to_lastname').value;
        orderDetails.ship_to.address1 = document.getElementById('ship_to_address1').value;
        orderDetails.ship_to.address2 = document.getElementById('ship_to_address2').value;
        orderDetails.ship_to.locality = document.getElementById('ship_to_city').value;
        orderDetails.ship_to.postalCode = document.getElementById('ship_to_postalCode').value;
        orderDetails.ship_to.country = document.getElementById('ship_to_country').value;
    }
    if(orderDetails.paymentInstrumentId === ""){
        if(!orderDetails.useShippingAsBilling){
            orderDetails.bill_to.firstname = document.getElementById('bill_to_forename').value;
            orderDetails.bill_to.lastname = document.getElementById('bill_to_surname').value;
            orderDetails.bill_to.address1 = document.getElementById('bill_to_address_line1').value;
            orderDetails.bill_to.address2 = document.getElementById('bill_to_address_line2').value;
            orderDetails.bill_to.locality = document.getElementById('bill_to_address_city').value;
            orderDetails.bill_to.postalCode = document.getElementById('bill_to_postcode').value;
            orderDetails.bill_to.country = document.getElementById('bill_to_address_country').value;
        }
        orderDetails.bill_to.email = document.getElementById('bill_to_email').value;
    }
}
function authorise() {
    var iframeForm = document.getElementById('iframe_form');
    if (iframeForm){
        sessionStorage.setItem("orderDetails", JSON.stringify(orderDetails));
        iframeForm.action = "authorise.php";
        iframeForm.target = "_self";
        iframeForm.submit();
    }
}
function editShippingAddress(){
    document.getElementById('paymentDetailsSection').style.display = "none";
    document.getElementById('iframeSection').style.display = "block";
    document.getElementById('customerToken').value = orderDetails.customerId;
    var iframeForm = document.getElementById('iframe_form');
    if (iframeForm){
        iframeForm.action = "edit_addresses.php";
        iframeForm.submit();
    }
}
function onShippingAddressUpdated(id, shipToText, shipTo) {
    orderDetails.shippingAddressId = id;
    console.log("onShippingAddressUpdated:\n" + id + "\n"+ shipToText);
    document.getElementById('shipToText').innerHTML = formatNameAddress(shipTo);
    document.getElementById('iframeSection').style.display = "none";
    document.getElementById('paymentDetailsSection').style.display = "block";
}
function onNewCardUsed(flexDetails, billToDetails) {
    console.log(flexDetails);
    console.log(billToDetails);
    // Hide/unload Security code
    document.getElementById('cardInputSection').style.display = "none";
    securityCode.unload();
    orderDetails.maskedPan = flexDetails.cardDetails.number;
    orderDetails.flexToken = flexDetails.flexToken;
    orderDetails.paymentInstrumentId = "";

    html = stylePaymentInstrument(orderDetails.maskedPan, flexDetails.cardDetails, billToDetails);
    document.getElementById('storedCardSection').innerHTML = html;
    document.getElementById('iframeSection').style.display = "none";
    document.getElementById('paymentDetailsSection').style.display = "block";
    document.getElementById('billToText').innerHTML = formatNameAddress(billToDetails);

    orderDetails.bill_to.firstname = billToDetails.firstName;
    orderDetails.bill_to.lastname = billToDetails.lastName;
    orderDetails.bill_to.address1 = billToDetails.address1;
    orderDetails.bill_to.address2 = billToDetails.address2;
    orderDetails.bill_to.locality = billToDetails.locality;
    orderDetails.bill_to.postalCode = billToDetails.postalCode;
    orderDetails.bill_to.country = billToDetails.country;
    orderDetails.bill_to.email = document.getElementById('bill_to_email').value;
    document.getElementById('payButton').disabled = false;
}
function onPaymentInstrumentUpdated(id, paymentInstrument) {
    orderDetails.paymentInstrumentId = id;
    orderDetails.maskedPan = paymentInstrument._embedded.instrumentIdentifier.card.number;
    console.log("onPaymentInstrumentUpdated:\n" + id + "\n"+ JSON.stringify(paymentInstrument, undefined, 2));
    html = stylePaymentInstrument(paymentInstrument._embedded.instrumentIdentifier.card.number,
                paymentInstrument.card, paymentInstrument.billTo);
    document.getElementById('storedCardSection').innerHTML  = html;
    document.getElementById('billToText').innerHTML = formatNameAddress(paymentInstrument.billTo);
    document.getElementById('iframeSection').style.display = "none";
    document.getElementById('paymentDetailsSection').style.display = "block";
    updateSecurityCodeField(paymentInstrument.card.type);
}
function stylePaymentInstrument(maskedPan, card, billTo){
    img = "";
    alt = "";
    if (card.type === "001" || card.type === "visa") {
        img = "images/Visa.svg";
        alt = "Visa card logo";
    } else if (card.type === "002" || card.type === "mastercard") {
        img = "images/Mastercard.svg";
        alt = "Mastercard logo";
    } else {
        img = "images/Amex.svg";
        alt = "Amex card logo";
    }
    html =  "<h5>Card:</h5>" +
            "<div class=\"row\">\n" +
                "<div class=\"col-3\">\n"+
                    "<img src=\"" + img + "\" class=\"img-fluid\" alt=\"" + alt + "\">"+
                "</div>\n" +
                "<div class=\"col-9\">\n" +
                    "<ul class=\"list-unstyled\">" +
                        "<li><strong>" + maskedPan + "</strong></li>\n" +
                        "<li><small>Expires:&nbsp;" + card.expirationMonth + "/" + card.expirationYear + "</small></li>\n" +
                    "</ul>\n" +
                "</div>\n" +
            "</div>\n";
    return html;
}
function onIframeCancelled(){
    document.getElementById('iframeSection').style.display = "none";
    document.getElementById('paymentDetailsSection').style.display = "block";
}
function editCard(){
    document.getElementById('paymentDetailsSection').style.display = "none";
    document.getElementById('iframeSection').style.display = "block";
    document.getElementById('customerToken').value = orderDetails.customerId;
    var iframeForm = document.getElementById('iframe_form');
    if (iframeForm){
        iframeForm.action = "edit_cards.php";
        iframeForm.submit();
    }
}
function newCard(){
    oldPaymentInstrumentId=orderDetails.paymentInstrumentId;
    orderDetails.paymentInstrumentId = "";
    showPanField(true);
    document.getElementById('billingForm').style.display = "block";
    document.getElementById('defaultBillingSection').style.display = "none";
    document.getElementById('storedCardSection').style.display = "none";
}
function cancelNewCard(){
    orderDetails.paymentInstrumentId = oldPaymentInstrumentId;
    showPanField(false);
    document.getElementById('billingForm').style.display = "none";
    document.getElementById('defaultBillingSection').style.display = "block";
    document.getElementById('storedCardSection').style.display = "block";
}
function onFinish(orderDetails2, status, requestId, newCustomer, paymentInstrumentCreated, httpResponseCode, errorReason, errorMessage) {
    document.getElementById('iframeSection').style.display = "none";
    document.getElementById("resultSection").style.display = "block";

    finish = "onFinish: " + JSON.stringify({
        "referenceNumber": orderDetails2.referenceNumber,
        "status": status,
        "httpResponseCode": httpResponseCode,
        "requestId": requestId,
        "amount": orderDetails2.amount,
        "pan": orderDetails2.maskedPan,
        "newCustomer": newCustomer,
        "newPaymentInstrument": paymentInstrumentCreated,
        "customerId": orderDetails2.customerId,
        "paymentInstrumentId": orderDetails2.paymentInstrumentId,
        "shippingAddressId": orderDetails2.shippingAddressId,
        "errorReason": errorReason,
        "errorMessage": errorMessage
    }, undefined, 2);
    console.log(finish);
    if (status === "AUTHORIZED") {
        text = "Thank you.  Your payment has completed" + "<BR><PRE>" + finish +"</PRE>";
        document.getElementById("retryButton").style.display = "none";
    } else {
        text = "Oh dear. Your payment was not successful.  You can try again or try a different payment method" + "<BR>" + finish;
    }
    result = document.getElementById("result").innerHTML = text;
}
</script>
