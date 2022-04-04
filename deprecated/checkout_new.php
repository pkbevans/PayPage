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
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
        <div id="paymentDetailsSection">
            <form id="emailForm" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-12">
                        <h1 class="display-5">Please enter your email address</h1>
                        <div class="form-group form-floating mb-3">
                            <input id="bill_to_email" type="email" class="form-control form-control-sm" value="<?php echo $defaultEmail;?>" placeholder="Enter email" required>
                            <label for="bill_to_email" class="form-label">Email</label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="button" class="btn btn-primary" onclick="nextButton('email')">Next</button>
                        <button type="button" class="btn btn-secondary" onclick="cancel()">Cancel</button>
                    </div>
                </div>
            </form>
            <form id="shippingForm" class="needs-validation" novalidate style="display: none">
                <div class="row">
                    <div class="col-12"><h1 class="display-5">Please enter delivery address</h1></div>
                </div>
                <div id="shippingDetailsSection" class="form-group">
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group form-floating mb-3">
                                <input id="ship_to_firstName" type="text" class="form-control form-control-sm" value="" placeholder="First name" maxlength="60" required>
                                <label for="ship_to_firstName" class="form-label">First name*</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group form-floating mb-3">
                                <input id="ship_to_lastName" type="text" class="form-control form-control-sm" value="" placeholder="Last Name" maxlength="60" required>
                                <label for="ship_to_lastName" class="form-label">Surname*</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group form-floating mb-3">
                                <input id="ship_to_address_line1" type="text" class="form-control form-control-sm" value="" placeholder="1st line of address" maxlength="60" required>
                                <label for="ship_to_address_line1" class="form-label">Address line 1*</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group form-floating mb-3">
                                <input id="ship_to_address_line2" type="text" class="form-control form-control-sm" value="" placeholder="2nd line of address" maxlength="60">
                                    <label for="ship_to_address_line2" class="form-label">Address line 2</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group form-floating mb-3">
                                <input id="ship_to_address_city" type="text" class="form-control form-control-sm" value="" placeholder="City/County" maxlength="50" required>
                                <label for="ship_to_address_city" class="form-label">City/County*</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group form-floating mb-3">
                                <input id="ship_to_postcode" type="text" class="form-control form-control-sm" value="" placeholder="Postcode" maxlength="10" required>
                                <label for="ship_to_postcode" class="form-label">PostCode*</label>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
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
                    <div class="col-12">
                        <button type="button" class="btn btn-primary" onclick="nextButton('shipping')">Next</button>
                        <button type="button" class="btn btn-secondary" onclick="backButton('shipping')">Back</button>
                        <button type="button" class="btn btn-secondary" onclick="cancel()">Cancel</button>
                    </div>
                </div>
                <a href="checkout_existing.php"></a>
            </form>
            <div id="cardForm" style="display: none">
                <div class="col-12"><h1 class="display-5">Payment card details</h1></div>
                <div id="cardInputSection"></div>
                <div id="billingAddressSection" style="display: block">
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
                    <form id="billingForm" class="needs-validation" novalidate style="display: none">
                        <div id="billingSection">
                            <h5>Card Billing Address</h5>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group form-floating mb-3">
                                        <input id="bill_to_forename" type="text" class="form-control form-control-sm" value="" tabindex="1" placeholder="First name" maxlength="60" required>
                                        <label for="bill_to_forename" class="form-label">First name*</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group form-floating mb-3">
                                        <input id="bill_to_surname" type="text" class="form-control form-control-sm" value="" tabindex="2" placeholder="Last Name" maxlength="60" required>
                                        <label for="bill_to_surname" class="form-label">Last name*</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group form-floating mb-3">
                                        <input id="bill_to_address_line1" type="text" class="form-control form-control-sm" value="" tabindex="3" placeholder="1st line of address" maxlength="60" required>
                                        <label for="bill_to_address_line1" class="form-label">Address line 1*</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group form-floating mb-3">
                                        <input id="bill_to_address_line2" type="text" class="form-control form-control-sm" value="" tabindex="4" placeholder="2nd line of address" maxlength="60">
                                        <label for="bill_to_address_line2" class="form-label">Address line 2</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group form-floating mb-3">
                                        <input id="bill_to_address_city" type="text" class="form-control form-control-sm" value="" tabindex="6" placeholder="City/County" required maxlength="50">
                                        <label for="bill_to_address_city" class="form-label">City/County*</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group form-floating mb-3">
                                        <input id="bill_to_postcode" type="text" class="form-control form-control-sm" value="" tabindex="5" placeholder="Postcode" required maxlength="10">
                                        <label for="bill_to_postcode" class="form-label">PostCode*</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
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
                    <div id="payButtonSection" class="row mt-3">
                        <div class="col-12">
                            <button type="button" id="payButton" onclick="nextButton('card')" class="btn btn-primary" tabindex="8" disabled="true">Next</button>
                            <button type="button" onclick="backButton('card')" class="btn btn-secondary">Back</button>
                            <button type="button" class="btn btn-secondary" onclick="cancel()">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
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
            <div id="summary_email" style="display: none">
                <div class="row">
                    <div class="col-3"><h5>Email:</h5></div>
                    <div class="col-9" id="emailText" style="display: none"></div>
                </div>
            </div>
            <div id="summary_delivery" style="display: none">
                <div class="row">
                    <div class="col-3"><h5>Delivery:</h5></div>
                    <div class="col-9" id="shipToText"></div>
                </div>
            </div>
            <div id="summary_card" style="display: none">
                <h5>Card:</h5>
                <div class="row">
                    <div id="cardImage" class="col-3">
                    </div>
                    <div class="col-9">
                        <ul class="list-unstyled">
                            <li><strong><span id="cardNumber"></span></strong></li>
                            <li><small>Expires:&nbsp;<span id="summaryExpDate"></span></small></li>
                        </ul>
                    </div>
                </div>
                <div class="row">
                    <div class="col-3"><h5>Card Billing:</h5></div>
                    <div class="col-9" id="billToText"></div>
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
                    <button type="button" id="retryButton" class="btn btn-secondary" onclick="retryAfterDecline()">Try again</button>
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
// Order details Object. Store details submitted on index.php, for use in the various Steps.
let orderDetails = {
        referenceNumber: <?php echo '"' . $_REQUEST['reference_number'] . '"'; ?>,
        orderId: "<?php echo $_REQUEST['orderId'];?>",
        amount: <?php echo '"' . $_REQUEST['amount'] . '"'; ?>,
        currency: <?php echo '"' . $_REQUEST['currency'] . '"'; ?>,
        local: <?php echo isset($_REQUEST['local']) && $_REQUEST['local'] === "true"?"true":"false";?>,
        shippingAddressRequired: true,
        useShippingAsBilling: true,
        customerId: "",
        paymentInstrumentId: "",
        maskedPan: "",
        flexToken: "",
        shippingAddressId: "",
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
    createCardInput("cardInputSection", "mainSpinner", "payButton");
});
function onTokenCreated(tokenDetails){
    console.log(tokenDetails);
    if(!orderDetails.useShippingAsBilling) {
        setBillingDetails();
    }
    orderDetails.flexToken = tokenDetails.flexToken;
    orderDetails.maskedPan = tokenDetails.cardDetails.number;
    document.getElementById("cardForm").style.display = "none";
    document.getElementById("cardNumber").innerHTML = orderDetails.maskedPan;
    document.getElementById("summaryExpDate").innerHTML = tokenDetails.cardDetails.expirationMonth+"/"+tokenDetails.cardDetails.expirationYear;
    if(tokenDetails.cardDetails.type === "001"){
        img = "images/Visa.svg";
        alt = "Visa card logo";
    }else if(tokenDetails.cardDetails.type === "002"){
        img = "images/Mastercard.svg";
        alt = "Mastercard logo";
    }else{
        img = "images/Amex.svg";
        alt = "Amex card logo";
    }
    document.getElementById("cardImage").innerHTML = 
       "<img src=\"" + img + "\" class=\"img-fluid\" alt=\"" + alt + "\">";
    document.getElementById("summary_card").style.display = "block";
    document.getElementById("confirmSection").style.display = "block";
    // IF storeCard checked, we will create a Token
    sc = document.getElementById('storeCard');
    if (sc.checked) {
        orderDetails.storeCard = true;
    }
}
function cancel() {
    window.open('index.php', '_parent');
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
        document.getElementById("billToText").innerHTML = formatNameAddress(orderDetails.ship_to);
    }else{
        setBillingDetails();
        document.getElementById('billingForm').style.display = "block";
    }
}
function authorise() {
    document.getElementById('authSection').style.display = "block";
    setUpPayerAuth();
}
function onIframeCancelled(){
    document.getElementById('iframeSection').style.display = "none";
    document.getElementById('paymentDetailsSection').style.display = "block";
}
function nextButton(form){
    switch(form){
        case "email":
            form = document.getElementById('emailForm');
            if(validateForm(form)){
                document.getElementById("emailForm").style.display = "none";
                document.getElementById("summary_email").style.display = "block";
                document.getElementById("shippingForm").style.display = "block";
                showSummaryItem("emailText", document.getElementById('bill_to_email').value );
                orderDetails.bill_to.email = document.getElementById('bill_to_email').value;
            }
            break;
        case "shipping":
            form = document.getElementById('shippingForm');
            if(validateForm(form)){
                document.getElementById("summary_delivery").style.display = "block";
                document.getElementById("shippingForm").style.display = "none";
                document.getElementById("cardForm").style.display = "block";
                setShippingDetails();
            }
            break;
        case "card":
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
function showSummaryItem(id, value){
    element = document.getElementById(id);
    if(element){
        element.style.display = 'block';
        element.innerHTML = value;
    }
}
function backButton(form){
    switch(form){
        case "shipping":
            document.getElementById("shippingForm").style.display = "none";
            document.getElementById("summary_email").style.display = "none";
            document.getElementById("emailForm").style.display = "block";
            break;
        case "card":
            document.getElementById("summary_delivery").style.display = "none";
            document.getElementById("summary_card").style.display = "none";
            document.getElementById("cardForm").style.display = "none";
            document.getElementById("shippingForm").style.display = "block";
            break;
        case "confirm":
            document.getElementById("summary_card").style.display = "none";
            document.getElementById("confirmSection").style.display = "none";
            document.getElementById("cardForm").style.display = "block";
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
    if(shipAsBill()){
        document.getElementById("billToText").innerHTML = formatNameAddress(orderDetails.ship_to);
    }
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
function retryAfterDecline(){
    console.log("Retry");
    document.getElementById('summarySection').style.display = "block";
    document.getElementById("resultSection").style.display = "none";
    backButton("confirm");
}
</script>