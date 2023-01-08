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

function createCardInput(progressName, buttonName, cvvOnlyFlag=false, panOnlyFlag=false, cvvOnlyCardType){
    cvvOnly = cvvOnlyFlag;
    panOnly = panOnlyFlag;
    cardType = cvvOnlyCardType;
    getTokenButtonId = buttonName;
    progress = document.getElementById(progressName);

    document.getElementById("cardNumber").style.display = (cvvOnly?"none":"block");
    document.getElementById("cardDate").style.display = (cvvOnly?"none":"block");

    if(progress){
        progress.style.display = "none";
    }
    return getCaptureContext();
}
function getCaptureContext(){
    console.log("getCaptureContext");
    return fetch("/payPage/checkout/api/getCaptureContext.php", {
      method: "post"
    })
    .then((result) => result.text())
    .then((result) => setUpMicroform(result))
    .catch((error) => console.log("Capture Context ERROR"))
}
function setUpMicroform(captureContext){
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
            // console.log(data);
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
    return "OK";
}
function setUpPanField(){
    // Set up PAN field
    number = microform.createField('number', {placeholder: 'Card number'});
    numberContainer = document.querySelector('#number-container');
    number.load('#number-container');
    number.on('change', function (data) {
        // console.log(data);
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
        // console.log(data);
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
        // console.log(event.target.value);
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
                // securityCode.focus();
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
var callBackFunction;
function getToken(tokenCallBack) {
    console.log("getToken");
    callBackFunction = tokenCallBack;
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
            console.log("Status: "+err.status+". Reason: "+err.reason);
            getTokenButton.disabled = true;
            errorAlert.style.display = "block";
            getCaptureContext();
        } else {
            // Token received.
            cardDetails = getCardDetails(jwt);
            flexToken = getJTI(jwt);
            result = {
                "flexToken": flexToken,
                "cardDetails": cardDetails
            };
            tokenCallBack(result);
        }
    });
}
function fieldsValid() {
    // Check PAN and CVN both Populated and valid
    getTokenButton.disabled = (panValid && cvnValid && expDateValid)?false:true;
    if(!getTokenButton.disabled){
//        getTokenButton.focus();
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