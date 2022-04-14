<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/php/utils/card_types.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/php/utils/countries.php';
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
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="/payPage/css/styles.css"/>
    <title>Payment Page</title>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
    <!--Cardinal device data collection code START-->
    <iframe id="cardinal_collection_iframe" name="collectionIframe" height="1" width="1" style="display: none;"></iframe>
    <form id="cardinal_collection_form" method="POST" target="collectionIframe" action="">
        <input id="cardinal_collection_form_input" type="hidden" name="JWT" value=""/>
    </form>
    </<!--Cardinal device data collection code END-->
    <div class="container d-flex justify-content-center">
        <div id="inputSection">
            <div class="d-flex justify-content-center">
                <div class="card">
                    <div class="card-body" style="width: 90vw">
                        <h5 class="card-title">Your Order</h5>
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
                                    <button id="summaryEmailButton" type="button" class="btn btn-link p-0" onclick="editEmail()">Edit</button>
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
                        <div id="summary_delivery" style="display:none">
                            <hr class="solid">
                            <h5 class="card-title">Delivery Address</h5>
                            <div id="shipToText" class="card-text small" style="max-height: 999999px;"></div>
                        </div>
                        <div id="summary_billTo" style="display:none">
                            <hr class="solid">
                            <h5 class="card-title">Payment Card</h5>
                            <p id="billToText" class="card-text small" style="max-height: 999999px;"></p>
                        </div>
                    </div>
                </div>
            </div>
            <BR>
            <div id="addressSection">
                <div class="row">
                    <div class="col-12"><h5>Tell us where to deliver your stuff:</h5></div>
                </div>
                <div class="row">
                    <div id="addressSelection">
                        <div class="row">
                            <div class="d-flex justify-content-center">
                                <div id="mainSpinner" class="spinner-border" style="display: block;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="paymentSection" style="display:none">
            </div>
            <div id="confirmSection" style="display: none">
                <div class="row">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary" onclick="nextButton('confirm')">Confirm</button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="button" class="btn btn-link" onclick="backButton('confirm')">Back</button>
                        <button type="button" class="btn btn-link" onclick="cancel()">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="offcanvas offcanvas-start" tabindex="-1" id="manageDataOffCanvas" aria-labelledby="offcanvasLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="offcanvasLabel"></h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <iframe id="manageIframe" name="manageIframe" src="" class="responsive-iframe" style="overflow: hidden; display: block; border:none; height:100vh; width:100%" ></iframe>
            </div>
        </div>
        <div id="authSection" style="display: none;">
            <div class="d-flex justify-content-center">
                <div id="authSpinner" class="spinner-border"></div>
            </div>
            <BR>
            <div id="authMessage" class="align-self-center">
                <div class="d-flex justify-content-center">
                    <div class="card">
                        <div class="card-body" style="width: 90vw; max-height: 999999px;">
                            <h5 class="card-title">Authorising</h5>
                            <p class="card-text small">We are authorizing your payment. Please be patient.  Please do not press BACK or REFRESH.</p>
                        </div>
                    </div>
                </div>
            </div>
            <iframe id="step_up_iframe" style="overflow: hidden; display: none; border:none; height:100vh; width:100%" name="stepUpIframe" ></iframe>
            <form id="step_up_form" name="stepup" method="POST" target="stepUpIframe" action="">
                <input id="step_up_form_jwt_input" type="hidden" name="JWT" value=""/>
                <input id="MD" type="hidden" name="MD" value="HELLO MUM. GET THE KETTLE ON"/>
            </form>
        </div>
        <div id="resultSection" style="display: none">
            <div id="resultText"></div>
            <div class="row">
                <div class="col-12">
                    <button type="button" class="btn btn-primary" onclick="window.open('index.php', '_parent')">Continue shopping</button>
                    <button type="button" id="retryButton" class="btn btn-secondary" onclick="window.location.reload(true)">Try again</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
</body>
<script src="https://flex.cybersource.com/cybersource/assets/microform/0.11/flex-microform.min.js"></script>
<script src="js/cardInput.js"></script>
<script src="js/utils.js"></script>
<script src="js/authorise.js"></script>
<script>
var oldPaymentInstrumentId;
// Order details Object. Store details submitted on index.php, for use in the various Steps.
let orderDetails = {
        referenceNumber: "<?php echo $_REQUEST['reference_number'];?>",
        orderId: "<?php echo $_REQUEST['orderId'];?>",
        amount: "<?php echo $_REQUEST['amount'];?>",
        currency: "<?php echo $_REQUEST['currency'];?>",
        local: <?php echo isset($_REQUEST['local']) && $_REQUEST['local'] === "true"?"true":"false";?>,
        shippingAddressRequired: true,
        useShippingAsBilling: true,
        customerId: "<?php echo isset($_REQUEST['customerToken'])?$_REQUEST['customerToken']:"";?>", // TODO
        paymentInstrumentId: "",
        shippingAddressId: "",
        flexToken: "",
        maskedPan: "",
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

document.addEventListener("DOMContentLoaded", function (e) {
    showAddresses();
    showCards();
});
function showCards(){
    $.ajax({
        type: "POST",
        url: "cardSelection.php",
        data: JSON.stringify({
            "customerId": orderDetails.customerId
        }),
        success: function (result) {
            document.getElementById('paymentSection').innerHTML = result;
            createCardInput("","payButton", false, false,"");
        }
    });
}
var myOffcanvas = document.getElementById('manageDataOffCanvas');
function showManageIframe(type){
    document.getElementById("offcanvasLabel").innerHTML = (type==="ADDRESS"?"My Addresses":"My Cards");
    var bsOffcanvas = new bootstrap.Offcanvas(myOffcanvas);
    var iframe = document.getElementById('manageIframe');
    if(type === "ADDRESS"){
        src = "/payPage/manageStoredAddresses.php?customerId=" + orderDetails.customerId;
    }else{
        src = "/payPage/manageStoredCards.php?customerId=" + orderDetails.customerId
            +"&reference_number="+orderDetails.referenceNumber+
            "&orderId="+orderDetails.orderId+
            "&email="+orderDetails.bill_to.email+
            "&currency="+ orderDetails.currency;
    }
    iframe.src = src;
    bsOffcanvas.show()
}
function onStoredDataUpdated(type, action){
    console.log("Stored data updated: "+type+" : "+action);
    if(type === "ADDRESS"){
        showAddresses();
    }else{
        showCards();
    }
}
function showAddresses(){
    $.ajax({
        type: "POST",
        url: "addressSelection.php",
        data: JSON.stringify({
            "customerId": orderDetails.customerId
        }),
        success: function (result) {
            document.getElementById('addressSelection').innerHTML = result;
        }
    });
}
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
function shipAsBill(){
    usb = document.querySelector('#useShipAsBill');
    if(usb){
        return usb.checked;
    }
    return false;
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
        case "pay":
            // Pay Button clicked
            if(orderDetails.useShippingAsBilling){
                getToken(onTokenCreated);
            }else{
                form = document.getElementById('billingForm');
                if(validateForm(form)){
                    getToken(onTokenCreated);
                }
            }
            break;
        case "confirm":
            // Confirm Button clicked
            document.getElementById("inputSection").style.display = "none";
            authorise();
            break;
    }
}
function backButton(form){
    switch(form){
        case "cardSelection":
            // Back to Address Selection or entry
            document.getElementById("paymentSection").style.display = "none";
            document.getElementById("addressSection").style.display = "block";
            document.getElementById("summaryEmailButton").style.display = "block";
            document.getElementById("summary_delivery").style.display = "none";
            document.getElementById("summary_billTo").style.display = "none";
            break;
        case "pay":
            // Existing Customer who has payment Instruments => back to Card selection 
            document.getElementById("paymentDetailsSection").style.display = "none";
            document.getElementById("cardSelectionSection").style.display = "block";
            document.getElementById("summary_billTo").style.display = "none";
            break;
        case "pay_new":
            // New customer with No Payment Instruments => Back to PAN entry
            document.getElementById("paymentSection").style.display = "none";
            document.getElementById("addressSection").style.display = "block";
            document.getElementById("summaryEmailButton").style.display = "block";
            document.getElementById("summary_delivery").style.display = "none";
            document.getElementById("summary_billTo").style.display = "none";
            break;
        case "confirm":
            document.getElementById("paymentDetailsSection").style.display = "block";
            document.getElementById("confirmSection").style.display = "none";
            break;
    }
}
function showNewAddress(){
    document.getElementById("newAddressSection").style.display = "block";
    document.getElementById("newAddressBackButton").style.display = "block";
    document.getElementById("addressButtonSection").style.display = "none";
}
function hideNewAddress(){
    document.getElementById("newAddressSection").style.display = "none";
    document.getElementById("newAddressBackButton").style.display = "none";
    document.getElementById("addressButtonSection").style.display = "block";
}
function useShippingAddress(id){
    console.log("Shipping Address: "+ id);
    if(id === "NEW"){
        orderDetails.shippingAddressId = "";
        form = document.getElementById('newAddressForm');
        if(validateForm(form)){
            setNewShippingDetails();
            document.getElementById("summary_delivery").style.display = "block";
            document.getElementById("paymentSection").style.display = "block";
            document.getElementById("addressSection").style.display = "none";
            document.getElementById("summaryEmailButton").style.display = "none";
            document.getElementById("cardSelectionSection").style.display = "block";
            document.getElementById('shipToText').innerHTML = formatNameAddress(orderDetails.ship_to);
        }
    }else{
        address=JSON.parse(document.getElementById("sa_"+id).value);
        setShippingDetails(address);
        orderDetails.shippingAddressId = id;
        document.getElementById("summary_delivery").style.display = "block";
        document.getElementById("cardSelectionSection").style.display = "block";
        document.getElementById("paymentSection").style.display = "block";
        document.getElementById("addressSection").style.display = "none";
        document.getElementById("summaryEmailButton").style.display = "none";
        document.getElementById('shipToText').innerHTML = formatNameAddress(address.shipTo);
    }
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
function setShippingDetails(address){
    orderDetails.ship_to.firstName = address.shipTo.firstName;
    orderDetails.ship_to.lastName = address.shipTo.lastName;
    orderDetails.ship_to.address1 = address.shipTo.address1;
    orderDetails.ship_to.address2 = address.shipTo.address2;
    orderDetails.ship_to.locality = address.shipTo.locality;
    orderDetails.ship_to.postalCode = address.shipTo.postalCode;
    orderDetails.ship_to.country = address.shipTo.country;
}
function setNewShippingDetails(){
    orderDetails.ship_to.firstName = document.getElementById('ship_to_firstName').value;
    orderDetails.ship_to.lastName = document.getElementById('ship_to_lastName').value;
    orderDetails.ship_to.address1 = document.getElementById('ship_to_address_line1').value;
    orderDetails.ship_to.address2 = document.getElementById('ship_to_address_line2').value;
    orderDetails.ship_to.locality = document.getElementById('ship_to_address_city').value;
    orderDetails.ship_to.postalCode = document.getElementById('ship_to_postcode').value;
    orderDetails.ship_to.country = document.getElementById('ship_to_address_country').value;
}
function usePaymentInstrument(id){
    console.log("Payment Instrument: "+ id);
    if(id === "NEW"){
        orderDetails.paymentInstrumentId = "";
        document.getElementById("storeCardCheck").style.display = "block";
        document.getElementById('billToText').innerHTML = "";
        flipCvvOnly(false);
    }else{
        orderDetails.paymentInstrumentId = id;
        document.getElementById('billingForm').style.display = "none";
        document.getElementById("storeCardCheck").style.display = "none";
        document.getElementById("useShipAsBill").checked = true;
        pi=JSON.parse(document.getElementById("pi_"+id).value);
        orderDetails.maskedPan = pi._embedded.instrumentIdentifier.card.number;
        document.getElementById('billToText').innerHTML = stylePaymentInstrument(pi.card,pi._embedded.instrumentIdentifier.card.number,pi.billTo);
        cardType = pi.card.type;
        flipCvvOnly(true, cardType);
        document.getElementById("summary_billTo").style.display = "block";
    }
    document.getElementById("cardSelectionSection").style.display = "none";
    document.getElementById("paymentDetailsSection").style.display = "block";
}
function onTokenCreated(tokenDetails){
    console.log(tokenDetails);
    // Hide card input, show Confirmation section
    document.getElementById("paymentDetailsSection").style.display = "none";
    document.getElementById("confirmSection").style.display = "block";

    orderDetails.flexToken = tokenDetails.flexToken;
    if(orderDetails.paymentInstrumentId === ""){
        orderDetails.maskedPan = tokenDetails.cardDetails.number;
        // If storeCard checked, we will create a Token
        sc = document.getElementById('storeCard');
        if (sc.checked) {
            orderDetails.storeCard = true;
        }
    }else{
        orderDetails.storeCard = false;
    }
    setBillingDetails();
    if(orderDetails.paymentInstrumentId === ""){
        document.getElementById("summary_billTo").style.display = "block";
        document.getElementById('billToText').innerHTML = 
            stylePaymentInstrument(tokenDetails.cardDetails, orderDetails.maskedPan, orderDetails.bill_to);
    }
}
function setBillingDetails() {
    if(orderDetails.useShippingAsBilling) {
        orderDetails.bill_to.firstName = orderDetails.ship_to.firstName;
        orderDetails.bill_to.lastName = orderDetails.ship_to.lastName;
        orderDetails.bill_to.address1 = orderDetails.ship_to.address1;
        orderDetails.bill_to.address2 = orderDetails.ship_to.address2;
        orderDetails.bill_to.locality = orderDetails.ship_to.locality;
        orderDetails.bill_to.postalCode = orderDetails.ship_to.postalCode;
        orderDetails.bill_to.country = orderDetails.ship_to.country;
    }else{
        orderDetails.bill_to.firstName = document.getElementById('bill_to_forename').value;
        orderDetails.bill_to.lastName = document.getElementById('bill_to_surname').value;
        orderDetails.bill_to.address1 = document.getElementById('bill_to_address_line1').value;
        orderDetails.bill_to.address2 = document.getElementById('bill_to_address_line2').value;
        orderDetails.bill_to.locality = document.getElementById('bill_to_address_city').value;
        orderDetails.bill_to.postalCode = document.getElementById('bill_to_postcode').value;
        orderDetails.bill_to.country = document.getElementById('bill_to_address_country').value;
    }
}
function authorise() {
    document.getElementById('authSection').style.display = "block";
    setUpPayerAuth();
}
function stylePaymentInstrument(card, number, billTo){
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
    html =  
            "<div class=\"row\">\n" +
                "<div class=\"col-3\">\n"+
                    "<img src=\"" + img + "\" class=\"img-fluid\" alt=\"" + alt + "\">"+
                "</div>\n" +
                "<div class=\"col-7 \">\n" +
                    "<ul class=\"list-unstyled\">" +
                        "<li><strong>" + number + "</strong></li>\n" +
                        "<li><small>Expires:&nbsp;" + card.expirationMonth + "/" + card.expirationYear + "</small></li>\n" +
                    "</ul>\n" +
                "</div>\n" +
            "</div>\n" +
            "<div class=\"row\">\n" +
                "<div class=\"col-12\">\n"+
                    "<h5>Billing Address</h5>" +
                "</div>\n" +
            "</div>" +
            "<div class=\"row\">\n" +
                "<div class=\"col-12 \">\n" +
                    formatNameAddress(billTo)+
                "</div>\n" +
            "</div>\n";
    return html;
}
</script>
