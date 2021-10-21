<?php
include_once 'card_types.php';
include_once 'countries.php';
include 'generate_capture_context.php';
include 'rest_get_customer.php';
$defaultEmail="";
if(isset($_REQUEST['email']) && !empty($_REQUEST['email'])) {
    $defaultEmail = $_REQUEST['email'];
}
$defaultEmail = $defaultPaymentInstrument->billTo->email;
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
        <input id="email" type="hidden" name="email" value="null@cybersource.com">
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
        <div id="progressSpinner2"  class="spinner-border text-info" style="display: block;"></div>
        <div class="row">
            <div class="col-sm-4">
                <div id="overlay">
                    <iframe id="step_up_iframe" style="overflow: hidden; display: block; border:none; height:100vh; width:100%" name="stepUpIframe" ></iframe>
                </div>
                <div id="iframeSection" style="display: none">
                    <iframe id="shippingAddressIframe" name="shippingAddress_iframe" src="about:blank" class="responsive-iframe" style="overflow: hidden; display: block; border:none; height:100vh; width:100%" ></iframe>
                </div>
                <div id="paymentDetailsSection">
                    <input id="bill_to_email" type="hidden" value="<?php echo $defaultEmail;?>">
                    <div id="storedCardSection">
                        <h5>Stored Card Details</h5>
                        <div class="row">
                            <div class="col-sm-2">
                                <img id="storedCardImg" src="images/<?php echo $cardTypes[$defaultPaymentInstrument->card->type]['image'];?>" class="img-fluid" alt="<?php echo $cardTypes[$defaultPaymentInstrument->card->type]['alt'];?>">
                            </div>
                            <div class="col-sm-8">
                                <ul class="list-unstyled">
                                    <li><strong><?php echo $defaultPaymentInstrument->_embedded->instrumentIdentifier->card->number;?></strong></li>
                                    <li><small>Expires:&nbsp;<?php echo $defaultPaymentInstrument->card->expirationMonth . "/" . $defaultPaymentInstrument->card->expirationYear;?></small></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div id="cardSection" style="display: block">
                        <form id="cardForm">
                            <div id="cardInputSection">
                            </div>
                        </form>
                        <div id="billingAddressSection" style="display: block">
                            <div id="defaultBillingSection">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <button type="button" class="btn btn-link" onclick="editCard()">Use a different Card</button>
                                    </div>
                                </div>
                            </div>
                            <div id="payButtonSection" class="row">
                                <div class="col-sm-12">
                                    <button type="button" id="payButton" onclick="payNow()" class="btn btn-primary" disabled="true">Pay</button>
                                    <button type="button" class="btn btn-secondary" onclick="cancel()">Cancel</button>
                                </div>
                            </div>
                            <div class="row">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card card-body" id="resultSection" style="display: none">
                    <div class="row">
                        <div class="col-12 d-flex justify-content-center">
                            <div id="progressSpinner" class="spinner-border text-info"></div>
                        </div>
                    </div>
                    <h5 class="card-title">Result</h5>
                    <p id="result" class="card-text"></p>
                    <button type="button" id="newPaymentButton" class="btn btn-primary" onclick="window.location.href='index.php'" style="display: none">New Payment</button>
                    <button type="button" id="retryButton" class="btn btn-primary" onclick="window.location.reload(true)" style="display: none">Try again</button>
                </div>
            </div>
            <div class="col-sm-4">
                <div id="summarySection">
                    <div class="row">
                        <h3>Your Order</h3>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <h5>Your email:</h5>
                        </div>
                        <div class="col-sm-6">
                            <?php echo $defaultEmail;?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <h5>Delivery Address:</h5>
                        </div>
                        <div id="shipToText" class="col-sm-6">
                            <?php echo $shipToText;?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                        </div>
                        <div id="summaryEditAddress" class="col-sm-6">
                            <button type="button" class="btn btn-link" onclick="editShippingAddress()">Edit</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                        <h5>Billing Address:</h5>
                        </div>
                        <div class="col-sm-6" id="billToText">
                        <?php echo $billToText;?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                        </div>
                        <div id="summaryEditCard" class="col-sm-6">
                        <button type="button" class="btn btn-link" onclick="editCard()">Edit</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <span><h5>Total:</h5></span>
                        </div>
                        <div class="col-sm-6">
                            <span><?php echo "£" . $_REQUEST['amount'];?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
</body>
<script src="https://flex.cybersource.com/cybersource/assets/microform/0.11/flex-microform.min.js"></script>
<script src="js/newCard2.js"></script>
<script>
// the capture context that was requested server-side for this transaction
var captureContext = "<?php echo $captureContext ?>";
<?php
if (isset($_REQUEST['standAlone'])){echo "var standAlone = true;\n";}else{echo "var standAlone = false;\n";}
if (isset($_REQUEST['local']) && $_REQUEST['local'] === "true") {echo "var local = true;\n";}else{echo "var local = false;\n";}
echo "var customerId ='".$customerToken."'" . ";\n";
?>
var paymentInstrumentId = "<?php echo $defaultPaymentInstrument->id;?>";
var oldPaymentInstrumentId;
var maskedPan = "<?php echo $defaultPaymentInstrument->_embedded->instrumentIdentifier->card->number;?>";
var shippingAddressId = "<?php echo $defaultShippingAddress->id;?>";
var flexToken="";
var pan;
// Order details Object. Store details submitted on index.php, for use in the various Steps.
let orderDetails = {
    referenceNumber: <?php echo '"' . $_REQUEST['reference_number'] . '"'; ?>,
    amount: <?php echo '"' . $_REQUEST['amount'] . '"'; ?>,
    currency: <?php echo '"' . $_REQUEST['currency'] . '"'; ?>,
    shippingAddressRequired: true,
    useShippingAsBilling: false,
    ship_to: {
        firstname: "",
        lastname: "",
        address1: "",
        address2: "",
        locality: "",
        postalCode: "",
        country: ""
    },
    bill_to: {
        firstname: "",
        lastname: "",
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
document.addEventListener("DOMContentLoaded", function (e) {
    createCardInput("cardInputSection", "progressSpinner2", "payButton", true);
});
function payNow(){
    document.getElementById("paymentDetailsSection").style.display = "none";
    document.getElementById("summaryEditAddress").style.display = "none";
    document.getElementById("summaryEditCard").style.display = "none";
    document.getElementById("resultSection").style.display = "block";

    if(flexToken ==""){
        getToken(onTokenCreated, onTokenError);
    }else{
        setUpPayerAuth();
    }
}
function onTokenCreated(tokenDetails){
    console.log(tokenDetails);
    flexToken = tokenDetails.flexToken;
    setUpPayerAuth();
}
function onTokenError(err){
    console.log("Token Creation Error:");
    console.log(err);
    onFinish(status, "", false, false, "n/a", err.reason, err.Message)
}
function cancel() {
    onFinish("CANCELLED", 0, false, false, "n/a", "User Cancelled", "");
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
    if(shippingAddressId === ""){
        orderDetails.useShippingAsBilling = shipAsBill();

        orderDetails.ship_to.firstname = document.getElementById('ship_to_firstname').value;
        orderDetails.ship_to.lastname = document.getElementById('ship_to_lastname').value;
        orderDetails.ship_to.address1 = document.getElementById('ship_to_address1').value;
        orderDetails.ship_to.address2 = document.getElementById('ship_to_address2').value;
        orderDetails.ship_to.locality = document.getElementById('ship_to_city').value;
        orderDetails.ship_to.postalCode = document.getElementById('ship_to_postalCode').value;
        orderDetails.ship_to.country = document.getElementById('ship_to_country').value;
    }
    if(paymentInstrumentId === ""){
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
function setUpPayerAuth() {
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
            let httpCode = res.responseCode;
            if (httpCode === 201) {
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
            "storeCard": false,
            "customerId": customerId,
            "paAction": paAction,
            "paymentInstrumentId": paymentInstrumentId,
            "shippingAddressId": shippingAddressId,
            "transientToken": flexToken,
            "referenceID": dfReferenceId,
            "authenticationTransactionID": authenticationTransactionID,
            "standAlone": standAlone,
            "capture": true     // TODO hardcoded capture
        }),
        success: function (result) {
            // Response is a json string - turn it into a javascript object
            let res = JSON.parse(result);
            console.log("\nEnrollment:\n" + JSON.stringify(res, undefined, 2));
            let httpCode = res.responseCode;
            let status = res.response.status;
            if (httpCode === 201) {
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
                        if(shippingAddressId === ""){
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
    document.getElementById("overlay").style.display = "block";
    // console.log( "Challenge Screen:\n"+stepUpURL);
    document.getElementById('step_up_form').action = stepUpURL;
    document.getElementById('step_up_form_jwt_input').value = jwt;
    var stepUpForm = document.getElementById('step_up_form');
    if (stepUpForm){
        stepUpForm.submit();
    }
}
function hideStepUpScreen(transactionId) {
    document.getElementById("overlay").style.display = "none";
    console.log("Challenge Complete TransactionId:\n" + transactionId);
    authorizeWithPA("", transactionId, "VALIDATE_CONSUMER_AUTHENTICATION");
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
    document.getElementById('shipToText').innerHTML = shipToText;
    document.getElementById('iframeSection').style.display = "none";
    document.getElementById('paymentDetailsSection').style.display = "block";
}
function onNewCardUsed(flexDetails, billToDetails) {
    console.log(flexDetails);
    console.log(billToDetails);
    // Hide/unload Security code
    document.getElementById('cardInputSection').style.display = "none";
    securityCode.unload();
    maskedPan = flexDetails.cardDetails.number;
    flexToken = flexDetails.flexToken;
    paymentInstrumentId = "";

    html = stylePaymentInstrument(maskedPan, flexDetails.cardDetails, billToDetails);
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
    paymentInstrumentId = id;
    maskedPan = paymentInstrument._embedded.instrumentIdentifier.card.number;
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
    if (card.type === "001") {
        img = "images/Visa.svg";
        alt = "Visa card logo";
    } else if (card.type === "002") {
        img = "images/Mastercard.svg";
        alt = "Mastercard logo";
    } else {
        img = "images/Amex.svg";
        alt = "Amex card logo";
    }
    html =  "<h5>Your Card Details</h5>" +
            "<div class=\"row\">\n" +
                "<div class=\"col-sm-2\">\n"+
                    "<img src=\"" + img + "\" class=\"img-fluid\" alt=\"" + alt + "\">"+
                "</div>\n" +
                "<div class=\"col-sm-8\">\n" +
                    "<ul class=\"list-unstyled\">" +
                        "<li><strong>" + maskedPan + "</strong></li>\n" +
                        "<li><small>Expires:&nbsp;" + card.expirationMonth + "/" + card.expirationYear + "</small></li>\n" +
                    "</ul>\n" +
                "</div>\n" +
            "</div>\n";
    return html;
}
function formatNameAddress(nameAddress){
    // return name and address string
    if(!nameAddress.hasOwnProperty("address2")){
        nameAddress.address2 = "";
    }
    return xtrim(nameAddress.firstName, " ") +
            xtrim(nameAddress.lastName, "<br>") +
            xtrim(nameAddress.address1, ",<br>") +
            xtrim(nameAddress.address2, ",<br>") +
            xtrim(nameAddress.locality, ",<br>") +
            xtrim(nameAddress.postalCode, ",<br>") +
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
    document.getElementById('paymentDetailsSection').style.display = "none";
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
        text = "Oh dear. Your payment was not successful.  You can try again or try a different payment method" + "<BR>" + finish;
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
