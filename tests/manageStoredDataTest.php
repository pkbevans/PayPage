<?php
include_once '../countries.php';
include_once '../card_types.php';
?>
<!DOCTYPE html>
<html lang="en-GB">
<head   >
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="../css/styles.css"/>
    <title>Bootstrap Test</title>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div id="addressSelection">
                </div>
            </div>
<!--            <div class="col-6">
                <div id="cardSelection">
                </div>
            </div>-->
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
</body>
<script src="https://flex.cybersource.com/cybersource/assets/microform/0.11/flex-microform.min.js"></script>
<script src="../js/cardInput.js"></script>
<script>
    var customerId = "<?php echo $_REQUEST['customerId'];?>";
document.addEventListener("DOMContentLoaded", function (e) {
    showAddresses();
//    showCards();
});
function showAddresses(){
    $.ajax({
        type: "POST",
        url: "/payPage/manageAddressSelection.php",
        data: JSON.stringify({
            "customerId": customerId
        }),
        success: function (result) {
            document.getElementById('addressSelection').innerHTML = result;
        }
    });
}
function showCards(){
    $.ajax({
        type: "POST",
        url: "/payPage/manageCardSelection.php",
        data: JSON.stringify({
            "customerId": customerId
        }),
        success: function (result) {
            document.getElementById('cardSelection').innerHTML = result;
        }
    });
}
function newAddress(){
    console.log("New Address");
    document.getElementById("newAddressSection").style = "block";
}
function editAddress(id){
    document.getElementById(id+"_form").style.display = "block";
    document.getElementById(id+"_buttons").style.display = "none";
}
function updateAddress(id, setDefaultOnly){
    console.log("\nUpdating Shipping Address: "+id);
    // Validate fields
    if(!setDefaultOnly){
        form = document.getElementById(id+"_form");
        if(!validateForm(form)){
            return;
        }
    }
    def = document.getElementById(id+"_defaultAddress");
    if(def){
        // This address is NOT the default address
        defaultAddress = def.checked;
    }else{
        // This address is currently the default address
        defaultAddress = true;
    }
    firstName = document.getElementById(id+"_firstName").value;
    lastName = document.getElementById(id+"_lastName").value;
    address1 = document.getElementById(id+"_address1").value;
    address2 = document.getElementById(id+"_address2").value;
    locality = document.getElementById(id+"_locality").value;
    postalCode = document.getElementById(id+"_postalCode").value;
    country = document.getElementById(id+"_country").value;

    $.ajax({
        type: "POST",
        url: "/payPage/rest_update_customer_shipping_address.php",
        data: JSON.stringify({
            "setDefaultOnly": setDefaultOnly,
            "customerId": customerId,
            "shippingAddressId": id,
            "default": defaultAddress,
            "firstName": firstName,
            "lastName": lastName,
            "address1": address1,
            "address2": address2,
            "locality": locality,
            "administrativeArea": "",
            "postalCode": postalCode,
            "country": country,
            "phoneNumber": ""
        }),
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
function makeDefault(id){
    console.log("makeDefault: "+id);
}
function deleteAddress(id){
    console.log("\nDeleting Shipping Address: "+id);
    $.ajax({
        type: "POST",
        url: "/payPage/rest_delete_customer_shipping_address.php",
        data: JSON.stringify({
            "customerId": customerId,
            "shippingAddressId": id
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
function addAddress(){
    console.log("\nAdding Shipping Address");
    // Validate fields
    form = document.getElementById('newAddressForm');
    if(!validateForm(form)){
        return;
    }
    def = document.getElementById("add_defaultAddress");
    if(def){
        defaultAddress = def.checked;
    }else{
        defaultAddress = false;
    }
    firstName = document.getElementById("ship_to_firstName").value;
    lastName = document.getElementById("ship_to_lastName").value;
    address1 = document.getElementById("ship_to_address1").value;
    address2 = document.getElementById("ship_to_address2").value;
    locality = document.getElementById("ship_to_locality").value;
    postalCode = document.getElementById("ship_to_postalCode").value;
    country = document.getElementById("ship_to_country").value;

    $.ajax({
        type: "POST",
        url: "/payPage/rest_add_customer_shipping_address.php",
        data: JSON.stringify({
            "customerId": customerId,
            "default": defaultAddress,
            "firstName": firstName,
            "lastName": lastName,
            "address1": address1,
            "address2": address2,
            "locality": locality,
            "administrativeArea": "",
            "postalCode": postalCode,
            "country": country,
            "phoneNumber": ""
        }),
        success: function (result) {
            // Response is a json string - turn it into a javascript object
            let res = JSON.parse(result);
            console.log("\Add:\n" + JSON.stringify(res, undefined, 2));
            let httpCode = res.responseCode;
            let status = res.response.status;
            if (httpCode === 201) {
                // Successfull response
                location.reload();
            } else {
                // 500 System error or anything else
            }
        }
    });
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
function closeWindow(){
    console.log("Close");
}
////////CARDS
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
    flexToken: "",
    maskedPan: "",
    storeCard: true,
    capture: false,
    bill_to: {
        firstName: "",
        lastName: "",
        email: "<?php echo $_REQUEST['email'];?>",
        address1: "",
        address2: "",
        locality: "",
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

    $.ajax({
        type: "POST",
        url: "/payPage/rest_update_customer_payment_instrument.php",
        data: JSON.stringify({
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
        }),
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
    $.ajax({
        type: "POST",
        url: "/payPage/rest_auth_with_pa.php",
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
                    location.reload();
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

function onAuthError(status, httpCode, reason, message){
    // TODO
}
function deleteCard(id){
    console.log("\nDeleting Card: "+id);
    $.ajax({
        type: "POST",
        url: "/payPage/rest_delete_customer_payment_instrument.php",
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
function cancelEdit(id){
    document.getElementById(id+"_form").style.display = "none";
    document.getElementById(id+"_buttons").style.display = "block";
}
</script>
</html>
