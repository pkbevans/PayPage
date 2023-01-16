<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/checkout/utils/cards.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/checkout/utils/countries.php';
$defaultEmail="";
if(isset($_REQUEST['email']) && !empty($_REQUEST['email'])) {
    $defaultEmail = $_REQUEST['email'];
}else if(isset($_REQUEST['customerToken']) && !empty($_REQUEST['customerToken'])) {
    include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/checkout/api/getDefaultEmail.php';
    $defaultEmail = getDefaultEmail($_REQUEST['customerToken']);
}
?>
<!doctype html>
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="/payPage/common/css/styles.css"/>
    <title>Payment Page</title>
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
        <div id="inputSection" style="display: none">
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
                                <div id="emailText"><?php echo $defaultEmail;?></div>
                                <div id="emailSection" style="display:<?php echo ($defaultEmail == ''?'block':'none');?>">
                                    <form id="emailForm" class="needs-validation" novalidate>
                                        <div class="row">
                                            <div class="form-group mb-3">
                                                <input id="bill_to_email" type="email" class="form-control" value="<?php echo $defaultEmail;?>" placeholder="Please enter your email" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <button id="updateEmailButton" type="button" class="btn btn-primary" onclick="nextButton('email')">Next</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-3"></div>
                                <div class="col-2">
                                    <button id="editEmailButton" type="button" class="btn btn-link p-0" onclick="editEmail()" style="display:<?php echo ($defaultEmail == ''?'none':'block');?>">Edit</button>
                                </div>
                            </div>
                        </div>
                        <div id="summary_delivery" style="display:none">
                            <hr class="solid">
                            <h5 class="card-title">Delivery Address</h5>
                            <div id="shipToText" class="card-text small" style="max-height: 999999px;"></div>
                            <div class="row" id="storeAddressSection" style="display:none">
                                <div class="col-12">
                                    <input type="checkbox" class="form-check-input" id="storeAddress" name="storeAddress" value="1">
                                    <label for="storeAddress" class="form-check-label">Store this address for future use</label>
                                </div>
                            </div>
                        </div>
                        <div id="summary_billTo" style="display:none">
                            <hr class="solid">
                            <h5 class="card-title">Payment Card</h5>
                            <p id="billToText" class="card-text small" style="max-height: 999999px;"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div id="addressSection" style="display:<?php echo ($defaultEmail == ''?'none':'block');?>">
                <BR>
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
                <div class="row" id="storeCardSection" style="display:none">
                    <div class="col-12">
                        <input type="checkbox" class="form-check-input" id="storeCard" name="storeCard" onchange="storeCardChanged()" value="1">
                        <label for="storeCard" class="form-check-label">Store these details for future use</label>
                    </div>
                </div>                
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
            <div id="createAccountSection" style="display: none">
                <form class="needs-validation" id="registerUserForm" name="" method="" target="" action="" novalidate >
                    <div class="form-group">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group form-floating mb-3">
                                    <input id="firstName" class="form-control" autocomplete="given-name" type="text" name="firstName" value="" required/>
                                    <label for="firstName" class="form-label">First name</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group form-floating mb-3">
                                    <input id="lastName" class="form-control" autocomplete="family-name" type="text" name="lastName" value="" required/>
                                    <label for="lastName" class="form-label">Last name</label>
                                </div>
                            </div>
                        </div>                    
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group form-floating mb-3">
                                    <input id="customerUserName" class="form-control" autocomplete="username" type="text" name="customerUserName" value="" required/>
                                    <label for="customerUserName" class="form-label">Username</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group form-floating mb-3">
                                <input id="customerPassword" class="form-control" type="password" name="customerPassword" value="" required/>
                                    <label for="customerPassword" class="form-label">Password</label>
                                </div>
                            </div>
                        </div>                    
                    </div>
                    <div class="col-12">
                        <button type="button" class="btn btn-primary" onclick="registerUser()">Create Account</button>
                        <button type="button" class="btn btn-link" onclick="cancelRegisterUser()">Cancel</button>
                    </div>
                </form>
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
                    <button type="button" class="btn btn-primary" onclick="window.open('/payPage/index.php', '_parent')">Continue shopping</button>
                    <button type="button" id="retryButton" class="btn btn-secondary" onclick="retry();">Try again</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
</body>
<script src="https://flex.cybersource.com/cybersource/assets/microform/0.11/flex-microform.min.js"></script>
<script src="../js/cardInput.js"></script>
<script src="../js/authorise.js"></script>
<script src="../../common/js/authenticate.js"></script>
<script src="../js/utils.js"></script>
<script src="../js/googlePay.js"></script>
<script async src="https://pay.google.com/gp/p/js/pay.js"></script>


<script>
// Order details Object. Store details submitted on index.php, for use in the various Steps.
let orderDetails = {
    referenceNumber: "<?php echo $_REQUEST['reference_number'];?>",
    orderId: "<?php echo $_REQUEST['orderId'];?>",
    amount: "<?php echo $_REQUEST['amount'];?>",
    currency: "<?php echo $_REQUEST['currency'];?>",
    shippingAddressRequired: true,
    useShippingAsBilling: true,
    customerUserId: <?php echo isset($_REQUEST['customerUserId'])?$_REQUEST['customerUserId']:0;?>,
    customerId: "<?php echo isset($_REQUEST['customerToken'])?$_REQUEST['customerToken']:"";?>",
    paymentInstrumentId: "",
    shippingAddressId: "",
    flexToken: "",
    googlePayToken: "",
    maskedPan: "",
    storeCard: false,
    storeAddress: false,
    buyNow: <?php echo isset($_REQUEST['buyNow']) && $_REQUEST['buyNow'] === "true"?"true":"false";?>,
    capture: <?php echo isset($_REQUEST['autoCapture']) && $_REQUEST['autoCapture'] === "true"?"true":"false";?>,
    ship_to: {
        firstName: "",
        lastName: "",
        address1: "",
        address2: "",
        locality: "",
        administrativeArea: "",
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
        administrativeArea: "",
        postalCode: "",
        country: ""
    }
 };

document.addEventListener("DOMContentLoaded", function (e) {
    start();
});
function start(){
    if(orderDetails.buyNow){
        getCustomer(orderDetails.customerId)
        .then (pan=>{
            orderDetails.maskedPan = pan;
            authorise()
        })
        .catch(error => {
            window.alert("Sorry. Something went wrong: " + error);
            console.log("ERROR: " + error)
        })
    }else{
        showAddresses();
        showCards();
        document.getElementById("inputSection").style.display = "block";
    }
}
function getCustomer(id){
    console.log("geCustomer: "+id);
    return fetch("/payPage/checkout/api/getCustomer.php", {
      method: "post",
      body: JSON.stringify({
          "customerId" : id
      })
    })
    .then(response =>{
        if (!response.ok) {
            throw Error(response.statusText);
        }
        return response.json();
    })
    .then(res => {
        try{
            // Need to check that customer has a saved card
            return res._embedded.defaultPaymentInstrument._embedded.instrumentIdentifier.card.number;
        }catch(err){
            throw "No saved cards for customerId: "+id;
        }
    })
    .catch(error =>{
        throw "Can't get card for customer: " + id;
    })
}
function retry(){
    // Authorization failed so give user chance to try again or use a
    // different card
    console.log("retry");
    if(orderDetails.buyNow){
        orderDetails.buyNow = false;
        start();
    }else{
        document.getElementById("resultSection").style.display = "none";
        document.getElementById("inputSection").style.display = "block";
        backButton("confirm");
    }
}
//Disable Back Button
window.history.pushState(null, null, window.location.href);
window.onpopstate = function () {
    window.history.go(1);
};
function showCards(){
    return fetch("/payPage/checkout/api/cardSelection.php", {
      method: "post",
      body: JSON.stringify({
        "customerId": orderDetails.customerId
      })
    })
    .then((result) => result.text())
    .then(result =>{
        document.getElementById('paymentSection').innerHTML = result;
        onGooglePayLoaded();
    })
    .catch((error) => console.log("showCards ERROR:"+error))
}
var myOffcanvas = document.getElementById('manageDataOffCanvas');
function showManageIframe(type){
    document.getElementById("offcanvasLabel").innerHTML = (type==="ADDRESS"?"My Addresses":"My Cards");
    var bsOffcanvas = new bootstrap.Offcanvas(myOffcanvas);
    var iframe = document.getElementById('manageIframe');
    if(type === "ADDRESS"){
        src = "/payPage/checkout/api/manageStoredAddresses.php?customerId=" + orderDetails.customerId;
    }else{
        src = "/payPage/checkout/api/manageStoredCards.php?customerId=" + orderDetails.customerId
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
    return fetch("/payPage/checkout/api/addressSelection.php", {
      method: "post",
      body: JSON.stringify({
        "customerId": orderDetails.customerId
      })
    })
    .then((result) => result.text())
    .then((result) => document.getElementById('addressSelection').innerHTML = result)
    .catch((error) => console.log("showAddresses ERROR:"+error))
}
function cancel() {
    window.open('/payPage/index.php', '_parent');
}
function useSameAddressChanged() {
    orderDetails.useShippingAsBilling = isChecked('useShipAsBill');
    if (orderDetails.useShippingAsBilling) {
        // Hide Billing fields
        document.getElementById('billingForm').style.display = "none";
    }else{
        document.getElementById('billingForm').style.display = "block";
    }
}
function storeCardChanged(){
    if(isChecked('storeCard') && !orderDetails.customerId){
        // New Customer. Show Register account screen, create user, update OrderDetails. 
        // Update User record with customerId after sucessfull Auth.
        // Update Order with CustomerUserId
        // If they cancel, then dont store cards
        document.getElementById('customerUserName').value = orderDetails.bill_to.email;
        document.getElementById('createAccountSection').style.display = "block";
        document.getElementById('confirmSection').style.display = "none";
    }else{
        document.getElementById('createAccountSection').style.display = "none";
        document.getElementById('confirmSection').style.display = "block";
    }
}
function cancelRegisterUser(){
    document.getElementById('createAccountSection').style.display = "none";
    document.getElementById('confirmSection').style.display = "block";
    document.querySelector('#storeCard').checked = false;
}   
function onAccountCreated(customerUserId){
    document.getElementById("createAccountSection").style.display="none";
    document.getElementById("confirmSection").style.display="block";
    orderDetails.customerUserId = customerUserId;
}
function isChecked(id){
    usb = document.querySelector('#'+id);
    if(usb){
        return usb.checked;
    }
    return false;
}
function editEmail(){
    document.getElementById('editEmailButton').style.display = "none";
    document.getElementById('emailText').style.display = "none";
    document.getElementById('emailSection').style.display = "block";
    document.getElementById('updateEmailButton').innerHTML = "Update";
}
function updateEmail(update){
    if(update){
        if(validateForm(document.getElementById('emailForm'))){
            orderDetails.bill_to.email = document.getElementById('bill_to_email').value;
            document.getElementById('emailText').innerHTML = orderDetails.bill_to.email;
        }else{
            return;
        }
    }
    // document.getElementById('emailForm').style.display = "none";
    document.getElementById('emailSection').style.display = "none";
    document.getElementById('addressSection').style.display = "block";
}
function nextButton(form){
    switch(form){
        case "email":
            if(!validateForm(document.getElementById('emailForm'))){
                break;
            }
            orderDetails.bill_to.email = document.getElementById('bill_to_email').value;
            document.getElementById('emailText').innerHTML = orderDetails.bill_to.email;
            document.getElementById('emailText').style.display = "block";
            document.getElementById('emailSection').style.display = "none";
            document.getElementById("editEmailButton").style.display = "block";
            document.getElementById('addressSection').style.display = "block";
            break;
        case "pay":
            orderDetails.useShippingAsBilling = isChecked('useShipAsBill');
            // Pay Button clicked
            if(!orderDetails.useShippingAsBilling){
                // Validate billing data form if neccessary
                // form = ;
                if(!validateForm(document.getElementById('billingForm'))){
                    break;
                }
            }
            getToken(onTokenCreated);
            break;
        case "confirm":
            // Confirm Button clicked
            document.getElementById("inputSection").style.display = "none";
            // If storeCard checked, we will create a Payment Instrument Token
            sc = document.getElementById('storeCard');
            orderDetails.storeCard = sc.checked;
            // If its a brand new customer then store card stores the address as well
            if(orderDetails.customerId === "" && orderDetails.storeCard){
                orderDetails.storeAddress = true;        
            }else{
                // If storeAddress checked, we will create a shipping Token
                sa = document.getElementById('storeAddress');
                orderDetails.storeAddress = sa.checked;
            }
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
            document.getElementById("editEmailButton").style.display = "block";
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
            document.getElementById("editEmailButton").style.display = "block";
            document.getElementById("summary_delivery").style.display = "none";
            document.getElementById("summary_billTo").style.display = "none";
            break;
        case "confirm":
            document.getElementById("storeCardSection").style.display = "none";
            document.getElementById("confirmSection").style.display = "none";
            document.getElementById("paymentDetailsSection").style.display = "block";
            if(orderDetails.customerId === ""){
                document.getElementById("cardSelectionSection").style.display = "block";
                document.getElementById("summary_billTo").style.display = "none";
            }
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
            document.getElementById("editEmailButton").style.display = "none";
            document.getElementById("cardSelectionSection").style.display = "block";
            if(orderDetails.customerId === ""){
                // New customer - setup Card input  
                createCardInput("","payButton", false, false,"");
            }
            else{
            // Dont show the storeAddress? question if it's a brand new customer - 
            // just ask if they want to store all details
            document.getElementById("storeAddressSection").style.display = "block";
            }
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
        document.getElementById("editEmailButton").style.display = "none";
        document.getElementById("storeAddressSection").style.display = "none";
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
    cardType="";
    if(id === "NEW"){
        orderDetails.paymentInstrumentId = "";
        document.getElementById("sameAddressCheck").style.display = "block";
        document.getElementById('billToText').innerHTML = "";
    }else{
        orderDetails.paymentInstrumentId = id;
        document.getElementById('billingForm').style.display = "none";
        document.getElementById("sameAddressCheck").style.display = "none";
        document.getElementById("useShipAsBill").checked = true;
        pi=JSON.parse(document.getElementById("pi_"+id).value);
        orderDetails.maskedPan = pi._embedded.instrumentIdentifier.card.number;
        document.getElementById('billToText').innerHTML = stylePaymentInstrument(pi.card,pi._embedded.instrumentIdentifier.card.number,pi.billTo);
        cardType = pi.card.type;
        document.getElementById("summary_billTo").style.display = "block";
    }
    document.getElementById("cardSelectionSection").style.display = "none";
    createCardInput("progressSpinner", "payButton", (id==="NEW"? false: true), false, cardType)
    .then(result =>{
        document.getElementById("paymentDetailsSection").style.display = "block";
    })
    .catch(error => console.log(error));
}
function onTokenCreated(tokenDetails){
    console.log(tokenDetails);
    // Hide card input, show Confirmation section
    document.getElementById("cardSelectionSection").style.display = "none";
    document.getElementById("paymentDetailsSection").style.display = "none";
    document.getElementById("confirmSection").style.display = "block";
    if(orderDetails.paymentInstrumentId === ""){
        document.getElementById("storeCardSection").style.display = "block";
    }else{
        document.getElementById("storeCardSection").style.display = "none";
    }

    orderDetails.flexToken = tokenDetails.flexToken;
    if(orderDetails.paymentInstrumentId === ""){
        orderDetails.maskedPan = tokenDetails.cardDetails.number;
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
function setGooglePayBillingDetails(billingAddress) {
    let names = billingAddress.name.split(/(?<=^\S+)\s/);
    orderDetails.bill_to.firstName = names[0];
    orderDetails.bill_to.lastName = names[1];
    orderDetails.bill_to.address1 = billingAddress.address1;
    orderDetails.bill_to.address2 = billingAddress.address2;
    orderDetails.bill_to.locality = billingAddress.locality;
    orderDetails.bill_to.administrativeArea = billingAddress.administrativeArea;
    orderDetails.bill_to.postalCode = billingAddress.postalCode;
    orderDetails.bill_to.country = billingAddress.countryCode;
}
function setBillingDetails() {
    if(orderDetails.useShippingAsBilling) {
        orderDetails.bill_to.firstName = orderDetails.ship_to.firstName;
        orderDetails.bill_to.lastName = orderDetails.ship_to.lastName;
        orderDetails.bill_to.address1 = orderDetails.ship_to.address1;
        orderDetails.bill_to.address2 = orderDetails.ship_to.address2;
        orderDetails.bill_to.locality = orderDetails.ship_to.locality;
        orderDetails.bill_to.administrativeArea = orderDetails.ship_to.administrativeArea;
        orderDetails.bill_to.postalCode = orderDetails.ship_to.postalCode;
        orderDetails.bill_to.country = orderDetails.ship_to.country;
    }else{
        orderDetails.bill_to.firstName = document.getElementById('bill_to_firstName').value;
        orderDetails.bill_to.lastName = document.getElementById('bill_to_lastName').value;
        orderDetails.bill_to.address1 = document.getElementById('bill_to_address1').value;
        orderDetails.bill_to.address2 = document.getElementById('bill_to_address2').value;
        orderDetails.bill_to.locality = document.getElementById('bill_to_address2_locality').value;
        orderDetails.bill_to.administrativeArea = document.getElementById('bill_to_administrativeArea').value;
        orderDetails.bill_to.postalCode = document.getElementById('bill_to_postalCode').value;
        orderDetails.bill_to.country = document.getElementById('bill_to_country').value;
    }
}
function authorise() {
    document.getElementById('authSection').style.display = "block";
    setUpPayerAuth();
}
function onGooglePayCardSelected(paymentData){
    console.log(JSON.stringify(paymentData, undefined, 2));
    // Hide card input, show Confirmation section
    document.getElementById("paymentDetailsSection").style.display = "none";
    document.getElementById("summary_billTo").style.display = "none";
    document.getElementById("confirmSection").style.display = "block";
    document.getElementById("cardSelectionSection").style.display = "none";
    document.getElementById("storeCardSection").style.display = "block";

    orderDetails.paymentInstrumentId = "";
    orderDetails.useShippingAsBilling = false;
    orderDetails.maskedPan = "xxxxxxxxxxxx"+paymentData.paymentMethodData.info.cardDetails;
    orderDetails.googlePayToken = paymentData.paymentMethodData.tokenizationData.token;
    setGooglePayBillingDetails(paymentData.paymentMethodData.info.billingAddress);
    document.getElementById("summary_billTo").style.display = "block";
    let type="";
    if(paymentData.paymentMethodData.info.cardNetwork === "VISA"){
        type="001";
    }else if(paymentData.paymentMethodData.info.cardNetwork === "MASTERCARD"){
        type="002";
    }else{
        type="003";
    }
    cardDetails = {
        type: type,
        expirationMonth: "",
        expirationYear: ""
    };
    document.getElementById('billToText').innerHTML =
        stylePaymentInstrument(cardDetails, orderDetails.maskedPan, orderDetails.bill_to);
}
function  expiredCard(){
    console.log("expired Card");
}
function stylePaymentInstrument(card, number, billTo){
    img = "";
    alt = "";
    if (card.type === "001" || card.type === "visa") {
        img = "/payPage/common/images/Visa.svg";
        alt = "Visa card logo";
    } else if (card.type === "002" || card.type === "mastercard") {
        img = "/payPage/common/images/Mastercard.svg";
        alt = "Mastercard logo";
    } else {
        img = "/payPage/common/images/Amex.svg";
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
                        (card.expirationMonth?"<li><small>Expires:&nbsp;" + card.expirationMonth + "/" + card.expirationYear + "</small></li>\n":"") +
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
