newCardButton// the capture context that was requested server-side for this transaction
var captureContext;
var maskedPan = "";
var flexToken;
var pan;
var storeCard = false;
// Order details Object. Store details submitted on index.php, for use in the various Steps.
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
var newCardButton;
var panValid;
var cvnValid;
var secCodeLbl;
var numberContainer;
document.addEventListener("DOMContentLoaded", function (e) {
    getCaptureContext(true);
});
function setUpMicroform(){
    flex = new Flex(captureContext);
    microform = flex.microform({styles: myStyles});
    number = microform.createField('number', {placeholder: 'Card number'});
    securityCode = microform.createField('securityCode', {placeholder: "•••", maxLength: 3});
    newCardButton = document.querySelector('#newCardButton');
    numberContainer = document.querySelector('#number-container');
    panValid = false;
    cvnValid = false;

    number.load('#number-container');
    securityCode.load('#securityCode-container');
    secCodeLbl = document.querySelector('#securityCodeLabel');

    console.log("\ncaptureContext:\n" + captureContext);

    number.on('change', function (data) {
        console.log(data);
        // Set "CVV" text with name based on scheme
        secCodeLbl.textContent = (data.card && data.card.length > 0) ? data.card[0].securityCode.name : 'CVN';
        if(data.card && data.card.length > 0){
            updateSecurityCodeField(data.card[0].cybsCardType);
        }
        panValid = data.valid;
        fieldsValid(panValid);
    });
    number.on('autocomplete', function (data) {
        console.log(data);
        let expDate = "";
        if (data.expirationMonth) {
            expDate = data.expirationMonth + "/";
        }
        if (data.expirationYear) {
            expdate += (parseInt(data.expirationYear) - 2000);
        }
        document.getElementById('expiryDate').value = expDate;
    });
    securityCode.on('change', function (data) {
        console.log(data);
        cvnValid = data.valid;
        fieldsValid(cvnValid);
    });
}
function tokenizeCard(){
    if(validateForm(document.getElementById('billingForm'))){
        return getToken();
    }
}
function updateSecurityCodeField(type){
    // If Amex, CVVis 4 digits, else 3
    if(type === "003"){
        securityCode.update({placeholder: "••••", maxLength: 4});
    }else{
        securityCode.update({placeholder: "•••", maxLength: 3});
    }
}
function cancel() {
    onFinish(true)
}

function getToken() {
    var options = {
        expirationMonth: document.getElementById('expiryDate').value.substring(0,2),
        expirationYear: 20 + document.getElementById('expiryDate').value.substring(3,5)
    };
    microform.createToken(options, function (err, jwt) {
        if (err) {
            // handle error
            console.log(err);
            // TODO
        } else {
            // At this point you may pass the token back to your server as you wish.
            console.log( "\nGot Token:\n" + jwt);
            maskedPan = getPAN(jwt);
            console.log( "\nGot PAN:" + pan);
            flexToken = getJTI(jwt);
            // IF storeCard checked, we will create a Token
            let sc = document.getElementById('storeCard');
            if (sc.checked) {
                storeCard = true;
            }
            return onFinish(false, storeCard, flexToken, maskedPan);
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
function fieldsValid(valid) {
    if (!valid || !expiryDateValid() || !(panValid && cvnValid)) {
        // Check PAN and CVN both Populated and valid
        newCardButton.disabled = true;
        return false;
    }
    newCardButton.disabled = false;
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
function getCaptureContext(local) {
    $.ajax({
        type: "POST",
        url: "rest_generate_capture_context.php",
        data: JSON.stringify({
            "local": local
        }),
        success: function (result) {
            res = JSON.parse(result);
            console.log("\nCapture Context:\n" + JSON.stringify(res, undefined, 2));
            let httpCode = res.responseCode;
            if (res.responseCode === 200) {
                captureContext = res.response.keyId;
                setUpMicroform();
            } else {
                // 500 System error or anything else TODO
            }
        }
    });
}
function onFinish(cancelled, storeCard, flexToken, maskedPan){
    // document.getElementById('paymentDetailsSection').style.display = "none";
    result = JSON.stringify({
        "cancelled": cancelled,
        "storeCard": storeCard,
        "flexToken": flexToken,
        "maskedPan": maskedPan,
        "bill_to_forename": (!cancelled?document.getElementById('bill_to_forename').value:""),
        "bill_to_surname": (!cancelled?document.getElementById('bill_to_surname').value:""),
        "bill_to_address_line1": (!cancelled?document.getElementById('bill_to_address_line1').value:""),
        "bill_to_address_line2": (!cancelled?document.getElementById('bill_to_address_line2').value:""),
        "bill_to_address_city": (!cancelled?document.getElementById('bill_to_address_city').value:""),
        "bill_to_postcode": (!cancelled?document.getElementById('bill_to_postcode').value:""),
        "bill_to_address_country": (!cancelled?document.getElementById('bill_to_address_country').value:"")
    });
    onNewCardReceived(result);
}
