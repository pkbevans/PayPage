function setUpPayerAuth(){
    document.getElementById('authSpinner').style.display = "block";
    $.ajax({
        type: "POST",
        url: "api/setupPayerAuth.php",
        data: JSON.stringify({
            "order": orderDetails
        }),
        success: function (result) {
            // Response is a json string - turn it into a javascript object
            let res = "";
            try{
                res = JSON.parse(result);
                console.log("\nSetup Payer Auth:\n" + JSON.stringify(res, undefined, 2));
            } catch(error){
                console.log("ERROR:"+ result);
                onFinish2("SETUPPA", "ERROR", "", false, false, false, "n/a", "JSON PARSE", result);
                return;
            }
            // If OK, set up device collection
            if (res.responseCode === 201){
                if( res.response.status === "COMPLETED") {
                    // Set up device collection
                    deviceDataCollectionURL = res.response.consumerAuthenticationInformation.deviceDataCollectionUrl;
                    accessToken = res.response.consumerAuthenticationInformation.accessToken;
                    doDeviceCollection(deviceDataCollectionURL, accessToken);
                }
//                else if("errorInformation" in res.response && res.response.errorInformation.reason === "EXPIRED_CARD" ){
//                    expiredCard();
//                }
                else{
                    onFinish2("SETUPPA", status, "", false, false, false, res.responseCode, res.response.errorInformation.reason, res.response.errorInformation.message);
                }
            }else if(res.responseCode === 400){
                onFinish2("SETUPPA", status, "", false, false, false, res.responseCode, res.response.reason, res.response.message);
            }else {
                // 500 System error or anything else
                onFinish2("SETUPPA", status, "", false, false, false, res.responseCode, res.response.errorInformation.reason, res.response.errorInformation.message);
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
    $.ajax({
        type: "POST",
        url: "api/authWithPa.php",
        data: JSON.stringify({
            "order": orderDetails,
            "paAction": paAction,
            "referenceID": dfReferenceId,
            "authenticationTransactionID": authenticationTransactionID
        }),
        success: function (result) {
            // Response is a json string - turn it into a javascript object
            let res = "";
            try{
                res = JSON.parse(result);
                console.log("\nResults:\n" + JSON.stringify(res, undefined, 2));
            } catch(error){
                console.log("ERROR:"+ result);
                onFinish2("AUTH+"+paAction, "ERROR", "", false, false, false, "n/a", "JSON PARSE", result);
                return;
            }
            let httpCode = res.responseCode;
            let status = res.response.status;
            if (httpCode === 201) {
                customerCreated = false;
                paymentInstrumentCreated = false;
                shippingAddressCreated = false;
                // Successfull response (but could be declined)
                if (status === "PENDING_AUTHENTICATION") {
                    // Card is enrolled - Kick off the cardholder authentication
                    showStepUpScreen(res.response.consumerAuthenticationInformation.stepUpUrl, res.response.consumerAuthenticationInformation.accessToken);
                } else if (status === "AUTHORIZED") {
                    if (orderDetails.storeCard || orderDetails.storeAddress) {
                        if(orderDetails.storeCard){
                            paymentInstrumentCreated = true;
                            orderDetails.paymentInstrumentId = res.response.tokenInformation.paymentInstrument.id;
                        }
                        if(orderDetails.storeAddress){
                            shippingAddressCreated = true;
                            orderDetails.shippingAddressId = res.response.tokenInformation.shippingAddress.id;
                        }
                        if (!orderDetails.customerId) {
                            // New Customer
                            customerCreated = true;
                            orderDetails.customerId = res.response.tokenInformation.customer.id;
                        }
                    }
                    onFinish2("AUTH+"+paAction, status, res.response.id, customerCreated, paymentInstrumentCreated, shippingAddressCreated, httpCode, "", "");
                } else {
                    // Decline
                    onFinish2("AUTH+"+paAction, status, res.response.id, false, false, false,httpCode, res.response.errorInformation.reason, res.response.errorInformation.message);
                }
            } else {
                // 500 System error or anything else
                switch(httpCode){
                    case 202:
                        onFinish2("AUTH+"+paAction, status, res.response.id, false, false, false, httpCode, res.response.errorInformation.reason, res.response.errorInformation.message);
                        break;
                    case 502:
                        onFinish2("AUTH+"+paAction, status, "", false, false, false, httpCode, res.response.reason, res.response.message);
                        break;
                    case 400:
                        onFinish2("AUTH+"+paAction, status, "", false, false, false, httpCode, res.response.reason, res.response.message);
                        break;
                    default:
                        onFinish2("AUTH+"+paAction, status, "", false, false, false, httpCode, res.response.reason, res.response.message);
                }
            }
        }
    });
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
