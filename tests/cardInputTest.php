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
        <div id="progressSpinner"  class="spinner-border text-info" style="display: block;"></div>
        <div class="row">
            <div class="col-12">
                <input type="checkbox" class="form-check-input" onchange="cvvOnlyChanged()" id="cvvOnly" name="cvvOnly" value="1" checked="checked">
                <label for="cvvOnly" class="form-check-label">CVV Only</label>
            </div>
        </div>                            
        <div id="cardInput">
            <form>
                <div class="card">
                   <div class="card-body">
                      <div class="row">
                         <div id="cardError" class="alert alert-danger alert-dismissible fade show" role="alert" style="display:none"><strong>Something went wrong. Please try again.</strong></div>
                      </div>
                      <div id="cardNumber">
                         <div class="d-flex align-items-center justify-content-between">
                            <div><img src="../images/Visa.svg" width="30"><img src="../images/Mastercard.svg" width="30"><img src="../images/Amex.svg" width="30"></div>
                         </div>
                         <div class="row mt-3 mb-3">
                            <div class="col-12">
                               <label class="form-check-label" for="number-container">Card Number</label>
                               <div class="cardInput">
                                    <i class="fa fa-credit-card"></i>
                                    <div id="number-container" class="form-control flex-microform">
                                    </div>
                               </div>
                            </div>
                         </div>
                      </div>
                      <div class="row">
                         <div class="col-6" id="cardDate">
                            <label class="form-check-label" for="expiryDate">Expiry Date</label>
                            <div class="cardInput"><i class="fa fa-calendar"></i><input class="form-control" id="expiryDate" type="text" placeholder="MM/YY" pattern="[0-1][0-9]/[2][1-9]" inputmode="numeric" autocomplete="cc-exp" autocorrect="off" spellcheck="off" aria-invalid="false" aria-placeholder="MM/YY" required=""></div>
                         </div>
                         <div class="col-6">
                            <label id="securityCodeLabel" class="form-check-label" for="securityCode-container">Security Code</label>
                            <div class="cardInput">
                               <i class="fa fa-lock"></i>
                               <div id="securityCode-container" class="form-control flex-microform">
                               </div>
                            </div>
                         </div>
                      </div>
                   </div>
                </div>
                <div class="row mt-3 mb-3">
                    <div class="col-sm-6">
                    <button type="button" class="btn btn-primary" onclick="getToken()" id="payButton" disabled="true">Pay</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
</body>
<script src="https://flex.cybersource.com/cybersource/assets/microform/0.11/flex-microform.min.js"></script>
<script>
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
var captureContext;
var cardType;
var flexToken;
var expDate;
var flex;
var microform;
var number;
var securityCode;
var getTokenButtonId;
var getTokenButton;
var panValid;
var cvnValid;
var expDateValid;
var secCodeLbl;
var numberContainer;
var cvvOnly;
var panOnly;
var errorAlert;
var container;

document.addEventListener("DOMContentLoaded", function (e) {
    createCardInput("", "progressSpinner", "payButton", true, false,"" );
});
function flipCvvOnly(cvvOnlyFlag, type){
    createCardInput("", "progressSpinner", "payButton", cvvOnlyFlag, false, type );
    return;
}
function createCardInput(containerName, progressName, buttonName, cvvOnlyFlag=false, panOnlyFlag=false, cvvOnlyCardType){
    cvvOnly = cvvOnlyFlag;
    panOnly = panOnlyFlag;
    cardType = cvvOnlyCardType;
    getTokenButtonId = buttonName;
    container = document.getElementById(containerName);
    progress = document.getElementById(progressName);

    document.getElementById("cardNumber").style.display = (cvvOnly?"none":"block");
    document.getElementById("cardDate").style.display = (cvvOnly?"none":"block");

    if(progress){
        progress.style.display = "none";
    }
    getCaptureContext(window.location.href.includes("localhost")?true:false);
}
function getCaptureContext(local) {
    $.ajax({
        type: "POST",
        url: "../rest_generate_capture_context.php",
        data: JSON.stringify({
            "local": local
        }),
        success: function (result) {
            res = JSON.parse(result);
//            console.log("\nCapture Context:\n" + JSON.stringify(res, undefined, 2));
            let httpCode = res.responseCode;
            if (httpCode === 201) {
                captureContext = res.rawResponse;
                setUpMicroform();
            } else {
                // 500 System error or anything else TODO
                console.log("Capture Context ERROR");
            }
        }
    });
}
function setUpMicroform(){
    panValid=false;
    cvnValid=false;
    expDateValid=false;
    flex = new Flex(captureContext);
    microform = flex.microform({styles: myStyles});
    if(cvvOnly){
        expDateValid = true;
        panValid = true;
    }else{
        // Set up PAN field
        setUpPanField();
    }
    if(panOnly){
        cvnValid = true;
    }else{
        if(cardType === "003" || cardType === "american express"){
            placeholder = "••••";
            maxLength = 4;
        }else{
            placeholder =  "•••";
            maxLength = 3;
        }
        securityCode = microform.createField('securityCode', {placeholder: placeholder, maxLength: maxLength});
        securityCode.load('#securityCode-container');
        secCodeLbl = document.querySelector('#securityCodeLabel');
        securityCode.on('change', function (data) {
            console.log(data);
            cvnValid = data.valid;
            fieldsValid();
        });
        securityCode.on('inputSubmitRequest', function() {
            fieldsValid();
        });

    }
    setUpExpiryDate("expiryDate");
    getTokenButton = document.querySelector('#'+getTokenButtonId);
    errorAlert = document.getElementById("cardError");
}
function setUpPanField(){
    // Set up PAN field
    number = microform.createField('number', {placeholder: 'Card number'});
    numberContainer = document.querySelector('#number-container');
    number.load('#number-container');
    number.on('change', function (data) {
        console.log(data);
        if(!panOnly){
            // Set "CVV" text with name based on scheme
            secCodeLbl.textContent = (data.card && data.card.length > 0) ? data.card[0].securityCode.name : 'CVN';
            if(data.card && data.card.length > 0){
                updateSecurityCodeField(data.card[0].cybsCardType);
            }
        }
        panValid = data.valid;
        fieldsValid();
    });
    number.on('autocomplete', function (data) {
        console.log(data);
        let xDate = "";
        if (data.expirationMonth) {
            xDate = data.expirationMonth + "/";
        }
        if (data.expirationYear) {
            d=parseInt(data.expirationYear);
            if(d>2000){
                d-=2000;
            }
            xDate += d;
        }
        expDate.value = xDate;
        expDateValid = expiryDateValid();
        fieldsValid();
    });
    number.on('inputSubmitRequest', function() {
        expDate.focus();
    });
}
function updateSecurityCodeField(type){
    // If Amex, CVV is 4 digits, else 3
    if(type === "003" || type === "amex"){
        securityCode.update({placeholder: "••••", maxLength: 4});
    }else{
        securityCode.update({placeholder: "•••", maxLength: 3});
    }
}
function setUpExpiryDate(id){
    expDate = document.getElementById(id);
    expDate.value="";
    expDate.addEventListener('input',dateInput);
    expDate.addEventListener('keydown',dateKeyDown);
}
function expiryDateValid() {
    if(expDate.value.length<5){
        return false;
    }
    d = new Date();
    todayYear = d.getFullYear();
    todayMonth = d.getMonth()+1;
    xMonth = parseInt(expDate.value.substring(0,2));
    xYear = 2000 + parseInt(expDate.value.substring(3,5));
    if (xYear < todayYear || (xYear === todayYear && xMonth < todayMonth) || xMonth > 12 || xMonth < 1) {
        return false;
    }
    return true;
}
function dateInput(event){
        console.log(event.target.value);
        const val = event.target.value.toString();
        // If last char is invalid - ignore it
        myChar = val.charAt(val.length-1);
        if(myChar<'0' || myChar>'9'){
            // Ignore invalid characters
            if(val === "1/"){
                // If its a slash following 1 insert the leading zero
                event.target.value = "01/";
            }else {
                //Ignore
                event.target.value = val.substring(0, val.length - 1);
            }
            return;
        }
        if(val.length>5){
            if(val.length===7){
                // Could be a auutocomplete in MM/YYYY format
                event.target.value = val.substring(0,2)+"/"+val.substring(5,7);
            }else{
                //Ignore
                event.target.value = val.substring(0, val.length - 1);
            }
        }
        switch (val.length) {
            case 0:
                break;
            case 1:
                if(val > 1){
                    event.target.value = "0"+ val+"/";
                }
                break;
            case 2:
                if((val>12)){
                    event.target.value = "12" +"/";
                }else if(val<1) {
                    event.target.value = "01" + "/";
                }else{
                    event.target.value = val + "/";
                }
                break;
            case 3:
                break;
            case 5:
                // Move focus to CVV input
                securityCode.focus();
                break;
        }
        expDateValid = expiryDateValid();
        fieldsValid();
}
function dateKeyDown(event){
    const val = event.target.value.toString();
    if(event.key === "Backspace"){
        if (event.target.selectionStart === 3 ){
            event.target.value = val.substring(0, 2);
        }else if(event.target.selectionStart === 4){
            event.target.value = val.substring(0, 3);
        }
    }else if(event.key === "Enter"){
        event.preventDefault();
        event.stopPropagation();
        securityCode.focus();
    }  
}
function getToken() {
    errorAlert.style.display = "none";
    var options = {};
    if(!cvvOnly){
        var options = {
            expirationMonth: expDate.value.substring(0,2),
            expirationYear: 20 + expDate.value.substring(3,5)
        };
    }
    microform.createToken(options, function (err, jwt) {
        if (err) {
            // handle error.  Probably a timeout. Start again
            console.log(err);
            console.log("Status: "+err.status+". Reason: "+err.reason);
            getTokenButton.disabled = true;
            errorAlert.style.display = "block";
            getCaptureContext(window.location.href.includes("localhost")?true:false);
        } else {
            // Token received.
            console.log( "\nGot Token:\n" + jwt);
            if(cvvOnly){
                console.log( "\nGot Token (CVV)\n");
            }else{
                cardDetails = getCardDetails(jwt);
                console.log( "\nGot Token:\n" + cardDetails.number +"\nExp Date: "+cardDetails.expirationMonth+"/"+cardDetails.expirationYear);
            }
            flexToken = getJTI(jwt);
        }
    });
}
function fieldsValid() {
    // Check PAN and CVN both Populated and valid
    getTokenButton.disabled = (panValid && cvnValid && expDateValid)?false:true;
    if(!getTokenButton.disabled){
        getTokenButton.focus();
    }
}
function getJTI(jwt) {
    jti = getPayload(jwt).jti;
//  console.log("JTI:" + jti);
    return (jti);
}
function getCardDetails(jwt) {
    return getPayload(jwt).data;
}
function getPayload(jwt) {
    jwtArray = jwt.split(".");
    payloadB64 = jwtArray[1];
    payloadJ = window.atob(payloadB64);
    payload = JSON.parse(payloadJ);
//    console.log(payload);
    return payload;
}
function cvvOnlyChanged(){
    xxx = document.querySelector('#cvvOnly');
    flipCvvOnly(xxx.checked, "003");
}
</script>
</html>
