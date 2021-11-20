<?php
include_once 'card_types.php';
include_once 'countries.php';
include 'rest_get_customer.php';
$defaultEmail="";
if(isset($_REQUEST['email']) && !empty($_REQUEST['email'])) {
    $defaultEmail = $_REQUEST['email'];
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
        <!-- Modal -->
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
        <div id="progressSpinner2"  class="spinner-border text-info" style="display: block;"></div>
        <div id="iframeSection" style="display: none">
            <iframe id="shippingAddressIframe" name="shippingAddress_iframe" src="about:blank" class="responsive-iframe" style="overflow: hidden; display: block; border:none; height:100vh; width:100%" ></iframe>
        </div>
        <div class="row">
            <div class="col-sm-4">
                <div id="paymentDetailsSection">
                    <form id="emailForm" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-sm-12">
                                <h5>Email</h5>
                                <div class="form-group form-floating mb-3">
                                    <input id="bill_to_email" type="email" class="form-control form-control-sm" <?php if(!empty($defaultEmail)) echo "readonly";?> value="<?php echo $defaultEmail;?>" placeholder="Enter email" required>
                                    <label for="bill_to_email" class="form-label">Email*</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <button type="button" class="btn btn-primary" onclick="nextButton('email')">Next</button>
                                <button type="button" class="btn btn-secondary" onclick="cancel()">Cancel</button>
                            </div>
                        </div>
                    </form>
                    <form id="shippingForm" class="needs-validation" novalidate style="display: none">
                        <div id="shippingDetailsSection" class="form-group">
                            <h5>Delivery Address</h5>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group form-floating mb-3">
                                        <input id="ship_to_firstName" type="text" class="form-control form-control-sm" value="" placeholder="First name" maxlength="60" required>
                                        <label for="ship_to_firstName" class="form-label">First name*</label>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group form-floating mb-3">
                                        <input id="ship_to_lastName" type="text" class="form-control form-control-sm" value="" placeholder="Last Name" maxlength="60" required>
                                        <label for="ship_to_lastName" class="form-label">Surname*</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group form-floating mb-3">
                                        <input id="ship_to_address_line1" type="text" class="form-control form-control-sm" value="" placeholder="1st line of address" maxlength="60" required>
                                        <label for="ship_to_address_line1" class="form-label">Address line 1*</label>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group form-floating mb-3">
                                        <input id="ship_to_address_line2" type="text" class="form-control form-control-sm" value="" placeholder="2nd line of address" maxlength="60">
                                            <label for="ship_to_address_line2" class="form-label">Address line 2</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group form-floating mb-3">
                                        <input id="ship_to_address_city" type="text" class="form-control form-control-sm" value="" placeholder="City/County" maxlength="50" required>
                                        <label for="ship_to_address_city" class="form-label">City/County*</label>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group form-floating mb-3">
                                        <input id="ship_to_postcode" type="text" class="form-control form-control-sm" value="" placeholder="Postcode" maxlength="10" required>
                                        <label for="ship_to_postcode" class="form-label">PostCode*</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <div class="form-floating">
                                        <select id="ship_to_address_country" class="form-control form-control-sm">
        <?php
        foreach ($countries as $key => $value) {
            echo "<option value=\"". $key ."\">" . $value . "</option>\n";
        }
        ?>
                                        </select>
                                        <label for="ship_to_address_country" class="form-label">Country*</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <button type="button" class="btn btn-primary" onclick="nextButton('shipping')">Next</button>
                                <button type="button" class="btn btn-secondary" onclick="backButton('shipping')">Back</button>
                                <button type="button" class="btn btn-secondary" onclick="cancel()">Cancel</button>
                            </div>
                        </div>
                    </form>
                    <div id="cardSection" style="display: none">
                        <h5>Card Payment Details</h5>
                        <div id="cardInputSection">
                        </div>
                        <div id="billingAddressSection" style="display: block">
                            <div class="row">
                                <div class="col-sm-12">
                                    <input type="checkbox" class="form-check-input" onchange="useSameAddressChanged()" id="useShipAsBill" name="useShipAsBill" value="1" checked="checked">
                                    <label for="useShipAsBill" class="form-check-label">Use Delivery Address as Billing Address</label>
                                </div>
                            </div>                            
                            <div class="row">
                                <div class="col-sm-12">
                                    <input type="checkbox" class="form-check-input" id="storeCard" name="storeCard" value="1">
                                    <label for="storeCard" class="form-check-label">Store my details for future use</label>
                                </div>
                            </div>
                            <form id="billingForm" class="needs-validation" novalidate style="display: none">
                                <div id="billingSection">
                                    <h5>Card Billing Address</h5>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group form-floating mb-3">
                                                <input id="bill_to_forename" type="text" class="form-control form-control-sm" value="" placeholder="First name" maxlength="60" required>
                                                <label for="bill_to_forename" class="form-label">First name*</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group form-floating mb-3">
                                                <input id="bill_to_surname" type="text" class="form-control form-control-sm" value="" placeholder="Last Name" maxlength="60" required>
                                                <label for="bill_to_surname" class="form-label">Last name*</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group form-floating mb-3">
                                                <input id="bill_to_address_line1" type="text" class="form-control form-control-sm" value="" placeholder="1st line of address" maxlength="60" required>
                                                <label for="bill_to_address_line1" class="form-label">Address line 1*</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group form-floating mb-3">
                                                <input id="bill_to_address_line2" type="text" class="form-control form-control-sm" value="" placeholder="2nd line of address" maxlength="60">
                                                <label for="bill_to_address_line2" class="form-label">Address line 2</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group form-floating mb-3">
                                                <input id="bill_to_address_city" type="text" class="form-control form-control-sm" value="" placeholder="City/County" required maxlength="50">
                                                <label for="bill_to_address_city" class="form-label">City/County*</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group form-floating mb-3">
                                                <input id="bill_to_postcode" type="text" class="form-control form-control-sm" value="" placeholder="Postcode" required maxlength="10">
                                                <label for="bill_to_postcode" class="form-label">PostCode*</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="form-group form-floating mb-3">
                                                <select id="bill_to_address_country" class="form-control form-control-sm">
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
                            <div id="payButtonSection" class="row mt-3">
                                <div class="col-sm-12">
                                    <button type="button" id="payButton" onclick="nextButton('card')" class="btn btn-primary" disabled="true">Pay</button>
                                    <button type="button" onclick="backButton('card')" class="btn btn-secondary">Back</button>
                                    <button type="button" class="btn btn-secondary" onclick="cancel()">Cancel</button>
                                </div>
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
                        <div id="summary_email" class="col-sm-6">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <h5>Delivery Address:</h5>
                        </div>
                        <div id="shipToText" class="col-sm-6">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                        <h5>Billing Address:</h5>
                        </div>
                        <div class="col-sm-6" id="billToText">
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
<script src="js/utils.js"></script>
<script>
<?php
if (isset($_REQUEST['standAlone'])){echo "var standAlone = true;\n";}else{echo "var standAlone = false;\n";}
if (isset($_REQUEST['local']) && $_REQUEST['local'] === "true") {echo "var local = true;\n";}else{echo "var local = false;\n";}
?>
var customerId;
var paymentInstrumentId = "";
var oldPaymentInstrumentId;
var maskedPan = "";
var flexToken;
var shippingAddressId = "";
var storeCard = false;
// Order details Object. Store details submitted on index.php, for use in the various Steps.
let orderDetails = {
    referenceNumber: <?php echo '"' . $_REQUEST['reference_number'] . '"'; ?>,
    amount: <?php echo '"' . $_REQUEST['amount'] . '"'; ?>,
    currency: <?php echo '"' . $_REQUEST['currency'] . '"'; ?>,
    shippingAddressRequired: true,
    useShippingAsBilling: true,
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
var form;
var myModal = new bootstrap.Modal(document.getElementById('staticBackdrop'), {keyboard: false});
document.addEventListener("DOMContentLoaded", function (e) {
    createCardInput("cardInputSection", "progressSpinner2", "payButton");
});
function payNow(){
}
function onTokenCreated(tokenDetails){
    console.log(tokenDetails);
    flexToken = tokenDetails.flexToken;
    maskedPan = tokenDetails.cardDetails.number;
    // IF storeCard checked, we will create a Token
    sc = document.getElementById('storeCard');
    if (sc.checked) {
        storeCard = true;
    }
//    setOrderDetails()
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
function useSameAddressChanged() {
    orderDetails.useShippingAsBilling = shipAsBill();
    if (orderDetails.useShippingAsBilling) {
        // Hide Billing fields
        document.getElementById('billingForm').style.display = "none";
    }else{
        document.getElementById('billingForm').style.display = "block";
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
            "storeCard": storeCard,
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
    myModal.show();
    // console.log( "Challenge Screen:\n"+stepUpURL);
    document.getElementById('step_up_form').action = stepUpURL;
    document.getElementById('step_up_form_jwt_input').value = jwt;
    var stepUpForm = document.getElementById('step_up_form');
    if (stepUpForm){
        stepUpForm.submit();
    }
}
function hideStepUpScreen(transactionId) {
    myModal.hide();
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
function nextButton(form){
    switch(form){
        case "email":
            form = document.getElementById('emailForm');
            if(validateForm(form)){
                document.getElementById("emailForm").style.display = "none";
                document.getElementById("shippingForm").style.display = "block";
                document.getElementById("summary_email").innerHTML = document.getElementById("bill_to_email").value;
                orderDetails.bill_to.email = document.getElementById('bill_to_email').value;
            }
            break;
        case "shipping":
            form = document.getElementById('shippingForm');
            if(validateForm(form)){
                document.getElementById("shippingForm").style.display = "none";
                document.getElementById("cardSection").style.display = "block";
                setShippingDetails();
            }
            break;
        case "card":
            // Pay Button clicked
            document.getElementById("cardSection").style.display = "none";
            document.getElementById("resultSection").style.display = "block";
            if(shipAsBill()){
                document.getElementById("billToText").innerHTML = formatNameAddress(orderDetails.ship_to);
            }else{
                setBillingDetails();
            }
            getToken(onTokenCreated, onTokenError);
            break;
    }
}
function backButton(form){
    switch(form){
        case "shipping":
            document.getElementById("shippingForm").style.display = "none";
            document.getElementById("emailForm").style.display = "block";
            break;
        case "card":
            document.getElementById("cardSection").style.display = "none";
            document.getElementById("shippingForm").style.display = "block";
            break;
    }
}
function setShippingDetails(){
    orderDetails.ship_to.firstName = document.getElementById('ship_to_firstName').value;
    orderDetails.ship_to.lastName = document.getElementById('ship_to_lastName').value;
    orderDetails.ship_to.address1 = document.getElementById('ship_to_address_line1').value;
    orderDetails.ship_to.address2 = document.getElementById('ship_to_address_line2').value;
    orderDetails.ship_to.locality = document.getElementById('ship_to_address_city').value;
    orderDetails.ship_to.postalCode = document.getElementById('ship_to_postcode').value;
    orderDetails.ship_to.country = document.getElementById('ship_to_address_country').value;
    document.getElementById("shipToText").innerHTML = formatNameAddress(orderDetails.ship_to);
}
function setBillingDetails() {
    orderDetails.bill_to.firstName = document.getElementById('bill_to_forename').value;
    orderDetails.bill_to.lastName = document.getElementById('bill_to_surname').value;
    orderDetails.bill_to.address1 = document.getElementById('bill_to_address_line1').value;
    orderDetails.bill_to.address2 = document.getElementById('bill_to_address_line2').value;
    orderDetails.bill_to.locality = document.getElementById('bill_to_address_city').value;
    orderDetails.bill_to.postalCode = document.getElementById('bill_to_postcode').value;
    orderDetails.bill_to.country = document.getElementById('bill_to_address_country').value;
    document.getElementById("billToText").innerHTML = formatNameAddress(orderDetails.bill_to);
}
</script>
