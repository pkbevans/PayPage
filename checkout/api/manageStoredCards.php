<?php
if(!isset($_COOKIE['accessToken'])|| !isset($_REQUEST['customerId'])){
    $response = new Response(401, false, "Access denied", null);
    $response->send();
    exit;
}
$accessToken = $_COOKIE['accessToken'];
require_once $_SERVER['DOCUMENT_ROOT'].'/payPage/common/cybsApi/RestRequest.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/checkout/utils/countries.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/checkout/utils/cards.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/checkout/utils/addresses.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/common/v1/controller/validation.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/common/v1/model/Response.php';

$response = checkPermission($accessToken, USERTYPE_CUSTOMER, false, $_REQUEST['customerId']);
if(!$response->success()){
    $response->send();
    exit;
}

$count=0;
$paymentInstruments = new stdClass();
try {
    // Get Payment Instruments
    $api = str_replace('{customerId}', $_REQUEST['customerId'], API_TMS_V2_CUSTOMER_PAYMENT_INSTRUMENTS);

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
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="/payPage/common/css/styles.css"/>
    <title>Manage Cards</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div id="cardSelection2">
                    <div id="cardSelectionSection2">
<?php if ($count>0): ?>
                    <div>
                        <ul class="list-group ">
<?php foreach ($paymentInstruments as $paymentInstrument): ?>
                        <li class="list-group-item <?php echo (cardExpired($paymentInstrument)? "list-group-item-warning":($paymentInstrument->default?"list-group-item-primary":"list-group-item-success"));?>">
<?php if($paymentInstrument->default):?>
                            <div class="row"><div class="col-12"><strong>*Default Card</strong></div></div>
<?php endif?>
                            <div class="row">
                                <div class="col-3">
                                    <img src="/payPage/common/images/<?php echo $cardTypes[$paymentInstrument->card->type]['image']?>" class="img-fluid" alt="<?php echo $cardTypes[$paymentInstrument->card->type]['alt'];?>">
                                </div>
                                <div class="col-6 text-start">
                                    <strong><?php echo $paymentInstrument->_embedded->instrumentIdentifier->card->number; ?></strong>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-3"></div>
                                <div class="col-6 text-start">
                                    <small>Expires:&nbsp;<?php echo $paymentInstrument->card->expirationMonth . "/" . $paymentInstrument->card->expirationYear;?></small>
                                    <?php if(cardExpired($paymentInstrument)): ?>
                                        <small>****EXPIRED CARD****</small>
                                    <?php endif ?>
                                </div>
                            </div><br>
                            <div class="row">
                                <div class="col-3">Billing Address:</div>
                                <div class="col-9">
                                    <div style="max-height: 999999px;"><small><?php echo concatinateNameAddress($paymentInstrument->billTo);?></small></div>
                                </div>
                            </div>
                            <div id="<?php echo $paymentInstrument->id."_buttons";?>">
                                <div class="row">
                                    <div class="col-3">
                                        <button type="button" class="btn btn-link" onclick="editCard('<?php echo $paymentInstrument->id;?>')">Edit</button>
                                    </div>
                                    <div class="col-3">
                                        <button type="button" class="btn btn-link" onclick="updateCard('<?php echo $paymentInstrument->id;?>', true)" <?php echo ($paymentInstrument->default?"disabled":"")?>>Make default</button>
                                    </div>
                                    <div class="col-3">
                                        <button type="button" class="btn btn-link" onclick="deleteCard('<?php echo $paymentInstrument->id;?>')"  <?php echo ($paymentInstrument->default?"disabled":"")?>>Remove</button>
                                    </div>
                                </div>
                            </div>
                            <form id="<?php echo $paymentInstrument->id;?>_form" style="display: none">
                                <div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group form-floating mb-3">
                                                <input id="<?php echo $paymentInstrument->id;?>_firstName" type="text" class="form-control form-control-sm" value="<?php echo $paymentInstrument->billTo->firstName;?>" placeholder="First name" required>
                                                <label for="<?php echo $paymentInstrument->id;?>_firstName" class="form-label">First name*</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group form-floating mb-3">
                                                <input id="<?php echo $paymentInstrument->id;?>_lastName" type="text" class="form-control form-control-sm" value="<?php echo $paymentInstrument->billTo->lastName;?>" placeholder="Last Name" required>
                                                <label for="<?php echo $paymentInstrument->id;?>_lastName" class="form-label">Surname*</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group form-floating mb-3">
                                                <input id="<?php echo $paymentInstrument->id;?>_address1" type="text" class="form-control form-control-sm" value="<?php echo $paymentInstrument->billTo->address1;?>" placeholder="1st line of address" required>
                                                <label for="<?php echo $paymentInstrument->id;?>_address1" class="form-label">Address line 1*</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group form-floating mb-3">
                                                <input id="<?php echo $paymentInstrument->id;?>_address2" type="text" class="form-control form-control-sm" value="<?php echo (isset($paymentInstrument->billTo->address2)?$paymentInstrument->billTo->address2:"");?>" placeholder="2nd line of address">
                                                <label for="<?php echo $paymentInstrument->id;?>_address2" class="form-label">Address line 2</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group form-floating mb-3">
                                                <input id="<?php echo $paymentInstrument->id;?>_locality" type="text" class="form-control form-control-sm" value="<?php echo $paymentInstrument->billTo->locality;?>" placeholder="City/County" required>
                                                <label for="<?php echo $paymentInstrument->id;?>_locality" class="form-label">City/County*</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group form-floating mb-3">
                                                <input id="<?php echo $paymentInstrument->id;?>_postalCode" type="text" class="form-control form-control-sm" value="<?php echo $paymentInstrument->billTo->postalCode;?>" placeholder="Postcode" required>
                                                <label for="<?php echo $paymentInstrument->id;?>_postalCode" class="form-label">PostCode*</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="form-group form-floating mb-3">
                                                <select id="<?php echo $paymentInstrument->id;?>_country" class="form-select">
<?php
foreach ($countries as $key => $value) {
    echo "<option value=\"". $key ."\"" . ( $paymentInstrument->billTo->country == $key? "selected": "") . ">" . $value . "</option>\n";
}
?>
                                                </select>
                                                <label for="<?php echo $paymentInstrument->id;?>_address_country" class="form-label">Country*</label>
                                            </div>
                                        </div>
                                    </div>
                            *Required fields
                                </div>
<?php if(!$paymentInstrument->default):?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="<?php echo $paymentInstrument->id;?>_defaultCard" <?php echo ($paymentInstrument->default?"checked ":"");?>>
                                    <label class="form-check-label" for="flexCheckDefault">Make this my default card</label>
                                </div>
<?php endif?>
                                <div class="row">
                                    <div class="col-sm-1">
                                        <button type="button" class="btn btn-link" onclick="updateCard('<?php echo $paymentInstrument->id;?>',false)">Save</button>
                                    </div>
                                    <div class="col-sm-1">
                                        <button type="button" class="btn btn-link" onclick="cancelEdit('<?php echo $paymentInstrument->id;?>')">Cancel</button>
                                    </div>
                                </div>
                            </form>
                        </li>
<?php endforeach; ?>
                        <li class="list-group-item">
                            <div class="row">
                                <button type="button" class="btn btn-primary" onclick="newCard()">Add a new card</button>
                            </div>
                        </li>
                        </ul>
                    </div>
<?php endif?>
                    <div id="newCardSection" style="display: <?php echo ($count>0?"none":"block");?>">
                        <div class="col-12">
                            <div id="cardInputSection">
                                <div class="d-flex mb-3">
                                    <div id="cardInput">
                                        <form onsubmit="return false;">
                                            <div class="card">
                                                <div class="card-body">
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
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="billingAddressSection">
                            <form id="billingForm" class="needs-validation" novalidate>
                                <div id="billingSection">
                                    <div class="row">
                                        <div class="12">
                                            <h5>Card Billing:</h5>
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
                                                <input id="bill_to_locality" type="text" class="form-control form-control-sm" value="" tabindex="5" placeholder="City/County" required maxlength="50">
                                                <label for="bill_to_locality" class="form-label">City/County*</label>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group form-floating mb-3">
                                                <input id="bill_to_postalCode" type="text" class="form-control form-control-sm" value="" tabindex="6" placeholder="Postcode" required maxlength="10">
                                                <label for="bill_to_postalCode" class="form-label">PostCode*</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
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
                        <div class="row">
                            <div class="col-12">
                                <button type="button" id="addCardButton" onclick="storeCard()" class="btn btn-primary" disabled="true">Store</button>
                                <button type="button" class="btn btn-secondary" onclick="cancelNewCard()">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="errorAlert" class="alert alert-danger fade show" role="alert" style="display:none">
            <strong><span id="alertText">Somethin went wrong. Please try again.</span></strong>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
</body>
<script src="https://flex.cybersource.com/cybersource/assets/microform/0.11/flex-microform.min.js"></script>
<script src="/payPage/checkout/js/cardInput.js"></script>
<script>
var customerId = "<?php echo $_REQUEST['customerId'];?>";
var errorAlert;
document.addEventListener("DOMContentLoaded", function (e) {
    errorAlert = document.getElementById("errorAlert");
});
function validateForm(form){
    if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
        form.classList.add('was-validated');
        return false;
    }
    return true;
}
function closeWindow(){
    console.log("Close");
}
let orderDetails = {
    referenceNumber: "<?php echo $_REQUEST['reference_number'];?>",
    orderId: "<?php echo $_REQUEST['orderId'];?>",
    amount: "0.00",
    currency: "<?php echo $_REQUEST['currency'];?>",
    shippingAddressRequired: false,
    useShippingAsBilling: false,
    customerId: customerId,
    customerUserId: 0,  //TODO
    paymentInstrumentId: "",
    shippingAddressId: "",
    flexToken: "",
    googlePayToken: "",
    maskedPan: "",
    storeCard: true,
    storeAddress: false,
    buyNow: false,
    capture: false,
    bill_to: {
        firstName: "",
        lastName: "",
        email: "<?php echo $_REQUEST['email'];?>",
        address1: "",
        address2: "",
        locality: "",
        administrativeArea: "",
        postalCode: "",
        country: ""
    }
};
function newCard(){
    console.log("New Card");
    document.getElementById("newCardSection").style.display = "block";
    createCardInput("", "addCardButton", false, false, "");
}
function cancelNewCard(){
    console.log("cancelNewCard");
    document.getElementById("newCardSection").style.display = "none";
}
function editCard(id){
    document.getElementById(id+"_form").style.display = "block";
    document.getElementById(id+"_buttons").style.display = "none";
}
function cancelEditCard(id){
    document.getElementById(id+"_form").style.display = "none";
    document.getElementById(id+"_buttons").style.display = "block";
}
function updateCard(id, setDefaultOnly){
    console.log("\nUpdating Card: "+id);
    def = document.getElementById(id+"_defaultCard");
    if(def){
        // This card is NOT the default card
        defaultCard = def.checked;
    }else{
        // This card is currently the default card
        defaultCard = true;
    }
    firstName = document.getElementById(id+"_firstName").value;
    lastName = document.getElementById(id+"_lastName").value;
    address1 = document.getElementById(id+"_address1").value;
    address2 = document.getElementById(id+"_address2").value;
    locality = document.getElementById(id+"_locality").value;
    postalCode = document.getElementById(id+"_postalCode").value;
    country = document.getElementById(id+"_country").value;

    return fetch("/payPage/checkout/api/updateCustomerPaymentInstrument.php", {
        method: "post",
        body: JSON.stringify({
            "setDefaultOnly": setDefaultOnly,
            "customerId": customerId,
            "paymentInstrumentId": id,
            "default": defaultCard,
            "firstName": firstName,
            "lastName": lastName,
            "address1": address1,
            "address2": address2,
            "locality": locality,
            "administrativeArea": "",
            "postalCode": postalCode,
            "country": country,
            "phoneNumber": ""
        })
    })
    .then((result) => {
        if (result.ok) {
            // Successfull response - reload this page and notify parent
            location.reload();
            parent.onStoredDataUpdated("CARD", "UPDATE");
        } else {
            // 500 System error or anything else
            document.getElementById("alertText").innerHTML = "Update Failed.  Please check your internet connection and try again";
            errorAlert.style.display = "block";
            throw "Unable to update Card details. "  + result.status + ":" + result.statusText;
        }        
    })
    .catch(error => console.error(error))
}
function storeCard(){
    form = document.getElementById('billingForm');
    if(validateForm(form)){
        getToken(onTokenCreated);
    }
}
function onTokenCreated(tokenDetails){
    console.log(tokenDetails);
    // Hide card input, show Confirmation section
    document.getElementById("newCardSection").style.display = "none";

    orderDetails.flexToken = tokenDetails.flexToken;
    orderDetails.maskedPan = tokenDetails.cardDetails.number;
    setBillingDetails();
    addCard()
}
function setBillingDetails() {
    orderDetails.bill_to.firstName = document.getElementById('bill_to_firstName').value;
    orderDetails.bill_to.lastName = document.getElementById('bill_to_lastName').value;
    orderDetails.bill_to.address1 = document.getElementById('bill_to_address1').value;
    orderDetails.bill_to.address2 = document.getElementById('bill_to_address2').value;
    orderDetails.bill_to.locality = document.getElementById('bill_to_locality').value;
    orderDetails.bill_to.postalCode = document.getElementById('bill_to_postalCode').value;
    orderDetails.bill_to.country = document.getElementById('bill_to_country').value;
}
function addCard(){
    // Zero-value auth without Payer Auth
    console.log("\nAdding Payment Instrument");
    return fetch("api/authWithPa.php", {
        method: "post",
        body: JSON.stringify({
            "order": orderDetails,
            "paAction": "NO_PA",
            "referenceID": "",
            "authenticationTransactionID": ""
        })
    })
    .then(response =>{
        console.log(response);
        if (!response.ok) {
            throw Error(response.statusText);
        }
        httpCode = response.status;
        return response.json();
    })
    .then(res => {
        // Successfull response (but could be declined)
        if (res.status === "AUTHORIZED") {
            // Get Payment instrument
            location.reload();
            parent.onStoredDataUpdated("CARD", "ADD");
        } else {
            // Let user know that it failed
            document.getElementById("alertText").innerHTML = "Add Failed.  Please check your internet connection and try again";
            errorAlert.style.display = "block";
            throw "Unable to Add new Card. "  + result.status + ":" + result.statusText;
        }
    })
    .catch(error => console.error(error))
}

function onAuthError(status, httpCode, reason, message){
    // TODO
}
function deleteCard(id){
    console.log("\nDeleting Card: "+id);
    return fetch("/payPage/checkout/api/deleteCustomerPaymentInstrument.php", {
        method: "post",
        body: JSON.stringify({
            "customerId": customerId,
            "paymentInstrumentId": id
        })
    })
    .then((result) => {
        if (result.ok) {
            // Successfull response - reload this page and notify parent
            location.reload();
            parent.onStoredDataUpdated("CARD", "DELETE");
        } else {
            // 500 System error or anything else
            document.getElementById("alertText").innerHTML = "Delete Failed.  Please check your internet connection and try again";
            errorAlert.style.display = "block";
            throw "Unable to update Address. "  + result.status + ":" + result.statusText;
        }        
    })
    .catch(error => console.error(error))
}
function cancelEdit(id){
    document.getElementById(id+"_form").style.display = "none";
    document.getElementById(id+"_buttons").style.display = "block";
}
</script>
</html>
