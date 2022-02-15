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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
    <!--Cardinal device data collection code START-->
    <iframe id="cardinal_collection_iframe" name="collectionIframe" height="1" width="1" style="display: none;"></iframe>
    <form id="cardinal_collection_form" method="POST" target="collectionIframe" action="">
        <input id="cardinal_collection_form_input" type="hidden" name="JWT" value=""/>
    </form>
    </<!--Cardinal device data collection code END-->
    <form id="step_up_form" name="stepup" method="POST" target="stepUpIframe" action="">
        <input id="step_up_form_jwt_input" type="hidden" name="JWT" value=""/>
        <input id="MD" type="hidden" name="MD" value="HELLO MUM. GET THE KETTLE ON"/>
    </form>
    <div class="container">
        <div class="d-flex justify-content-center">
            <div id="mainSpinner" class="spinner-border" style="display: block;"></div>
        </div>
        <iframe id="step_up_iframe" style="overflow: hidden; display: block; border:none; height:100vh; width:100%" name="stepUpIframe" ></iframe>
        <div id="resultSection" style="display: none">
            <h3>Result</h3>
            <p id="result"></p>
        </div>
        <div class="row">
            <div class="col-sm-2">
                <button type="button" id="newPaymentButton" class="btn btn-primary" onclick="window.location.href='index.php'" style="display: none">New Payment</button>
            </div>
            <div class="col-sm-2">
                <button type="button" id="retryButton" class="btn btn-primary" onclick="history.back()" style="display: none">Try again</button>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
</body>
<script>
var orderDetails;
document.addEventListener("DOMContentLoaded", function (e) {
    orderDetails = JSON.parse(sessionStorage.getItem("orderDetails"));
    setUpPayerAuth();
});
function setUpPayerAuth(){
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
                onFinish(orderDetails, status, "", false, false, httpCode, res.response.reason, res.response.message);
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
            // Response is a json string - turn it into a javascript object
            let res = JSON.parse(result);
            console.log("\nResults:\n" + JSON.stringify(res, undefined, 2));
            let httpCode = res.responseCode;
            let status = res.response.status;
            if (httpCode === 201) {
                customerCreated = false;
                paymentInstrumentCreated = false;
                shippingAddressCreated = false;
                let requestID = res.response.id;
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
                    onFinish(orderDetails, status, requestID, customerCreated, paymentInstrumentCreated, httpCode, "", "");
                } else {
                    onFinish(orderDetails, status, requestID, false, false, httpCode, res.response.reason, res.response.message);
                }
            } else {
                // 500 System error or anything else
                switch(httpCode){
                    case "202":
                        onFinish(orderDetails, status, "", false, false, httpCode, res.response.errorInformation.reason, res.response.errorInformation.message);
                        break;
                    case "400":
                        onFinish(orderDetails, status, "", false, false, httpCode, res.response.reason, res.response.details);
                        break;
                    default:
                        onFinish(orderDetails, status, "", false, false, httpCode, res.response.reason, res.response.message);
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
function onFinish(orderDetails2, status, requestId, newCustomer, paymentInstrumentCreated, httpResponseCode, errorReason, errorMessage) {
    document.getElementById('mainSpinner').style.display = "none";
    document.getElementById('step_up_iframe').style.display = "none";
    document.getElementById("resultSection").style.display = "block";
    finish = "onFinish: " + JSON.stringify({
        "referenceNumber": orderDetails2.referenceNumber,
        "status": status,
        "httpResponseCode": httpResponseCode,
        "requestId": requestId,
        "amount": orderDetails2.amount,
        "pan": orderDetails2.maskedPan,
        "newCustomer": newCustomer,
        "newPaymentInstrument": paymentInstrumentCreated,
        "customerId": orderDetails2.customerId,
        "paymentInstrumentId": orderDetails2.paymentInstrumentId,
        "shippingAddressId": orderDetails2.shippingAddressId,
        "errorReason": errorReason,
        "errorMessage": errorMessage
    }, undefined, 2);
    console.log(finish);
    if (status === "AUTHORIZED") {
        text = "Thank you.  Your payment has completed" + "<BR><PRE>" + finish +"</PRE>";
        document.getElementById("newPaymentButton").style.display = "block";
    } else {
        text = "Oh dear. Your payment was not successful.  You can try again or try a different payment method" + "<BR>" + finish;
        document.getElementById("retryButton").style.display = "block";
    }
    result = document.getElementById("result").innerHTML = text;
    if(newCustomer && !orderDetails2.paymentInstrumentId !== ""){
        // Write new Customer Token to cookie
        document.cookie = "customerId=" + orderDetails2.customerId;
    }
}
</script>
