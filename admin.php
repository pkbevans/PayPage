<!DOCTYPE html>
<html lang="en-GB">
    <head>
        <!-- <meta http-equiv="Content-Security-Policy" content="script-src 'self' cdn.jsdelivr.net https://testflex.cybersource.com/ bondevans.com 'unsafe-inline' 'unsafe-eval'; style-src 'self' cdn.jsdelivr.net/ bondevans.com 'unsafe-eval' 'unsafe-inline' ; frame-src 'self' https://testflex.cybersource.com/ bondevans.com; child-src https://testflex.cybersource.com/; "> -->
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
        <title>Admin Portal</title>
    </head>
    <body>
        <div class="container-fluid justify-content-center">
            <header class="d-flex flex-wrap justify-content-center py-3 mb-4 border-bottom">
                <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-dark text-decoration-none">
                    <span class="fs-4">Admin Portal</span>
                </a>
                <ul class="nav nav-pills">
                    <li class="nav-item"><a href="/payPage/" class="nav-link active" aria-current="page">Home</a></li>
                    <li class="nav-item"><a href="#" class="nav-link">About</a></li>
                    <li class="nav-item"><a href="index.php" class="nav-link">Checkout</a></li>
                </ul>
            </header>
            <div id=loginSection>
            <h1>Login</h1>
                    <form class="needs-validation" id="loginForm" name="" method="" target="" action="" novalidate >
                        <label for="userName" class="form-label">Username</label><input id="userName" class="form-control" autocomplete="username" type="text" name="userName" value="" required/>
                        <label for="password" class="form-label">Password</label><input id="password" class="form-control" autocomplete="current-password" type="password" name="password" value="" required/>
                        <button type="button" class="btn btn-primary" onclick="login()">Log in</button>
                    </form>
            </div>
            <div class="row">
                <div id="formSection" style="display: none">
                    <h1>Find Order</h1>
                    <form class="needs-validation" id="findForm" name="checkout" method="" target="" action="" novalidate >
                        <label for="orderId" class="form-label">Order Id</label><input id="orderId" class="form-control" type="text" name="orderId" value="" />
                        <label for="referenceNumber" class="form-label">Order Reference</label><input id="referenceNumber" class="form-control" type="text" name="findForm" value="" />
                        <label for="email" class="form-label">Email</label><input id="email" class="form-control" type="email" name="email" value="" />
                        <label for="customerToken" class="form-label">Customer Token</label><input id="customerToken" class="form-control" type="text" name="customerToken" value=""/>
                        <label for="status" class="form-label">Status</label><input id="status" class="form-control" type="text" name="status" value=""/>
                        <BR>
                        <button type="button" class="btn btn-primary" onclick="validateForm()">Find Orders</button>
                    </form>
                </div>
            </div>
            <div id="ordersSection"></div>
            <div id="orderSection">
                <div id="orderDetailSection"></div>
                <div id="actionSection"></div>
                <div id="statusSection"></div>
            </div>
            <div class="modal fade" id="requestModal" tabindex="-1" aria-labelledby="requestModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="requestModalLabel">Request Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                              <div id="requestSection"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row" id="backButton" style="display: none">
                <div class="col-12">
                    <button type="button" class="btn btn-primary" onclick="backButton()">Back</button>
                </div>
            </div>
        </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
    <script>
    let back=0;
    let selectedOrderId=0;

    function login(){
        var form = document.getElementById('loginForm');

        if(!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
            form.classList.add('was-validated');
        }else{
            return fetch("/payPage/v1/sessions", {
            headers: {
                  'Content-Type': 'application/json'
            },
            method: "post",
            body: JSON.stringify({
                "userName": document.getElementById('userName').value,
                "password": document.getElementById('password').value,
            })
        })
        .then((result) => {
            console.log(result);
            if(result.ok){
                return result.json()
            }else{
                throw "unauthorised"
            }
        })
        .then((result)=>{
            console.log(result);
            document.cookie = "tokenExpires=" + result.data.accessTokenExpiresIn+';expires=;path=/';
            document.cookie = "sessionId=" + result.data.sessionId+';expires=;path=/';
            document.cookie = "accessToken=" + result.data.accessToken+';expires=;path=/';
            document.cookie = "refreshToken=" + result.data.refreshToken+';expires=;path=/';
            document.getElementById("loginSection").style.display="none"
            document.getElementById("formSection").style.display="block"
        })
        .catch(error => {
            console.log("ERROR: "+error)
        })}
    }
    function buttonClicked(){
        ++back;
        document.getElementById('formSection').style.display="none";
        getOrders();
    }
    function validateForm(){
        var form = document.getElementById('findForm');

        if(!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
            form.classList.add('was-validated');
        }else{
            buttonClicked();
        }
    }
    function getOrders(){
        document.getElementById('backButton').style.display = "block";
        document.getElementById('ordersSection').style.display = "block";
        return fetch("/payPage/view/listOrders.php", {
            method: "post",
            body: JSON.stringify({
                "orderId":    document.getElementById('orderId').value,
                "mrn":        document.getElementById('referenceNumber').value,
                "customerId": document.getElementById('customerToken').value,
                "email":      document.getElementById('email').value,
                "status":     document.getElementById('status').value
            })
        })
        .then((result) => result.text())
        .then(res => {
            return document.getElementById('ordersSection').innerHTML = res;
        })
        .catch(error => {
            console.log("ERROR: "+error)
        })
    }
    function getOrder(id){
        selectedOrderId = id;
        ++back;
        document.getElementById('ordersSection').style.display = "none";
        document.getElementById('orderSection').style.display = "block";
        // console.log("GOT ID: "+id)
        return fetch("/payPage/view/viewOrder.php", {
            method: "post",
            body: JSON.stringify({
                "orderId":    id
            })
        })
        .then((result) => result.text())
        .then(res => {
            return document.getElementById('orderDetailSection').innerHTML = res;
        })
        .catch(error => {
            console.log("ERROR: "+error)
        })
    }
    function showActionPage(paymentId, action){
        // console.log("showRefundPage: "+paymentId)
        ++back;
        document.getElementById('actionSection').style.display = "block";
        return fetch("/payPage/view/create"+action+".php", {
            method: "post",
            body: JSON.stringify({
                "paymentId": paymentId
            })
        })
        .then((result) => result.text())
        .then(res => {
            return document.getElementById('actionSection').innerHTML = res;
        })
        .catch(error => {
            console.log("ERROR: "+error)
        })
    }
    function submitAction(action, orderId, requestId, currency, originalAmount, cardNumber){
        reason = document.getElementById('reason').value;
        reference = document.getElementById('reference').value;
        amount = Number(document.getElementById('amount').value);
        var form = document.getElementById('actionForm');
        let amountOK=true;
        if((action == "Refund" && amount > originalAmount)
                || (!form.checkValidity())) {
            event.preventDefault();
            event.stopPropagation();
            form.classList.add('was-validated');
        }else{
            // console.log(action + ": " + requestId + " Amount: " + amount);
            return fetch("/payPage/api/submit" + action + ".php", {
                method: "post",
                body: JSON.stringify({
                    "orderId": orderId,
                    "referenceNumber": reference,
                    "reason": reason,
                    "requestId": requestId,
                    "currency": currency,
                    "amount": amount,
                    "cardNumber": cardNumber
                })
            })
            .then(result => result.json())
            .then(res => {
                // console.log(res);
                if(res.responseCode === 201){
                    document.getElementById('statusSection').innerHTML = "SUCCESS. The " + action + " has been submitted";
                    // Refresh the order list and the specific order
                    getOrders();
                    getOrder(orderId);
                    --back;
                    backButton();
                }else{
                    $errMessage = "";
                    if(res.responseCode === 400){
                        $errMessage = res.response.message;
                    }
                    document.getElementById('statusSection').innerHTML = "ERROR. The "+ action + " could not be submitted: " + $errMessage;
                }
                return "OK";
            })
            .catch(error => {
                console.log("ERROR: "+error)
            })
        }
    }
    function showRequest(id){
        document.getElementById('requestModalLabel').innerHTML = "Request Details";
        return fetch("/payPage/view/viewGatewayRequest.php", {
            method: "post",
            body: JSON.stringify({
                "requestId": id
            })
        })
        .then((result) => {
            // console.log(result);
            return result.text()
        })
        .then(res => {
            document.getElementById('requestSection').innerHTML = res;
            var myModal = new bootstrap.Modal(document.getElementById('requestModal'), {keyboard: false});
            myModal.show();
        })
        .catch(error => {
            console.log("ERROR: "+error)
        })
    }
    function showShipping(id){
        document.getElementById('requestModalLabel').innerHTML = "Shipping Details";
        return fetch("/payPage/view/viewShippingAddress.php", {
            method: "post",
            body: JSON.stringify({
                "requestId": id
            })
        })
        .then((result) => {
            // console.log(result);
            return result.text()
        })
        .then(res => {
            document.getElementById('requestSection').innerHTML = res;
            var myModal = new bootstrap.Modal(document.getElementById('requestModal'), {keyboard: false});
            myModal.show();
        })
        .catch(error => {
            console.log("ERROR: "+error)
        })
    }
    function showCustomer(customerId){
        document.getElementById('requestModalLabel').innerHTML = "Customer Details";
        return fetch("/payPage/view/viewGatewayCustomer.php", {
            method: "post",
            body: JSON.stringify({
                "customerId": customerId,
                "noEcho": true
            })
        })
        .then((result) => {
            // console.log(result);
            return result.text()
        })
        .then(res => {
            document.getElementById('requestSection').innerHTML = res;
            var myModal = new bootstrap.Modal(document.getElementById('requestModal'), {keyboard: false});
            myModal.show();
        })
        .catch(error => {
            console.log("ERROR: "+error)
        })
    }
    function showLog(referenceNumber){
        document.getElementById('requestModalLabel').innerHTML = "Order Logs";
        return fetch("/payPage/view/viewGatewayLog.php", {
            method: "post",
            body: JSON.stringify({
                "referenceNumber": referenceNumber
            })
        })
        .then((result) => {
            // console.log(result);
            return result.text()
        })
        .then(res => {
            document.getElementById('requestSection').innerHTML = res;
            var myModal = new bootstrap.Modal(document.getElementById('requestModal'), {keyboard: false});
            myModal.show();
        })
        .catch(error => {
            console.log("ERROR: "+error)
        })
    }
    function backButton() {
        document.getElementById('statusSection').innerHTML = "";
        switch (back){
            case 1: // Orders
                document.getElementById('formSection').style.display="block";
                document.getElementById('ordersSection').style.display="none";
                document.getElementById('backButton').style.display = "none";
                break;
            case 2: // Order Detail
                document.getElementById('orderSection').style.display="none";
                document.getElementById('ordersSection').style.display="block";
                break;
            case 3: //  Refund/Reversal Screen
                document.getElementById('actionSection').style.display="none";
                document.getElementById('orderSection').style.display="block";
                break;
            case 4: // Refund submitted
                document.getElementById('actionSection').style.display="none";
                document.getElementById('orderSection').style.display="block";
                break;
        }
        --back;
    }
    function refresh(){
        getOrders();
    }
    </script>
    </body>
</html>
