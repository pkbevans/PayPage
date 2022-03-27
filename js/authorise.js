function setUpPayerAuth(){
    document.getElementById('authSpinner').style.display = "block";
    $.ajax({
        type: "POST",
        url: "rest_setup_payerAuth.php",
        data: JSON.stringify({
            "order": orderDetails
        }),
        success: function (result) {
            res = JSON.parse(result);
            console.log("\nSetup Payer Auth:\n" + JSON.stringify(res, undefined, 2));
            // If OK, set up device collection
            let httpCode = res.responseCode;
            if (httpCode === 201) {
                // Set up device collection
                deviceDataCollectionURL = res.response.consumerAuthenticationInformation.deviceDataCollectionUrl;
                accessToken = res.response.consumerAuthenticationInformation.accessToken;
                doDeviceCollection(deviceDataCollectionURL, accessToken);
            } else {
                // 500 System error or anything else
                onFinish2("SETUPPA", status, "", false, false, httpCode, res.response.reason, res.response.message);
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
        url: "rest_auth_with_pa.php",
        data: JSON.stringify({
            "order": orderDetails,
            "paAction": paAction,
            "referenceID": dfReferenceId,
            "authenticationTransactionID": authenticationTransactionID
        }),
        success: function (result) {
            console.log("\nResult:\n" + result);
            // Response is a json string - turn it into a javascript object
            let res = JSON.parse(result);
            console.log("\nResults:\n" + JSON.stringify(res, undefined, 2));
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
                    if (orderDetails.storeCard) {
                        paymentInstrumentCreated = true;
                        orderDetails.paymentInstrumentId = res.response.tokenInformation.paymentInstrument.id;
                        if(orderDetails.shippingAddressId === ""){
                            // Not using an existing shippingAddress so must be creating a new one
                            orderDetails.shippingAddressId = res.response.tokenInformation.shippingAddress.id;
                        }
                        if (!orderDetails.customerId) {
                            // New Customer
                            customerCreated = true;
                            orderDetails.customerId = res.response.tokenInformation.customer.id;
                        }
                    }
                    onFinish2("AUTH+"+paAction, status, res.response.id, customerCreated, paymentInstrumentCreated, httpCode, "", "");
                } else {
                    // Decline
                    onFinish2("AUTH+"+paAction, status, res.response.id, false, false, httpCode, res.response.errorInformation.reason, res.response.errorInformation.message);
                }
            } else {
                // 500 System error or anything else
                switch(httpCode){
                    case 202:
                        onFinish2("AUTH+"+paAction, status, res.response.id, false, false, httpCode, res.response.errorInformation.reason, res.response.errorInformation.message);
                        break;
                    case 502:
                        onFinish2("AUTH+"+paAction, status, "", false, false, httpCode, res.response.reason, res.response.message);
                        break;
                    case 400:
                        onFinish2("AUTH+"+paAction, status, "", false, false, httpCode, res.response.reason, res.response.details.field);
                        break;
                    default:
                        onFinish2("AUTH+"+paAction, status, "", false, false, httpCode, res.response.reason, res.response.message);
                }
            }
        }
    });
}
function showStepUpScreen(stepUpURL, jwt) {
    // console.log( "Challenge Screen:\n"+stepUpURL);
    document.getElementById('step_up_form').action = stepUpURL;
    document.getElementById('step_up_form_jwt_input').value = jwt;
    var stepUpForm = document.getElementById('step_up_form');
    if (stepUpForm){
        stepUpForm.submit();
    }
}
function hideStepUpScreen(transactionId) {
    console.log("Challenge Complete TransactionId:\n" + transactionId);
    document.getElementById("step_up_iframe").style.display = "none";
    authorizeWithPA("", transactionId, "VALIDATE_CONSUMER_AUTHENTICATION");
}
function onFinish2(apiCalled, status, requestId, newCustomer, paymentInstrumentCreated, httpResponseCode, errorReason, errorMessage) {
    document.getElementById('authSection').style.display = "none";
    document.getElementById('summarySection').style.display = "none";
    document.getElementById('confirmSection').style.display = "none";
//    document.getElementById('iframeSection').style.display = "none";
    finish = "onFinish2: " + JSON.stringify({
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
        "customerId": orderDetails.customerId,
        "paymentInstrumentId": orderDetails.paymentInstrumentId,
        "shippingAddressId": orderDetails.shippingAddressId,
        "errorReason": errorReason,
        "errorMessage": errorMessage
    }, undefined, 2);
    console.log(finish);
    if (status === "AUTHORIZED") {
        text = "Thank you.  Your payment has completed" + "<BR><PRE>" + finish +"</PRE>";
        document.getElementById("retryButton").style.display = "none";
    } else {
        text = "Oh dear. Your payment was not successful.  You can try again or try a different payment method" + "<BR>" + finish;
    }
    document.getElementById("resultText").innerHTML = text;
    document.getElementById("resultSection").style.display = "block";
    if(newCustomer && !orderDetails.paymentInstrumentId !== ""){
        // Write new Customer Token to cookie
        document.cookie = "customerId=" + orderDetails.customerId;
    }
}
