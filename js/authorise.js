function setUpPayerAuth(){
    console.log("setUpPayerAuth");
    document.getElementById('authSpinner').style.display = "block";
    return fetch("api/setupPayerAuth.php", {
        method: "post",
        body: JSON.stringify({
            "order" : orderDetails
        })
    })
    .then(response =>{
        if (!response.ok) {
            throw Error(response.statusText);
        }
        return response.json();
    })
    .then(res => {
        doDeviceCollection(res.consumerAuthenticationInformation.deviceDataCollectionUrl, 
            res.consumerAuthenticationInformation.accessToken);
    })
    .catch(error =>{
        onFinish2("SETUPPA", "FAILED", "", false, false, false, "n/a", 
                "SetupPa Failed", error);
    })
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
        }else{
            // ERROR - Try Authorizing without PA
            console.log("Error with PA:" + data.SessionId);
            authorizeWithPA(data.SessionId, "", "NO_PA");
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
    console.log("\nAuthorizing +" + paAction + " ...\n");
    let httpCode = 0;
    return fetch("api/authWithPa.php", {
        method: "post",
        body: JSON.stringify({
            "order": orderDetails,
            "paAction": paAction,
            "referenceID": dfReferenceId,
            "authenticationTransactionID": authenticationTransactionID
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
        console.log(res);
        let status = res.status;
        let customerCreated = false;
        let paymentInstrumentCreated = false;
        let shippingAddressCreated = false;
        // Successfull response (but could be declined)
        if (status === "PENDING_AUTHENTICATION") {
            // Card is enrolled - Kick off the cardholder authentication
            showStepUpScreen(res.consumerAuthenticationInformation.stepUpUrl, res.consumerAuthenticationInformation.accessToken);
        } else if (status === "AUTHORIZED") {
            if (orderDetails.storeCard || orderDetails.storeAddress) {
                if(orderDetails.storeCard){
                    paymentInstrumentCreated = true;
                    orderDetails.paymentInstrumentId = res.tokenInformation.paymentInstrument.id;
                }
                if(orderDetails.storeAddress){
                    shippingAddressCreated = true;
                    orderDetails.shippingAddressId = res.tokenInformation.shippingAddress.id;
                }
                if (!orderDetails.customerId) {
                    // New Customer
                    customerCreated = true;
                    orderDetails.customerId = res.tokenInformation.customer.id;
                }
            }
            onFinish2("AUTH+"+paAction, status, res.id, customerCreated, paymentInstrumentCreated, shippingAddressCreated, httpCode, "", "");
        } else {
            // Decline
            onFinish2("AUTH+"+paAction, status, res.id, false, false, false,httpCode, res.errorInformation.reason, res.errorInformation.message);
        }
    })
    .catch(error =>{
        onFinish2("AUTH+"+paAction, "FAILED", "", false, false, false, httpCode, "Auth failed", error);
    })
}
function showStepUpScreen(stepUpURL, jwt) {
    // console.log( "Challenge Screen:\n"+stepUpURL);
    document.getElementById('step_up_form').action = stepUpURL;
    document.getElementById('step_up_form_jwt_input').value = jwt;
    document.getElementById("step_up_iframe").style.display = "block";
    document.getElementById("authMessage").style.display = "none";
    document.getElementById("authSpinner").style.display = "none";
    var stepUpForm = document.getElementById('step_up_form');
    if (stepUpForm){
        stepUpForm.submit();
    }
}
function hideStepUpScreen(transactionId) {
    console.log("Challenge Complete TransactionId:\n" + transactionId);
    document.getElementById("step_up_iframe").style.display = "none";
    document.getElementById("authMessage").style.display = "block";
    document.getElementById("authSpinner").style.display = "block";
    authorizeWithPA("", transactionId, "VALIDATE_CONSUMER_AUTHENTICATION");
}
function onFinish2(apiCalled, status, requestId, newCustomer, paymentInstrumentCreated, shippingAddressCreated, httpResponseCode, errorReason, errorMessage) {
    document.getElementById('authSection').style.display = "none";
    document.getElementById('inputSection').style.display = "none";
    document.getElementById('confirmSection').style.display = "none";
    let finish = {
        "orderId": orderDetails.orderId,
        "referenceNumber": orderDetails.referenceNumber,
        "amount": orderDetails.amount,
        "apiCalled": apiCalled,
        "httpResponseCode": httpResponseCode,
        "status": status,
        "requestId": requestId,
        "email": orderDetails.bill_to.email,
        "autoCapture": orderDetails.capture,
        "pan": orderDetails.maskedPan,
        "newCustomer": newCustomer,
        "newPaymentInstrument": paymentInstrumentCreated,
        "newShippingAddress": shippingAddressCreated,
        "customerId": orderDetails.customerId,
        "paymentInstrumentId": orderDetails.paymentInstrumentId,
        "shippingAddressId": orderDetails.shippingAddressId,
        "errorReason": errorReason,
        "errorMessage": errorMessage
    };
    console.log(JSON.stringify(finish, undefined, 2));
    if (status === "AUTHORIZED") {
        text = successHTML(finish);
        document.getElementById("retryButton").style.display = "none";
    } else {
        text = failHTML(finish);
    }
    document.getElementById("resultText").innerHTML = text;
    document.getElementById("resultSection").style.display = "block";
    if(newCustomer && !orderDetails.paymentInstrumentId !== ""){
        // Write new Customer Token to cookie
        document.cookie = "customerId=" + orderDetails.customerId;
    }
}
function successHTML(finish){
    template =
        "<h3>Thank you for your order.  Your payment was successful</h3><br>"+
        "<div class='row'><div class='col-4'>Order Reference</div><div class='col-8'>?mr?</div></div>"+
        "<div class='row'><div class='col-4'>Request ID</div><div class='col-8'>?requestId?</div></div><br>"
        ;
    html = template.replace("?mr?", finish.referenceNumber);
    html = html.replace("?requestId?", finish.requestId);
    return html;
}
function failHTML(finish){
    template =
        "<h3>Oh dear. Something is not working. Please check your internet connection and try again.</h3><br>"+
        "<div class='row'><div class='col-4'>Order Reference</div><div class='col-8'>?mr?</div></div>"+
        "<div class='row'><div class='col-4'>Request ID</div><div class='col-8'>?requestId?</div></div><br>"
        ;
    html = template.replace("?mr?", finish.referenceNumber);
    html = html.replace("?requestId?", finish.requestId);
    return html;
}
