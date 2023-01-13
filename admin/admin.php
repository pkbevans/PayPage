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
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">
                    <a class="navbar-brand" href="#">Admin Portal</a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="/payPage/">Home</a>
                        </li>
                        <li class="nav-item">
                        <a class="nav-link" href="#">About</a>
                        </li>
                        <li class="nav-item">
                        <a class="nav-link" href="../index.php">Checkout</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link disabled" id="userFullName" href="#" tabindex="-1" aria-disabled="true"></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="logout" href="#" onclick="logout()">Logout</a>
                        </li>
                    </ul>
                    </div>
                </div>
            </nav>
            <div id=loginSection>
                <h3>Login</h3>
                <form class="needs-validation" id="loginForm" name="" method="" target="" action="" novalidate >
                    <label for="userName" class="form-label">Username</label><input id="userName" class="form-control" autocomplete="username" type="text" name="userName" value="" required/>
                    <label for="password" class="form-label">Password</label><input id="password" class="form-control" autocomplete="current-password" type="password" name="password" value="" required/>
                    <button type="button" class="btn btn-primary" onclick="login()">Log in</button>
                </form>
            </div>
            <div id=contentSection>
                <div id="formSection">
                    <h3>Find Order</h3>
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
        </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
    <script src="../common/js/authenticate.js"></script>
    <script>
    let back="none";
    let selectedOrderId=0;

    document.addEventListener("DOMContentLoaded", function (e) {
        authenticate('/payPage/admin')
        .then(accessToken=>{
            console.log("Authenticated")
            document.getElementById("userFullName").innerHTML=getCookie('fullName');
        })
        .catch(error=>console.log(error));
    });
    function onSuccessfulLogin(result){
        console.log(result);
        var t = new Date();
        t.setSeconds(t.getSeconds() + result.data.accessTokenExpiresIn);
        document.cookie = "accessTokenExpires=" + t+';expires=;';
        t = new Date();
        t.setSeconds(t.getSeconds() + result.data.refreshTokenExpiresIn);
        document.cookie = "refreshTokenExpires=" + t+';expires=; ';
        document.cookie = "sessionId=" + result.data.sessionId+';expires=; ';
        document.cookie = "accessToken=" + result.data.accessToken+';expires=;';
        document.cookie = "refreshToken=" + result.data.refreshToken+';expires=;';
        // Special handling for Guest user
        if(result.data.userName !== "guest"){
            fullName = result.data.firstName + " " + result.data.lastName;
            document.cookie = "fullName=" + fullName+';expires=; ';
            document.cookie = "email=" + result.data.email+';expires=;';
            document.getElementById("userFullName").innerHTML=fullName;
        }
        document.getElementById("loginSection").style.display="none"
        document.getElementById("formSection").style.display="block"
        document.getElementById("contentSection").style.display="block"
        document.getElementById("logout").style.display="block"
    }
    function onSuccessfulLogout(){
        document.getElementById("userFullName").innerHTML='';
        document.getElementById('ordersSection').innerHTML="";
        document.getElementById('orderDetailSection').innerHTML="";

        document.getElementById("logout").style.display="none"
        document.getElementById("loginSection").style.display="block"
        document.getElementById('backButton').style.display = "none";
        document.getElementById("contentSection").style.display="none"
    }
    function validateForm(){
        var form = document.getElementById('findForm');

        if(!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
            form.classList.add('was-validated');
        }else{
            document.getElementById('formSection').style.display="none";
            getOrders(1);
        }
    }
    function getOrders(page){
        authenticate('/payPage/admin')
        .then(accessToken=>{
            console.log("getOrders: "+accessToken);
            back = "ORDERLIST";
            document.getElementById('backButton').style.display = "block";
            document.getElementById('ordersSection').style.display = "block";
            return fetch("/payPage/admin/api/getOrders.php", {
                method: "post",
                body: JSON.stringify({
                    "page":    page,
                    "rows":    8,
                    "accessToken":    accessToken,
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
        })
        .catch(error=>console.log("Authentication error: "+error));
    }
    function getOrder(id){
        authenticate('/payPage/admin')
        .then(accessToken=>{
            console.log("getOrder "+id);
            back = "ORDER";
            selectedOrderId = id;
            document.getElementById('ordersSection').style.display = "none";
            document.getElementById('orderSection').style.display = "block";
            // console.log("GOT ID: "+id)
            return fetch("/payPage/admin/api/getOrder.php", {
                method: "post",
                body: JSON.stringify({
                    "accessToken" : accessToken,
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
        })
        .catch(error=>console.log("Authentication error: "+error));
    }
    function showActionPage(paymentId, action){
        authenticate('/payPage/admin')
        .then(accessToken=>{
            back = "SHOWACTION";
            document.getElementById('actionSection').style.display = "block";
            return fetch("/payPage/admin/api/getPayment.php", {
                method: "post",
                body: JSON.stringify({
                    "accessToken" : accessToken,
                    "paymentId": paymentId,
                    "action": action
                })
            })
            .then((result) => result.text())
            .then(res => {
                return document.getElementById('actionSection').innerHTML = res;
            })
            .catch(error => {
                console.log("ERROR: "+error)
            })
        })
    }
    function submitAction(action, orderId, requestId, currency, originalAmount, cardNumber){
        authenticate('/payPage/admin')
        .then(accessToken=>{
            back = "SUBMITACTION";
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
                return fetch("/payPage/admin/api/submit" + action + ".php", {
                    method: "post",
                    body: JSON.stringify({
                        "accessToken": accessToken,
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
                    if(res.success){
                        document.getElementById('statusSection').innerHTML = "SUCCESS. The " + action + " has been submitted";
                        // Refresh the specific order details
                        getOrder(orderId);
                        backButton();
                    }else{
                        $errMessage = res.messages[0];
                        document.getElementById('statusSection').innerHTML = "ERROR. The "+ action + " could not be submitted: " + $errMessage;
                    }
                    return "OK";
                })
                .catch(error => {
                    console.log("ERROR: "+error)
                })
            }
        })
    }
    function showRequest(id){
        authenticate('/payPage/admin')
        .then(accessToken=>{
            document.getElementById('requestModalLabel').innerHTML = "Request Details";
            return fetch("/payPage/admin/view/viewGatewayRequest.php", {
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
        })
    }
    function showShipping(id){
        authenticate('/payPage/admin')
        .then(accessToken=>{
            document.getElementById('requestModalLabel').innerHTML = "Shipping Details";
            return fetch("/payPage/admin/view/viewShippingAddress.php", {
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
        })
    }
    function showCustomer(customerId){
        authenticate('/payPage/admin')
        .then(accessToken=>{
            document.getElementById('requestModalLabel').innerHTML = "Customer Details";
            return fetch("/payPage/admin/view/viewGatewayCustomer.php", {
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
        })
    }
    function showLog(referenceNumber){
        authenticate('/payPage/admin')
        .then(accessToken=>{
            document.getElementById('requestModalLabel').innerHTML = "Order Logs";
            return fetch("/payPage/admin/view/viewGatewayLog.php", {
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
        })
    }
    function backButton() {
        document.getElementById('statusSection').innerHTML = "";
        switch (back){
            case "ORDERLIST": // Orders
                document.getElementById('formSection').style.display="block";
                document.getElementById('ordersSection').innerHTML="";
                document.getElementById('ordersSection').style.display="none";
                document.getElementById('backButton').style.display = "none";
                break;
            case "ORDER": // Order Detail
                refresh();
                document.getElementById('orderDetailSection').innerHTML="";
                document.getElementById('orderSection').style.display="none";
                document.getElementById('ordersSection').style.display="block";
                back="ORDERLIST";
                break;
            case "SHOWACTION": //  Refund/Reversal Screen
                document.getElementById('actionSection').innerHTML="";
                document.getElementById('actionSection').style.display="none";
                document.getElementById('orderSection').style.display="block";
                back="ORDER";
                break;
            case "SUBMITACTION": // Refund submitted
                document.getElementById('actionSection').style.display="none";
                document.getElementById('orderSection').style.display="block";
                back="ORDER";
                break;
        }
    }
    function refresh(){
        getOrders(1);
    }
    </script>
    </body>
</html>
