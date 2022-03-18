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
        <input id="orderId" type="hidden" name="orderId" value="<?php echo $_REQUEST['orderId'];?>">
        <input id="customerToken" type="hidden" name="customerToken" value="<?php echo $customerToken;?>">
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
    <div class="container">
        <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" role="dialog" aria-hidden="false">
          <div class="modal-dialog modal-tall">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">3DS</h5>
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
                    <div id="emailSection">
                        <div id="emailText"><?php echo $defaultEmail;?></div>
                        <button id="summaryEmail" type="button" class="btn btn-link p-0" onclick="editEmail()">Edit</button>
                    </div>
                    <form id="emailForm" class="needs-validation" novalidate style="display:none">
                        <div class="row">
                            <div class="col-9">
                                <div class="form-group mb-3">
                                    <input id="bill_to_email" type="email" class="form-control" value="<?php echo $defaultEmail;?>" placeholder="Enter email" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="button" class="btn btn-primary" onclick="updateEmail(true)">Update</button>
                                <button type="button" class="btn btn-secondary" onclick="updateEmail(false)">Cancel</button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
            <div class="row">
                <div class="col-3">
                    <h5>Delivery:</h5>
                </div>
                <div class="col-9">
                    <div id="shipToText"></div>
                    <button id="summaryEditAddress" type="button" class="btn btn-link p-0" onclick="editShippingAddress()">Edit</button>
                </div>
            </div>
            <div class="row">
                <div class="col-3">
                </div>
            </div>
            <div id="storedCardSection">
                <div class="row">
                    <div class="col-3">
                        <h5>Card:</h5>
                    </div>
                    <div class="col-2">
                        <img src="images/<?php echo $cardTypes[$defaultPaymentInstrument->card->type]['image'];?>" class="img-fluid" alt="<?php echo $cardTypes[$defaultPaymentInstrument->card->type]['alt'];?>">
                    </div>
                    <div class="col-7">
                        <ul class="list-unstyled">
                            <li><strong><?php echo $defaultPaymentInstrument->_embedded->instrumentIdentifier->card->number;?></strong></li>
                            <li><small>Expires:&nbsp;<?php echo $defaultPaymentInstrument->card->expirationMonth . "/" . $defaultPaymentInstrument->card->expirationYear;?></small></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-3">
                </div>
                <div class="col-9">
                    <div id="billToText"></div>
                    <button id="summaryEditCard" type="button" class="btn btn-link p-0" onclick="editCard()">Edit</button>
                </div>
            </div>
            <div id="paymentDetailsSection">
                <div class="row">
                    <div class="col-3"></div>
                    <div class="col-9">
                        <form id="cardForm">
                            <div id="cardInputSection">
                            </div>
                        </form>
                    </div>
                </div>
                <div id="payButtonSection" class="row">
                    <div class="col-3">
                    </div>
                    <div class="col-9">
                        <button type="button" id="payButton" onclick="nextButton('cvv')" class="btn btn-primary" disabled="true">Pay</button>
                        <button type="button" class="btn btn-secondary" onclick="cancel()">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
        <div id="confirmSection" style="display: none">
            <div class="row">
                <div class="col-12">
                    <button type="button" class="btn btn-primary" onclick="nextButton('confirm')">Confirm</button>
                    <button type="button" class="btn btn-secondary" onclick="backButton('confirm')">Back</button>
                    <button type="button" class="btn btn-secondary" onclick="cancel()">Cancel</button>
                </div>
            </div>
        </div>
        <div id="authSection" style="display: none;">
            <form id="step_up_form" name="stepup" method="POST" target="stepUpIframe" action="">
                <input id="step_up_form_jwt_input" type="hidden" name="JWT" value=""/>
                <input id="MD" type="hidden" name="MD" value="HELLO MUM. GET THE KETTLE ON"/>
            </form>
            <div class="d-flex justify-content-center">
                <div id="authSpinner" class="spinner-border" ></div>
            </div>
            <iframe id="step_up_iframe" style="overflow: hidden; display: block; border:none; height:100vh; width:100%" name="stepUpIframe" ></iframe>
        </div>
        <div id="resultSection" style="display: none">
            <h3>Result</h3>
            <div id="resultText"></div>
            <div class="row">
                <div class="col-12">
                    <button type="button" class="btn btn-primary" onclick="window.open('index.php', '_parent')">Home</button>
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
<script src="js/authorise.js"></script>
<script>
var oldPaymentInstrumentId;
var defaultPaymentInstrumentJson = '<?php echo json_encode($defaultPaymentInstrument);?>';
var defaultShippingAddressJson = '<?php echo json_encode($defaultShippingAddress);?>';
// Order details Object. Store details submitted on index.php, for use in the various Steps.
let orderDetails = {
        referenceNumber: "<?php echo $_REQUEST['reference_number'];?>",
        orderId: "<?php echo $_REQUEST['orderId'];?>",
        amount: "<?php echo $_REQUEST['amount'];?>",
        currency: "<?php echo $_REQUEST['currency'];?>",
        local: <?php echo isset($_REQUEST['local']) && $_REQUEST['local'] === "true"?"true":"false";?>,
        shippingAddressRequired: true,
        useShippingAsBilling: true,
        customerId: "<?php echo $customerToken;?>",
        paymentInstrumentId: "<?php echo $defaultPaymentInstrument->id;?>",
        shippingAddressId: "<?php echo $defaultShippingAddress->id;?>",
        flexToken: "",
        maskedPan: "<?php echo $defaultPaymentInstrument->_embedded->instrumentIdentifier->card->number;?>",
        storeCard: false,
        capture: <?php echo isset($_REQUEST['autoCapture']) && $_REQUEST['autoCapture'] === "true"?"true":"false";?>,
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
            email: '<?php echo $defaultEmail;?>',
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
function cancel() {
    window.open('index.php', '_parent');
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
function editEmail(){
    document.getElementById('emailForm').style.display = "block";
    document.getElementById('emailSection').style.display = "none";
}
function updateEmail(update){
    if(update){
        orderDetails.bill_to.email = document.getElementById('bill_to_email').value;
        document.getElementById('emailText').innerHTML = orderDetails.bill_to.email;
    }
    document.getElementById('emailForm').style.display = "none";
    document.getElementById('emailSection').style.display = "block";
}
function nextButton(form){
    switch(form){
        case "email":
        case "shipping":
            break;
        case "cvv":
            // Pay Button clicked
            getToken(onTokenCreated);
            break;
        case "confirm":
            // Confirm Button clicked
            document.getElementById("confirmSection").style.display = "none";
            authorise();
            break;
    }
}
function backButton(form){
    switch(form){
        case "shipping":
        case "card":
            break;
        case "confirm":
            document.getElementById("paymentDetailsSection").style.display = "block";
            document.getElementById("summaryEditAddress").style.display = "block";
            document.getElementById("summaryEditCard").style.display = "block";
            document.getElementById("confirmSection").style.display = "none";
            break;
    }
}
function onTokenCreated(tokenDetails){
    // Hide CVV input, show Confirmation section
    document.getElementById("paymentDetailsSection").style.display = "none";
    document.getElementById("summaryEditAddress").style.display = "none";
    document.getElementById("summaryEditCard").style.display = "none";
    document.getElementById("confirmSection").style.display = "block";
    console.log(tokenDetails);
    orderDetails.flexToken = tokenDetails.flexToken;
//    authorise();
}
function authorise() {
    document.getElementById('authSection').style.display = "block";
    setUpPayerAuth();
}
function editShippingAddress(){
    document.getElementById('paymentDetailsSection').style.display = "none";
    document.getElementById('iframeSection').style.display = "block";
//    document.getElementById('customerToken').value = orderDetails.customerId;
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
function onPaymentInstrumentUpdated(id, paymentInstrument) {
    orderDetails.paymentInstrumentId = id;
    orderDetails.maskedPan = paymentInstrument._embedded.instrumentIdentifier.card.number;
    console.log("onPaymentInstrumentUpdated:\n" + id + "\n"+ JSON.stringify(paymentInstrument, undefined, 2));
    html = stylePaymentInstrument(paymentInstrument._embedded.instrumentIdentifier.card.number,
                paymentInstrument.card, paymentInstrument.billTo);
    document.getElementById('storedCardSection').innerHTML  = html;
    btt = document.getElementById('billToText');
    if(btt){
        btt.innerHTML = formatNameAddress(paymentInstrument.billTo);
    }
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
                "<div class=\"col-7 \">\n" +
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
//    document.getElementById('customerToken').value = orderDetails.customerId;
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
    document.getElementById('storedCardSection').style.display = "none";
}
function cancelNewCard(){
    orderDetails.paymentInstrumentId = oldPaymentInstrumentId;
    showPanField(false);
    document.getElementById('billingForm').style.display = "none";
    document.getElementById('storedCardSection').style.display = "block";
}
</script>
