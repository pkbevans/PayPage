
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
</head>
<body>
    <!--Cardinal device data collection code START-->
    <iframe id="cardinal_collection_iframe" name="collectionIframe" height="1" width="1" style="display: none;"></iframe>
    <form id="cardinal_collection_form" method="POST" target="collectionIframe" action="">
        <input id="cardinal_collection_form_input" type="hidden" name="JWT" value=""/>
    </form>
    </<!--Cardinal device data collection code END-->
    <div id="overlay">
        <iframe id="step_up_iframe" style="border: none; margin-left: auto; margin-right: auto; display: none" height="50%" width="100%" name="stepUpIframe" ></iframe>
    </div>
    <form id="step_up_form" name="stepup" method="POST" target="stepUpIframe" action="">
        <input id="step_up_form_jwt_input" type="hidden" name="JWT" value=""/>
        <input id="MD" type="hidden" name="MD" value="HELLO MUM. GET THE KETTLE ON"/>
    </form>
    <div class="container">
        <div id="paymentDetailsSection">
            <div class="row">
                <div class="col-sm-6"><label for="bill_to_email" class="form-label">Email*</label><input id="bill_to_email" type="email" class="form-control form-control-sm" value="" placeholder="Enter email" required></div>
                <div class="valid-feedback">
                    Looks good!
                </div>
            </div>
            <div id="shippingDetailsSection" class="form-group">
                    <form id="shippingForm" class="needs-validation" novalidate>    
                        <h5>Delivery Address</h5>
                             
                        <div class="row">
                            <div class="col-sm-2">
                                <label for="ship_to_forename" class="form-label">First name*</label>
                                <input id="ship_to_forename" type="text" class="form-control form-control-sm" value="" placeholder="First name" required>
                            </div>
                            <div class="col-sm-2">
                                <label for="ship_to_surname" class="form-label">Surname*</label>
                                <input id="ship_to_surname" type="text" class="form-control form-control-sm" value="" placeholder="Last Name" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-3">
                                <label for="ship_to_address_line1" class="form-label">Address line 1*</label>
                                <input id="ship_to_address_line1" type="text" class="form-control form-control-sm" value="" placeholder="1st line of address" required>
                            </div>
                            <div class="col-sm-3">
                                <label for="ship_to_address_line2" class="form-label">Address line 2</label>
                                <input id="ship_to_address_line2" type="text" class="form-control form-control-sm" value="" placeholder="2nd line of address">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-3"><label for="ship_to_address_city" class="form-label">City/County*</label>
                                <input id="ship_to_address_city" type="text" class="form-control form-control-sm" value="" placeholder="City/County" required>
                            </div>
                            <div class="col-sm-3"><label for="ship_to_postcode" class="form-label">PostCode*</label>
                                <input id="ship_to_postcode" type="text" class="form-control form-control-sm" value="" placeholder="Postcode" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6"><label for="ship_to_address_country" class="form-label">Country*</label>
                                <input id="ship_to_address_country" type="text" class="form-control form-control-sm" value="" placeholder="Country" required>
                            </div>
                        </div>
                        *Required fields
                             
                    </form>
            </div>
            <h5>Card Details</h5>
            <div id="cardDetailsSection">
                <div class="row">
                    <div class="col-sm-4">
                        <label class="control-label" for="number-container">Card Number</label>
                        <div id="number-container" class="form-control form-control-sm"></div>
                    </div>
                </div>
                <div class="row">
                        <label class="control-label" for="expiryContainer" >Expires</label>
                    <div class="col-sm-4" id="expiryContainer">
                        <input type="number" maxLength="2" id="card_expirationMonth" class="expInput" name="card_expirationMonth" placeholder="MM" pattern="1[0-2]|0[1-9]" required>
                        <input type="number" min="21" max="30" id="card_expirationYear" class="expInput" name="card_expirationYear" placeholder="YY" required>
                    </div>
                </div>
            </div>
            <div class="row">
                <label id="mySecurityCodeLabel" class="form-label">Security Code</label> 
                <div class="col-sm-2">
                    <div id="securityCode-container" class="form-control form-control-sm"></div>
                </div>
            </div>
            <br>
            <div id="storeCardSection" class="row">
                <div class="col-sm-5">
                    <input type="checkbox" class="form-check-input" id="storeCard" name="storeCard" value="1">
                    <label for="storeCard" class="form-check-label">Store my details for future use</label>
                </div>
            </div>
            <div id="useShippingAsBilling" class="row">
                <div class="col-sm-5">
                    <input type="checkbox" class="form-check-input" onchange="useSameAddressChanged()" id="useShipAsBill" name="useShipAsBill" value="1" checked="checked">
                    <label for="useShipAsBill" class="form-check-label">Use Delivery Address as Billing Address</label>
                </div>
            </div>
                <div class="form-group">
                <form id="billingForm" class="needs-validation" novalidate style="display: none">    
                    <h5>Card Billing Address</h5>
                         
                    <div class="row">
                        <div class="col-sm-3">
                            <label for="bill_to_forename" class="form-label">First name*</label>
                            <input id="bill_to_forename" type="text" class="form-control form-control-sm" value="" placeholder="First name" required>
                        </div>
                        <div class="col-sm-3">
                            <label for="bill_to_surname" class="form-label">Surname*</label>
                            <input id="bill_to_surname" type="text" class="form-control form-control-sm" value="" placeholder="Last Name" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3">
                            <label for="bill_to_address_line1" class="form-label">Address line 1*</label>
                            <input id="bill_to_address_line1" type="text" class="form-control form-control-sm" value="" placeholder="1st line of address" required>
                        </div>
                        <div class="col-sm-3">
                            <label for="bill_to_address_line2" class="form-label">Address line 2</label>
                            <input id="bill_to_address_line2" type="text" class="form-control form-control-sm" value="" placeholder="2nd line of address">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3"><label for="bill_to_address_city" class="form-label">City/County*</label>
                            <input id="bill_to_address_city" type="text" class="form-control form-control-sm" value="" placeholder="City/County" required>
                        </div>
                        <div class="col-sm-3"><label for="bill_to_postcode" class="form-label">PostCode*</label>
                            <input id="bill_to_postcode" type="text" class="form-control form-control-sm" value="" placeholder="Postcode" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6"><label for="bill_to_address_country" class="form-label">Country*</label>
                            <input id="bill_to_address_country" type="text" class="form-control form-control-sm" value="" placeholder="Country" required>
                        </div>
                    </div>
                    *Required fields
                                    </form>
            </div>
            <BR><button type="button" class="btn btn-primary" disabled="true">Pay</button><BR><BR>
            <button type="button" class="btn btn-secondary" onclick="cancel()">Cancel</button>
        </div>
        <div class="card card-body" id="resultSection" style="display: none">
            <h4 class="card-title">Result</h4>
            <div id="progressSpinner"  class="spinner-border text-info"></div>
            <p id="result" class="card-text"></p>
            <button type="button" id="retryButton" class="btn btn-primary" onclick="window.location.reload(true)" style="display: none">Try again</button>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
</body>
<script src="https://flex.cybersource.com/cybersource/assets/microform/0.11/flex-microform.min.js"></script>
<script>
// the capture context that was requested server-side for this transaction
var captureContext = "eyJraWQiOiJ6dSIsImFsZyI6IlJTMjU2In0.eyJmbHgiOnsicGF0aCI6Ii9mbGV4L3YyL3Rva2VucyIsImRhdGEiOiJ5Nld1ZVVEOUowZ3Y4UUVwTnU2cVV4QUFFSVE2UldSeW1WcUl1WDlwT2ZGbUNOb3J0cWVVYVd3RjRuVUdCbUZpdGNVUStNUmhSL3J5bitVN1VOQXArdGZtWGFJcUJWaDNGNURsT0pHTmkycXVQVVAvVHdZZnU1WWtUSlJQVFROS0dCOWwiLCJvcmlnaW4iOiJodHRwczovL3Rlc3RmbGV4LmN5YmVyc291cmNlLmNvbSIsImp3ayI6eyJrdHkiOiJSU0EiLCJlIjoiQVFBQiIsInVzZSI6ImVuYyIsIm4iOiJnZDFCQ2M1TDh5UHNFa2dWc083TEZLVHpjTEJ3dVJkZDlNbjdEV1NPVzM1WmtNQ2hTZnFRU3d6aEZHclFtQlhhMmJpRzQ2QWV0YzE1UXVQSjVsQ19MWTFSdHNTbi03d3FwMVFyazV3WHZ6Z19YbHZCS1h2dUQtV0wwQ2djaXNLQml4TExQOF9qLVZJMHFDUVZjaktBN1BQcWc1em1oWi1oekxrVTZJbEJJQkRFNWJ0bDZkVWc1dlZBbW5GY1dtWFRFT0hqY0RTSGlvSVlmUTROcElWa25UaTJTbmFWM0Ridjk3UDJsd05XNHBoSktVLUt5cFUxMTNPTERjRmM4alJZcnpVRFlyN2dMQzhabzRvdHIybTY1M1pxaVN0SlY0ckx0QkxwNmhxQ0dVUkhBb3FWVjltanNLV0c1MXdoU2R6NWt6N3VaQWJ3a08yWnFHZGtETFZ1YlEiLCJraWQiOiIwOGdQM3h2SzVzeEhyUFBTNWZXRkZkWEx1MGhaYmFHNyJ9fSwiY3R4IjpbeyJkYXRhIjp7InRhcmdldE9yaWdpbnMiOlsiaHR0cDovL2xvY2FsaG9zdCJdLCJtZk9yaWdpbiI6Imh0dHBzOi8vdGVzdGZsZXguY3liZXJzb3VyY2UuY29tIn0sInR5cGUiOiJtZi0wLjExLjAifV0sImlzcyI6IkZsZXggQVBJIiwiZXhwIjoxNjMyMTU0MjY2LCJpYXQiOjE2MzIxNTMzNjYsImp0aSI6Im9WNkU2YU5YUVMyRG1Bb08ifQ.F6C4_ECStcBOMUf5Unc5-8ODMf1MsobJeLFFaBMNqkXgiNsWeob4CPmY9WlUP8dq4mUmkMIEAZZtX35r0OUilYaY2bPbxUNNmIQE9PEuTxNNvwqfN_uaq5gYz9ZV5CpxocjQeJVYDC73BKVlObwA4ZKpal0eCeVy5SG5M-q4z8VDkib0YUD8QUBH6dgs1ucPTsNqo_YJW2nP3kJi4caVpQeTNmVHsX6CH-NTGYb76m1gdxbqrIP2AA55QVHuAtYZQqCzaCBk8KkEhJ2bhPNkrDXZSh1-NbLACzJuJyZO2K-b_NpzxxJlAqOtV5oRKeTp9Z7eMz7WN3uQ18SGdQsxBw";
var paymentInstrumentUsed = false;  // Only true if user selects the text box
var standAlone = false;
var local = true;
var customerId ;
var shippingAddressRequired = true;
var paymentInstrumentId = "";
var shippingAddressId = "";
var flexToken;
var pan;
var storeCard = false;
// Order details Object. Store details submitted on index.php, for use in the various Steps.
let orderDetails = {
    referenceNumber: "PayPage6148ae5b3af98",
    amount: "63.99",
    currency: "GBP",
    shippingAddressRequired: shippingAddressRequired,
    useShippingAsBilling: false,
    ship_to: {
        ship_to_forename: "",
        ship_to_surname: "",
        ship_to_address_line1: "",
        ship_to_address_line2: "",
        ship_to_address_city: "",
        ship_to_postcode: "",
        ship_to_address_country: ""
    },
    bill_to: {
        bill_to_forename: "",
        bill_to_surname: "",
        bill_to_email: "",
        bill_to_address_line1: "",
        bill_to_address_line2: "",
        bill_to_address_city: "",
        bill_to_postcode: "",
        bill_to_address_country: ""
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
var flex;
var microform;
var number;
var securityCode;
var payButton;
//    var errorsOutput;
var panValid;
var cvnValid;
var secCodeLbl;
var form;
var maskedPan;
var numberContainer;
var expYear;
var expMonth;
var paymentInstruments
;console.log(paymentInstruments);
document.addEventListener("DOMContentLoaded", function (e) {
    flex = new Flex(captureContext);
    microform = flex.microform({styles: myStyles});
    number = microform.createField('number', {placeholder: 'Card number'});
    securityCode = microform.createField('securityCode', {placeholder: '•••'});
    payButton = document.querySelector('button');
//        errorsOutput = document.querySelector('#errors-output');
    numberContainer = document.querySelector('#number-container');
    expYear = document.querySelector('#card_expirationYear');
    expMonth = document.querySelector('#card_expirationMonth');
    panValid = false;
    cvnValid = false;

    if (customerId) {
        showPanField(false);
        cardRadioClicked();
    } else {
        showPanField(true);
    }
    securityCode.load('#securityCode-container');
    secCodeLbl = document.querySelector('#mySecurityCodeLabel');

    console.log("\ncaptureContext:\n" + captureContext);

    form = document.querySelector('#payment_form');
    number.on('change', function (data) {
        console.log(data);
        secCodeLbl.textContent = (data.card && data.card.length > 0) ? data.card[0].securityCode.name : 'CVN';
        panValid = data.valid;
        fieldsValid(panValid);
    });
    number.on('autocomplete', function (data) {
        console.log(data);
        if (data.expirationMonth) {
            document.getElementById('card_expirationMonth').value = data.expirationMonth;
        }
        if (data.expirationYear) {
            document.getElementById('card_expirationYear').value = parseInt(data.expirationYear) - 2000;
        }
    });
    securityCode.on('change', function (data) {
        cvnValid = data.valid;
        fieldsValid(cvnValid);
    });
    expYear.addEventListener('change', (event) => {
        fieldsValid(true);
    });
    expMonth.addEventListener('change', (event) => {
        fieldsValid(true);
    });
    payButton.addEventListener('click', (event) => {
        if (billingFieldsValid()) {
            payNow();
        }
    });
});
function cancel() {
    onFinish("CANCELLED", 0, false, "", "n/a", "User Cancelled", "");
    window.location.assign("index.php");
}
function billingFieldsValid() {
    ret = true;
    if(shippingAddressRequired){
        orderDetails.useShippingAsBilling = shipAsBill();
        form = document.getElementById('shippingForm');
        ret = validateForm(form);
    }
    if(ret){
        if(!shippingAddressRequired || !orderDetails.useShippingAsBilling){
            form = document.getElementById('billingForm');
            ret = validateForm(form);
        }
    }
    return ret;
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
function payNow() {
    if (paymentInstrumentUsed) {
        var options = {
        };
    } else {
        var options = {
            expirationMonth: getMonth(),
            expirationYear: 20 + document.getElementById('card_expirationYear').value
        };
    }
    microform.createToken(options, function (err, jwt) {
        if (err) {
            // handle error
            console.log(err);
            onFinish("FLEXERROR", "", false,"", "", err.reason);
        } else {
            // At this point you may pass the token back to your server as you wish.
//          console.log( "\nGot Token:\n" + jwt);
            if (!paymentInstrumentUsed) {
                maskedPan = getPAN(jwt);
            }
//          console.log( "\nGot PAN:" + pan);
            flexToken = getJTI(jwt);
            // IF storeCard checked, we will create a Token
            let sc = document.getElementById('storeCard');
            if (sc.checked) {
                storeCard = true;
            }
            setUpPayerAuth();
        }
        document.getElementById("resultSection").style.display = "block";
        document.getElementById('paymentDetailsSection').style.display = "none";
    });
}
function getMonth() {
    months = ["01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12"];
    m = parseInt(document.getElementById('card_expirationMonth').value);
    // turn 1,2,3,4 into "01","02","03", etc
    return(months[m - 1]);
}
function fieldsValid(valid) {
    if (!valid || !expiryDateValid() || !(panValid && cvnValid)) {
        // Check PAN and CVN both Populated and valid
        payButton.disabled = true;
        return false;
    }
    payButton.disabled = false;
    return true;
}
function getJTI(jwt) {
    jti = getPayload(jwt).jti;
//  console.log("JTI:" + jti);
    return (jti);
}
function getPAN(jwt) {
    pan = getPayload(jwt).data.number;
    // console.log("PAN:" + pan);
    return (pan);
}
function getPayload(jwt) {
    jwtArray = jwt.split(".");
    payloadB64 = jwtArray[1];
    payload = window.atob(payloadB64);
    payloadJ = JSON.parse(payload);
//  console.log(payloadJ);
    return payloadJ;
}
function cardRadioClicked() {
    index = document.querySelector('input[name="cardRadio"]:checked').value;
    if (index === "new") {
        paymentInstrumentUsed = false;
        paymentInstrumentId = "";
        showPanField(true);
    } else {
//        setBillingFields(index);
        paymentInstrumentId = paymentInstruments[index].id;
        maskedPan = paymentInstruments[index]._embedded.instrumentIdentifier.card.number;
        if (!paymentInstrumentUsed) {
            paymentInstrumentUsed = true;
            showPanField(false);
        }
    }
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
function setBillingFields(index) {
    document.getElementById('bill_to_forename').value = paymentInstruments[index].billTo.firstName;
    document.getElementById('bill_to_surname').value = paymentInstruments[index].billTo.lastName;
    document.getElementById('bill_to_email').value = paymentInstruments[index].billTo.email;
    document.getElementById('bill_to_address_line1').value = paymentInstruments[index].billTo.address1;
    document.getElementById('bill_to_address_line2').value = paymentInstruments[index].billTo.address2;
    document.getElementById('bill_to_address_city').value = paymentInstruments[index].billTo.locality;
    document.getElementById('bill_to_postcode').value = paymentInstruments[index].billTo.postalCode;
    document.getElementById('bill_to_address_country').value = paymentInstruments[index].billTo.country;
}
function showPanField(show) {
    if (show) {
        panValid = false;
        document.getElementById('cardDetailsSection').style.display = "block";
        document.getElementById('storeCardSection').style.display = "block";
        if (!number._loaded) {
            number.load('#number-container');
        }
    } else {
        panValid = true;
        document.getElementById('cardDetailsSection').style.display = "none";
        document.getElementById('storeCardSection').style.display = "none";
        if (number._loaded) {
            number.unload();
        }
    }
}
function expiryDateValid() {
    d = new Date();
    todayYear = d.getFullYear();
    todayMonth = d.getMonth();
    expMonth = parseInt(document.getElementById('card_expirationMonth').value);
    expYear = 2000 + parseInt(document.getElementById('card_expirationYear').value);
    if (expYear < todayYear || (expYear === todayYear && expMonth < todayMonth) || expMonth > 12 || expMonth < 1) {
        document.getElementById('card_expirationMonth').setCustomValidity('poopants');
        return false;
    }
    return true;
}
function setOrderDetails() {
    if(shippingAddressRequired && shippingAddressId === ""){
        orderDetails.useShippingAsBilling = shipAsBill();

        orderDetails.ship_to.ship_to_forename = document.getElementById('ship_to_forename').value;
        orderDetails.ship_to.ship_to_surname = document.getElementById('ship_to_surname').value;
        orderDetails.ship_to.ship_to_address_line1 = document.getElementById('ship_to_address_line1').value;
        orderDetails.ship_to.ship_to_address_line2 = document.getElementById('ship_to_address_line2').value;
        orderDetails.ship_to.ship_to_address_city = document.getElementById('ship_to_address_city').value;
        orderDetails.ship_to.ship_to_postcode = document.getElementById('ship_to_postcode').value;
        orderDetails.ship_to.ship_to_address_country = document.getElementById('ship_to_address_country').value;
    }
    if(paymentInstrumentId === ""){
        if(!orderDetails.useShippingAsBilling){
            orderDetails.bill_to.bill_to_forename = document.getElementById('bill_to_forename').value;
            orderDetails.bill_to.bill_to_surname = document.getElementById('bill_to_surname').value;
            orderDetails.bill_to.bill_to_address_line1 = document.getElementById('bill_to_address_line1').value;
            orderDetails.bill_to.bill_to_address_line2 = document.getElementById('bill_to_address_line2').value;
            orderDetails.bill_to.bill_to_address_city = document.getElementById('bill_to_address_city').value;
            orderDetails.bill_to.bill_to_postcode = document.getElementById('bill_to_postcode').value;
            orderDetails.bill_to.bill_to_address_country = document.getElementById('bill_to_address_country').value;
        }
        orderDetails.bill_to.bill_to_email = document.getElementById('bill_to_email').value;
    }
}
function setUpPayerAuth() {
    setOrderDetails();
    $.ajax({
        type: "POST",
        url: "rest_setup_payerAuth.php",
        data: JSON.stringify({
            "order": orderDetails,
            "paymentInstrumentProvided": paymentInstrumentUsed,
            "customerId": customerId,
            "paymentInstrumentId": paymentInstrumentId,
            "transientToken": flexToken
        }),
        success: function (result) {
            res = JSON.parse(result);
            console.log("\nSetup Payer Auth:\n" + JSON.stringify(res, undefined, 2));
            // If OK, set up device collection
            let httpCode = res.httpCode;
            if (httpCode === "201") {
                // Set up device collection
                deviceDataCollectionURL = res.response.consumerAuthenticationInformation.deviceDataCollectionUrl;
                accessToken = res.response.consumerAuthenticationInformation.accessToken;
                doDeviceCollection(deviceDataCollectionURL, accessToken);
            } else {
                // 500 System error or anything else
                onFinish(status, "", false, "", httpCode, res.response.reason, res.response.message);
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
            "paymentInstrumentProvided": paymentInstrumentUsed,
            "customerId": customerId,
            "paAction": paAction,
            "paymentInstrumentId": paymentInstrumentId,
            "shippingAddressId": shippingAddressId,
            "transientToken": flexToken,
            "referenceID": dfReferenceId,
            "authenticationTransactionID": authenticationTransactionID,
            "standAlone": standAlone
        }),
        success: function (result) {
            // Response is a json string - turn it into a javascript object
            let res = JSON.parse(result);
            console.log("\nEnrollment:\n" + JSON.stringify(res, undefined, 2));
            let httpCode = res.httpCode;
            let status = res.response.status;
            if (httpCode === "201") {
                tokenCreated = false;
                let requestID = res.response.id;
                // Successfull response (but could be declined)
                if (status === "PENDING_AUTHENTICATION") {
                    // Card is enrolled - Kick off the cardholder authentication
                    showStepUpScreen(res.response.consumerAuthenticationInformation.stepUpUrl, res.response.consumerAuthenticationInformation.accessToken);
                } else if (status === "AUTHORIZED") {
                    if (storeCard) {
                        tokenCreated = true;
                        paymentInstrumentId = res.response.tokenInformation.paymentInstrument.id;
                        if(shippingAddressRequired){
                            shippingAddressId = res.response.tokenInformation.shippingAddress.id;
                        }
                        if (!customerId) {
                            // New Customer
                            customerId = res.response.tokenInformation.customer.id;
                        }
                    }
                    onFinish(status, requestID, tokenCreated, shippingAddressId, httpCode);
                } else {
                    onFinish(status, requestID, tokenCreated, "", httpCode, res.response.reason, res.response.message);
                }
            } else {
                // 500 System error or anything else
                onFinish(status, "", false, "", httpCode, res.response.reason, res.response.message);
            }
        }
    });
}
function showStepUpScreen(stepUpURL, jwt) {
    on();
    // console.log( "Challenge Screen:\n"+stepUpURL);
    document.getElementById('step_up_iframe').style.display = "block";
    document.getElementById('step_up_form').action = stepUpURL;
    document.getElementById('step_up_form_jwt_input').value = jwt;
    var stepUpForm = document.getElementById('step_up_form');
    if (stepUpForm)
        stepUpForm.submit();
}
function hideStepUpScreen(transactionId) {
    off();
    console.log("Challenge Complete TransactionId:\n" + transactionId);
    document.getElementById('step_up_iframe').style.display = "none";
    authorizeWithPA("", transactionId, "VALIDATE_CONSUMER_AUTHENTICATION");
}
function on() {
    document.getElementById("overlay").style.display = "block";
}
function off() {
    document.getElementById("overlay").style.display = "none";
}
function onFinish(status, requestId, tokenCreated, shippingAddressId, httpResponseCode, errorReason, errorMessage) {
    finish = "onFinish: " + JSON.stringify({
        "referenceNumber": orderDetails.referenceNumber,
        "status": status,
        "httpResponseCode": httpResponseCode,
        "requestId": requestId,
        "amount": orderDetails.amount,
        "pan": maskedPan,
        "tokenCreated": tokenCreated,
        "paymentInstrumentProvided": paymentInstrumentUsed,
        "customerId": customerId,
        "paymentInstrumentId": paymentInstrumentId,
        "shippingAddressId": shippingAddressId,
        "errorReason": errorReason,
        "errorMessage": errorMessage
    });
    console.log(finish);
    if (status === "AUTHORIZED") {
        text = "Thank you.  Your payment has completed" + "<BR>" + finish;
    } else {
        text = "Oh dear. Your payment was not approved.  You can try again or try a different payment method" + "<BR>" + finish;
        document.getElementById("retryButton").style.display = "block";
    }
    result = document.getElementById("result").innerHTML = text;
    result = document.getElementById("result").style.display = "block";
    document.getElementById("progressSpinner").style.display = "none";
    if(tokenCreated && !paymentInstrumentUsed){
        // Write new Customer Token to cookie
        document.cookie = "customerId=" + customerId;
    }
}
</script>

