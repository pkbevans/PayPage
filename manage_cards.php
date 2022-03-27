<?php
include "rest_get_customer_cards.php";
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
?>
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css/styles.css"/>
    <title>Manage Your Cards</title>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
    <body>
        <div class="container">
        <h5>Select which card to use</h5>
            <ul class="list-group">
<?php       foreach ($paymentInstruments as $paymentInstrument): ?>
                <li class="list-group-item">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="paymentInstrument" value="<?php echo $paymentInstrument->id;?>" <?php echo ($paymentInstrument->default?"checked":"");?>>
                        <div class="row">
                            <div class="col-2">
                                <img src="images/<?php echo $cardTypes[$paymentInstrument->card->type]['image']?>" class="img-fluid" alt="<?php echo $cardTypes[$paymentInstrument->card->type]['alt'];?>">
                            </div>
                            <div class="col-5">
                                <ul class="list-unstyled">
                                    <li><strong><?php echo $paymentInstrument->_embedded->instrumentIdentifier->card->number; ?></strong></li>
                                    <li><small>Expires:&nbsp;<?php echo $paymentInstrument->card->expirationMonth . "/" . $paymentInstrument->card->expirationYear;?></small></li>
                                    <li><small><?php echo concatinateNameAddress($paymentInstrument->billTo);?></small></li>
                                </ul>
                            </div>
                            <div class="col-2">
                                <?php if(!$paymentInstrument->default):?>
                                <button type="button" class="btn btn-primary" onclick="deletePaymentInstrument('<?php echo $paymentInstrument->id;?>')">Remove</button>
                                <button type="button" class="btn btn-link" onclick="updatePaymentInstrument('<?php echo $paymentInstrument->id;?>',true)">Set as default</button>
                                <?php endif?>
                            </div>
                        </div>
                    </div>
                </li>
<?php endforeach; ?>
                <li class="list-group-item">
                    <input class="form-check-input" type="radio" name="paymentInstrument" value="NEW" onclick="addCard()">
                    <label class="form-check-label" for="exampleRadios1">Add a new card</label>
                    <div id="addCardSection" style="display:none">
                        <div id="cardInputSection"></div>
                        <form id="billingForm" class="needs-validation" novalidate>
                            <div id="billingSection">
                                <h5>Card Billing Address</h5>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group form-floating mb-3">
                                            <input id="bill_to_firstName" type="text" class="form-control form-control-sm" value="" placeholder="First name" required>
                                            <label for="bill_to_firstName" class="form-label">First name*</label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group form-floating mb-3">
                                            <input id="bill_to_lastName" type="text" class="form-control form-control-sm" value="" placeholder="Last Name" required>
                                            <label for="bill_to_lastName" class="form-label">Last name*</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group form-floating mb-3">
                                            <input id="bill_to_address1" type="text" class="form-control form-control-sm" value="" placeholder="1st line of address" required>
                                            <label for="bill_to_address1" class="form-label">Address line 1*</label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group form-floating mb-3">
                                            <input id="bill_to_address2" type="text" class="form-control form-control-sm" value="" placeholder="2nd line of address">
                                            <label for="bill_to_address2" class="form-label">Address line 2</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group form-floating mb-3">
                                            <input id="bill_to_locality" type="text" class="form-control form-control-sm" value="" placeholder="City/County" required>
                                            <label for="bill_to_locality" class="form-label">City/County*</label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group form-floating mb-3">
                                            <input id="bill_to_postalCode" type="text" class="form-control form-control-sm" value="" placeholder="Postcode" required>
                                            <label for="bill_to_postalCode" class="form-label">PostCode*</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group form-floating mb-3">
                                            <select id="bill_to_country" class="form-control form-control-sm">
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
                </li>
            </ul>
        <div class="row">
            <div class="col-5">
                <button type="button" class="btn btn-primary" onclick="usePaymentInstrument()">OK</button>
                <button type="button" class="btn btn-secondary" onclick="cancel()">Cancel</button>
            </div>
        </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
    </body>
<script src="https://flex.cybersource.com/cybersource/assets/microform/0.11/flex-microform.min.js"></script>
<script src="js/newCard2.js"></script>
<script>
var customerId = "<?php echo $customerToken;?>";
<?php
foreach ($paymentInstruments as $paymentInstrument){
echo "var paymentInstrument_". $paymentInstrument->id . " = '" . json_encode($paymentInstrument) . "';\n";
}
?>
document.addEventListener("DOMContentLoaded", function (e) {
    createCardInput("cardInputSection", "", "newCardButton", false, true);
});
function newCard(flexDetails){
    // New card details received from newCard2.js
    billTo = {
        firstName: document.getElementById("bill_to_firstName").value,
        lastName: document.getElementById("bill_to_lastName").value,
        address1: document.getElementById("bill_to_address1").value,
        address2: document.getElementById("bill_to_address2").value,
        locality: document.getElementById("bill_to_locality").value,
        postalCode: document.getElementById("bill_to_postalCode").value,
        country: document.getElementById("bill_to_country").value
    };
    // New card/billing details but not to be stored
//    parent.onNewCardUsed(flexDetails, billTo);
    addPaymentInstrument(flexDetails, billTo);
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
function addCard(){
    console.log("add card");
    document.getElementById("addCardSection").style.display = "block";
}
function editPaymentInstrument(id){
    document.getElementById(id+"_form").style.display = "block";
    document.getElementById(id+"_buttons").style.display = "none";
}
function cancelEdit(id){
    document.getElementById(id+"_form").style.display = "none";
    document.getElementById(id+"_buttons").style.display = "block";
}
function updatePaymentInstrument(id, setDefaultOnly){
    console.log("\nUpdating Card: "+id);

    defaultCard = false;
    if(setDefaultOnly){
        defaultCard = true;
        data = {    
            setDefaultOnly: true,
            customerId: customerId,
            paymentInstrumentId: id
        };
    }else{
        def = document.getElementById(id+"_defaultCard");
        if(def){
            defaultCard = def.checked;
            firstName = document.getElementById(id+"_firstName").value;
            lastName = document.getElementById(id+"_lastName").value;
            address1 = document.getElementById(id+"_address1").value;
            address2 = document.getElementById(id+"_address2").value;
            locality = document.getElementById(id+"_locality").value;
            postalCode = document.getElementById(id+"_postalCode").value;
            country = document.getElementById(id+"_country").value;
            data = {
                "setDefaultOnly": false,
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
            };
        }
    }

    $.ajax({
        type: "POST",
        url: "rest_update_customer_payment_instrument.php",
        data: JSON.stringify(data),
        success: function (result) {
            // Response is a json string - turn it into a javascript object
            let res = JSON.parse(result);
            console.log("\nUpdate:\n" + JSON.stringify(res, undefined, 2));
            let httpCode = res.responseCode;
            if (httpCode === 200) {
                // Successfull response
                location.reload();
            } else {
                // 500 System error or anything else
            }
        }
    });
}
function usePaymentInstrument(){
    // Work out which card is selected
    var radios = document.getElementsByName('paymentInstrument');
    id="";
    for (var i = 0, length = radios.length; i < length; i++) {
      if (radios[i].checked) {
        // do whatever you want with the checked radio
        id = radios[i].value;
        break;
      }
    }
    if(id === "NEW"){
       addNewCard();
    }else{
        xxx = window['paymentInstrument_'+id];
        parent.onPaymentInstrumentUpdated(id, JSON.parse(xxx));
    }
}
function addNewCard(){
    form = document.getElementById('billingForm');
    if(validateForm(form)){
        getToken(newCard);
    }
}
function addPaymentInstrument(flexDetails, billToDetails){
    // Zero-value auth without Payer Auth
    console.log("\nAdding Payment Instrument");
//    card = JSON.parse(cardDetails);
    let orderDetails = {
        referenceNumber: "<?php echo $_REQUEST['reference_number'];?>",
        orderId: "<?php echo $_REQUEST['orderId'];?>",
        amount: "0.00",
        currency: "<?php echo $_REQUEST['currency'];?>",
        local: false, // TODO
        shippingAddressRequired: false,
        useShippingAsBilling: false,
        customerId: customerId,
        paymentInstrumentId: "",
        shippingAddressId: "",
        flexToken: flexDetails.flexToken,
        maskedPan: flexDetails.cardDetails.number,
        storeCard: true,
        capture: false,
        bill_to: {
            firstName: billToDetails.firstName,
            lastName: billToDetails.lastName,
            email: "<?php echo $_REQUEST['email'];?>",
            address1: billToDetails.address1,
            address2: billToDetails.address2,
            locality: billToDetails.locality,
            postalCode: billToDetails.postalCode,
            country: billToDetails.country
        }
    };
    $.ajax({
        type: "POST",
        url: "rest_auth_with_pa.php",
        data: JSON.stringify({
            "order": orderDetails,
            "paAction": "NO_PA",
            "referenceID": "",
            "authenticationTransactionID": ""
        }),
        success: function (result) {
            console.log("\Auth:\n" + result);
            // Response is a json string - turn it into a javascript object
            let res = JSON.parse(result);
            console.log("\Auth:\n" + JSON.stringify(res, undefined, 2));
            let httpCode = res.responseCode;
            let status = res.response.status;
            if (httpCode === 201) {
                let requestID = res.response.id;
                // Successfull response (but could be declined)
                if (status === "AUTHORIZED") {
                    // Get Payment instrument
                    getPaymentInstrument(res.response.tokenInformation.paymentInstrument.id);
                } else {
                    // TODO - let user know that it failed
                }
            } else {
                // 500 System error or anything else
                switch(httpCode){
                    case "202":
                        onAuthError(status, httpCode, res.response.errorInformation.reason, res.response.errorInformation.message);
                        break;
                    case "400":
                        onAuthError(status, httpCode, res.response.reason, res.response.details);
                        break;
                    default:
                        onAuthError(status, httpCode, res.response.reason, res.response.message);
                }
            }
        }
    });
}
function getPaymentInstrument(id){
    console.log("\nGetting Payment Instrument: "+id);
    $.ajax({
        type: "POST",
        url: "rest_get_payment_instrument.php",
        data: JSON.stringify({
            "id": id
        }),
        success: function (result) {
            // Response is a json string - turn it into a javascript object
            let res = JSON.parse(result);
            console.log("\nGot:\n" + JSON.stringify(res, undefined, 2));
            let httpCode = res.responseCode;
            if (httpCode === 200) {
                // Successfull response
                parent.onPaymentInstrumentUpdated(id, res.response);
            } else {
                // 500 System error or anything else - TODO
            }
        }
    });
}
function onAuthError(status, httpCode, reason, message){
    // TODO
}
function deletePaymentInstrument(id){
    console.log("\nDeleting Card: "+id);
    $.ajax({
        type: "POST",
        url: "rest_delete_customer_payment_instrument.php",
        data: JSON.stringify({
            "customerId": customerId,
            "paymentInstrumentId": id
        }),
        success: function (result) {
            // Response is a json string - turn it into a javascript object
            let res = JSON.parse(result);
            console.log("\nDelete:\n" + JSON.stringify(res, undefined, 2));
            let httpCode = res.responseCode;
            if (httpCode === 204) {
                // Successfull response
                location.reload();
            } else {
                // 500 System error or anything else - TODO
            }
        }
    });
}
function cancel(){
    parent.onIframeCancelled();
}
</script>
</html>
