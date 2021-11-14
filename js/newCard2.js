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
var maskedPan = "";
var cardType;
var flexToken;
var pan;
var storeCard = false;
var expDate;
var flex;
var microform;
var number;
var securityCode;
var newCardButton;
var panValid=false;
var cvnValid=false;
var secCodeLbl;
var numberContainer;
var tokenCreatedCallback;
var tokenErrorCallback;
var cvvOnly = false;

function setUpMicroform(cardButtonName){
    flex = new Flex(captureContext);
    microform = flex.microform({styles: myStyles});
    if(cvvOnly){
        panValid = true;
    }else{
        number = microform.createField('number', {placeholder: 'Card number'});
        numberContainer = document.querySelector('#number-container');
        number.load('#number-container');
        number.on('change', function (data) {
//        console.log(data);
            // Set "CVV" text with name based on scheme
            secCodeLbl.textContent = (data.card && data.card.length > 0) ? data.card[0].securityCode.name : 'CVN';
            if(data.card && data.card.length > 0){
                updateSecurityCodeField(data.card[0].cybsCardType);
            }
            panValid = data.valid;
            fieldsValid(panValid);
        });
        number.on('autocomplete', function (data) {
    //        console.log(data);
            let expDate = "";
            if (data.expirationMonth) {
                expDate = data.expirationMonth + "/";
            }
            if (data.expirationYear) {
                expdate += (parseInt(data.expirationYear) - 2000);
            }
            document.getElementById('expiryDate').value = expDate;
        });
    }
    securityCode = microform.createField('securityCode', {placeholder: "•••", maxLength: 3});
    newCardButton = document.querySelector('#'+cardButtonName);

    securityCode.load('#securityCode-container');
    secCodeLbl = document.querySelector('#securityCodeLabel');
    securityCode.on('change', function (data) {
//        console.log(data);
        cvnValid = data.valid;
        fieldsValid(cvnValid);
    });
}
function updateSecurityCodeField(type){
    // If Amex, CVVis 4 digits, else 3
    if(type === "003"){
        securityCode.update({placeholder: "••••", maxLength: 4});
    }else{
        securityCode.update({placeholder: "•••", maxLength: 3});
    }
}
function getToken(tokenCallback, errorCallback) {
    tokenCreatedCallback = tokenCallback;
    tokenErrorCallback = errorCallback;
    var options = {};
    if(!cvvOnly){
        var options = {
            expirationMonth: document.getElementById('expiryDate').value.substring(0,2),
            expirationYear: 20 + document.getElementById('expiryDate').value.substring(3,5)
        };
    }
    microform.createToken(options, function (err, jwt) {
        if (err) {
            // handle error
            console.log(err);
            tokenErrorCallback(err);
        } else {
            // Token received.
//            console.log( "\nGot Token:\n" + jwt);
            cardDetails = getCardDetails(jwt);
            flexToken = getJTI(jwt);
            return tokenCreated(flexToken, cardDetails);
        }
    });
}
function fieldsValid(valid) {
    if (!valid || (!cvvOnly && !expiryDateValid()) || !(panValid && cvnValid)) {
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
function getCardDetails(jwt) {
    return getPayload(jwt).data;
}
function getPayload(jwt) {
    jwtArray = jwt.split(".");
    payloadB64 = jwtArray[1];
    payload = window.atob(payloadB64);
    payloadJ = JSON.parse(payload);
//  console.log(payloadJ);
    return payloadJ;
}
function getCaptureContext(local, cardButtonName) {
    $.ajax({
        type: "POST",
        url: "rest_generate_capture_context.php",
        data: JSON.stringify({
            "local": local
        }),
        success: function (result) {
            res = JSON.parse(result);
//            console.log("\nCapture Context:\n" + JSON.stringify(res, undefined, 2));
            let httpCode = res.responseCode;
            if (res.responseCode === 200) {
                captureContext = res.response.keyId;
                setUpMicroform(cardButtonName);
            } else {
                // 500 System error or anything else TODO
            }
        }
    });
}
function tokenCreated(flexToken, cardDetails){
    // document.getElementById('paymentDetailsSection').style.display = "none";
    result = {
        "flexToken": flexToken,
        "cardDetails": cardDetails
    };
    tokenCreatedCallback(result);
}
function createCardInput(containerName, progressName, cardButtonName, cvvOnlyFlag=false){
    cvvOnly = cvvOnlyFlag;
    container = document.getElementById(containerName);
    progress = document.getElementById(progressName);
    html = "<div class=\"d-flex mt-3 mb-3\">"+
        (!cvvOnly?
        "<div class=\"card\">"+
            "<div class=\"card-body\"> "+
                "<div class=\"d-flex align-items-center justify-content-between\">"+
                    "<div>"+
                        "<img src=\"images/Visa.svg\" width=\"30\">"+
                        "<img src=\"images/Mastercard.svg\" width=\"30\">"+
                        "<img src=\"images/Amex.svg\" width=\"30\">"+
                    "</div>"+
                "</div>"+
                "<div class=\"row mt-3 mb-3\">"+
                    "<div class=\"col-md-12\">"+
                        "<label class=\"form-check-label\" for=\"number-container\">Card Number</label>"+
                        "<div class=\"cardInput\">"+
                            "<i class=\"fa fa-credit-card\"></i>"+
                            "<div id=\"number-container\" class=\"form-control\"></div>"+
                        "</div>"+
                    "</div>"+
                "</div>":"")+
                "<div class=\"row mt-3 mb-3\">"+
                    (!cvvOnly?
                    "<div class=\"col-md-6\">"+
                        "<label class=\"form-check-label\" for=\"expiryDate\">Expiry Date</label>"+
                        "<div class=\"cardInput\">"+
                            "<i class=\"fa fa-calendar\"></i>"+
                            "<input class=\"form-control\" id=\"expiryDate\" type=\"text\" placeholder=\"MM/YY\" pattern=\"[0-1][0-9]\/[2][1-9]\" inputmode=\"numeric\" autocomplete=\"cc-exp\" autocorrect=\"off\" spellcheck=\"off\" aria-invalid=\"false\" aria-placeholder=\"MM/YY\" required>"+
                        "</div>"+
                    "</div>":"")+
                    "<div class=\"col-md-6\"> "+
                        "<label id=\"securityCodeLabel\" class=\"form-check-label\" for=\"securityCode-container\">Security Code</label>"+
                        "<div class=\"cardInput\">"+
                            "<i class=\"fa fa-lock\"></i>"+
                            "<div id=\"securityCode-container\" class=\"form-control\"></div>"+
                        "</div>"+
                    "</div>"+
                "</div>"+
            (!cvvOnly?
            "</div>"+
        "</div>":"")+
    "</div>";
    if(container){
        container.innerHTML = html;
        
        if(progress){
            progress.style.display = "none";
        }
        getCaptureContext(window.location.href.includes("localhost")?true:false, cardButtonName);
        if(!cvvOnly){
            setUpExpiryDate("expiryDate");
        }
    }
}
function expiryDateValid() {
    d = new Date();
    todayYear = d.getFullYear();
    todayMonth = d.getMonth();
    xMonth = parseInt(expDate.value.substring(0,2));
    xYear = 2000 + parseInt(expDate.value.substring(3,5));
    if (xYear < todayYear || (xYear === todayYear && xMonth < todayMonth) || xMonth > 12 || xMonth < 1) {
        return false;
    }
    return true;
}
function setUpExpiryDate(id){
    expDate = document.getElementById(id);
    expDate.addEventListener('input',(event)=>{
        const val = event.target.value.toString();
        // If last char is invalid - ignore it
        myChar = val.charAt(val.length-1);
        if(myChar<'0' || myChar>'9'){
            // Ignore invalid characters
            if(val === "1/"){
                // If its a slash following 1 insert the leading zero
                event.target.value = "01/"
            }else {
                //Ignore
                event.target.value = val.substring(0, val.length - 1);
            }
            return;
        }
        if(val.length>5){
            //Ignore
            event.target.value = val.substring(0, val.length - 1);
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
        }

    });
    expDate.addEventListener('keydown',(event)=> {
        const val = event.target.value.toString();
        if(event.key === "Backspace"){
            if (event.target.selectionStart === 3 ){
                event.target.value = val.substring(0, 2);
            }else if(event.target.selectionStart === 4){
                event.target.value = val.substring(0, 3);
            }
        }
    });
}