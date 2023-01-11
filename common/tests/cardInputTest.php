<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/checkout/php/utils/countries.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/checkout/php/utils/cards.php';
?>
<!DOCTYPE html>
<html lang="en-GB">
<head   >
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="../css/stylesTest.css"/>
    <title>Bootstrap Test</title>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
    <div class="container">
        <div id="progressSpinner"  class="spinner-border text-info" style="display: block;"></div>
        <div class="row">
            <div class="col-12">
                <input type="checkbox" class="form-check-input" onchange="cvvOnlyChanged()" id="cvvOnly" name="cvvOnly" value="1">
                <label for="cvvOnly" class="form-check-label">CVV Only</label>
            </div>
        </div>
        <label>TOKEN</label><input id="debug" type="text">
<div id="paymentDetailsSection" style="display: block">
    <!--<div class="col-12">-->
        <div id="cardInputSection">
            <div class="d-flex mb-3">
                <div id="cardInput">
                    <form onsubmit="return false;">
                        <div class="card">
                            <div class="card-body" style="width: 90vw">
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
            <!--</div>-->
            <div id="billingAddressSection">
                <div  id="storeCardCheck" style="display: <?php echo ($count>0?"none":"block");?>">
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
                </div>
                <form id="billingForm" class="needs-validation" novalidate style="display: none">
                    <div id="billingSection">
                        <div class="row">
                            <div class="12">
                                <h5>Card Billing:</h5>
                            </div>
                        </div>
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
                                    <input id="bill_to_address_city" type="text" class="form-control form-control-sm" value="" tabindex="5" placeholder="City/County" required maxlength="50">
                                    <label for="bill_to_address_city" class="form-label">City/County*</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group form-floating mb-3">
                                    <input id="bill_to_postcode" type="text" class="form-control form-control-sm" value="" tabindex="6" placeholder="Postcode" required maxlength="10">
                                    <label for="bill_to_postcode" class="form-label">PostCode*</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
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
            </div>
            <div class="row">
                <div class="col-12">
                    <button type="button" id="payButton" onclick="payNow()" class="btn btn-primary" disabled="true">Pay</button>
                    <button type="button" class="btn btn-secondary" onclick="backButton()">Back</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
</body>
<script src="https://flex.cybersource.com/cybersource/assets/microform/0.11/flex-microform.min.js"></script>
<script src="../js/cardInput.js"></script>
<script>
let orderDetails = {
        referenceNumber: "",
        orderId: "",
        amount: "",
        currency: "",
        local: "<?php echo isset($_REQUEST['local']) && $_REQUEST['local'] === "true"?"true":"false";?>",
        shippingAddressRequired: true,
        useShippingAsBilling: true,
        customerId: "",
        paymentInstrumentId: "",
        shippingAddressId: "",
        flexToken: "",
        maskedPan: "",
        storeCard: false,
        capture: false,
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
document.addEventListener("DOMContentLoaded", function (e) {
    createCardInput("progressSpinner", "payButton", false, false,"" );
});

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
function payNow(){
    if(orderDetails.useShippingAsBilling){
        getToken(onTokenCreated);
    }else{
        form = document.getElementById('billingForm');
        if(validateForm(form)){
            getToken(onTokenCreated);
        }
    }
}
function onTokenCreated(tokenDetails){
    console.log(tokenDetails);
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
function cvvOnlyChanged(){
    xxx = document.querySelector('#cvvOnly');
    flipCvvOnly(xxx.checked, "003");
}
function backButton(){
    console.log("Back Button");
}
</script>
</html>
