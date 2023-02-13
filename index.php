<?php
//////////////////////FUNCTIONS
function getCookie($name){
    if(isset($_COOKIE[$name])){
        return $_COOKIE[$name];
    } else{
        return "";
    }
}
$mrn=uniqid("PayPage", false);  // Unique Merchant Reference No. for each visit
?>
<!DOCTYPE html>
<html lang="en-GB">
    <head>
        <!-- <meta http-equiv="Content-Security-Policy" content="script-src 'self' cdn.jsdelivr.net https://testflex.cybersource.com/ bondevans.com 'unsafe-inline' 'unsafe-eval'; style-src 'self' cdn.jsdelivr.net/ bondevans.com 'unsafe-eval' 'unsafe-inline' ; frame-src 'self' https://testflex.cybersource.com/ bondevans.com; child-src https://testflex.cybersource.com/; "> -->
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
        <title>View Basket</title>
    </head>
    <body>
        <div class="container-fluid justify-content-center">
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">
                    <a class="navbar-brand" href="#">Checkout</a>
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
                        <a class="nav-link" href="admin/admin.php">Admin Portal</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link disabled" id="userFullName" href="#" tabindex="-1" aria-disabled="true"></a>
                        </li>
                    </ul>
                    </div>
                </div>
            </nav>
        </div>
        <div class="container">
            <div class="row" id=loginSection>
                <div class="col-12 col-lg-4 justify-content-center">
                    <div class="card">
                        <div class="card-body">
                            <div >
                                <h3>Login</h3>
                                <form class="needs-validation" id="loginForm" name="" method="" target="" action="" novalidate >
                                    <label for="userName" class="form-label">Username</label><input id="userName" class="form-control" autocomplete="username" type="text" name="userName" value="" required/>
                                    <label for="password" class="form-label">Password</label><input id="password" class="form-control" autocomplete="current-password" type="password" name="password" value="" required/>
                                    <button type="button" class="btn btn-primary" onclick="logUserIn()">Log in</button>
                                </form>
                                <div id="loginAlert" class="alert alert-danger" role="alert" style="display: none;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-lg-6">
                    <div id=contentSection style="display: none">
                        <div class="card">
                            <div class="card-body">
                                <div id="formSection">
                                    <h3>Your Basket</h3>
                                    <div class="row">
                                        <div class="col-4 col-lg-3" >
                                            Order Ref
                                        </div>
                                        <div class="col-8 col-lg-9 d-flex justify-content-end">
                                            <?php echo $mrn;?>
                                        </div>
                                    </div>
                                    <BR>
                                    <div id="basketSection"></div>
                                    <input id="currency" type="hidden" name="currency" value="GBP"/>
                                    <form class="needs-validation" id="checkout_form" name="checkout" method="POST" target="checkout_iframe" action="" novalidate >
                                        <input id="orderId" type="hidden" name="orderId" value=""/>
                                        <input id="orderHash" type="hidden" name="orderHash" value=""/>
                                        <BR>
                                        <div class="row">
                                            <div class="col-8 col-lg-9" >
                                                Auto-Capture
                                            </div>
                                            <div class="col-4 col-lg-3 d-flex justify-content-end">
                                            <select id="autoCapture" class="form-select" name="autoCapture">
                                                <option value="true" selected>Yes</option>
                                                <option value="false">No</option>
                                            </select>
                                            </div>
                                        </div>
                                        <BR>
                                        <button id="checkoutButton" type="button" class="btn btn-primary" onclick="validateForm()">Checkout</button>
                                        <button id="buyNowButton" type="button" class="btn btn-secondary" onclick="buyNowClicked()" disabled>Buy Now</button>
                                        <input id="buyNow" type="hidden" name="buyNow" value="false"/>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <iframe id="checkoutIframe" name="checkout_iframe" src="about:blank" class="responsive-iframe" style="overflow: hidden; display: none; border:none; height:90vh; width:100vw" ></iframe>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
    <script src="common/js/authenticate.js"></script>
    <script src="checkout/js/authorise.js"></script>
    <script>
    var customerId;
    var customerUserId;
    var orderDetails = {
        orderItems:[ 
            {
                productCode:"10000001",
                description:"Big Brown Britches",
                quantity:2,
                unitPrice:10.00,
                totalAmount:20.00
            },
            {
                productCode:"10000010",
                description:"Frilly Knickers",
                quantity:1,
                unitPrice:14.00,
                totalAmount:14.00
            }
        ],
        delivery:4.99,
        // subTotal=38.99
        vat:7.80,
        totalAmount:46.79
    };

    document.addEventListener("DOMContentLoaded", function (e) {
        renderBasket();
    });
    function logUserIn(){
        login(document.getElementById('userName').value, document.getElementById('password').value)
    }
    function onSuccessfulLogin(result){
        console.log(result);
        var t = new Date();
        t.setSeconds(t.getSeconds() + result.data.accessTokenExpiresIn);
        document.cookie = "accessTokenExpires=" + t+';expires=;path=/';
        t = new Date();
        t.setSeconds(t.getSeconds() + result.data.refreshTokenExpiresIn);
        document.cookie = "refreshTokenExpires=" + t+';expires=;path=/';
        document.cookie = "sessionId=" + result.data.sessionId+';expires=;path=/';
        document.cookie = "accessToken=" + result.data.accessToken+';expires=;path=/';
        document.cookie = "refreshToken=" + result.data.refreshToken+';expires=;path=/';
        // Special handling for Guest user
        if(result.data.userName !== "guest"){
            fullName = result.data.firstName + " " + result.data.lastName;
            document.cookie = "fullName=" + fullName+';expires=;path=/';
            document.cookie = "email=" + result.data.email+';expires=;path=/';
            document.getElementById("userFullName").innerHTML=fullName;
            document.getElementById("buyNowButton").disabled = false;
        }
        customerId=result.data.customerId;
        customerUserId=result.data.customerUserId;
        document.getElementById("loginAlert").style.display='none';
        document.getElementById("loginSection").style.display="none"
        document.getElementById("contentSection").style.display="block"
    }
    function onQtyChange(item){
        orderDetails.orderItems[item].quantity = document.getElementById("quantity_"+item).value;
        let subTotal=0.00;
        orderDetails.orderItems.forEach((number, index, array) => {
            orderDetails.orderItems[item].totalAmount = array[index].quantity*array[index].unitPrice;
            subTotal+=orderDetails.orderItems[item].totalAmount;
        });
        subTotal+=orderDetails.delivery;
        let vat = subTotal*0.20;
        orderDetails.vat = vat;
        let totalAmount = subTotal+orderDetails.vat;
        orderDetails.totalAmount = totalAmount;
        renderBasket();
    }
    function renderBasket(){
        return fetch("/payPage/checkout/api/basket.php", {
            method: "post",
            body: JSON.stringify({
                "orderDetails": orderDetails
            })
        })
        .then((result) => result.text())
        .then((result) => document.getElementById('basketSection').innerHTML = result)
        .catch((error) => console.log("Basket ERROR:"+error))
    }
    function buyNowClicked(){
        console.log("Buy Now");
        id=customerId;
        if(id.value===""){
            id.required = true;
        }else{
            document.getElementById("buyNow").value = "true";
        }
        validateForm();
    }
    function buttonClicked(){
        document.getElementById('contentSection').style.display="none";
        document.getElementById('checkoutIframe').style.display="block";
        var checkout_form = document.getElementById('checkout_form');
        if(checkout_form){
            checkout_form.action = "checkout/api/checkout.php"
            writeOrder();
        }
    }
    function validateForm(){
        authenticate('/')
        .then(accessToken=>{
        var form = document.getElementById('checkout_form');

            if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
            form.classList.add('was-validated');
            }else{
                buttonClicked();
            }
        })
    }
    function writeOrder(){
        return fetch("/payPage/common/v1/controller/orders.php", {
            headers: {
                    'Content-Type': 'application/json',
                    'Authorization': getCookie("accessToken")
                },
            method: "post",
            body: JSON.stringify({
                "merchantReference": "<?php echo $mrn?>",
                "customerId": customerId,
                "customerUserId": customerUserId,
                "orderDetails": JSON.stringify(orderDetails),
                "amount": orderDetails.totalAmount,
                "refundAmount": 0,
                "currency": document.getElementById('currency').value,
                "customerEmail": "",
                "status": "NEW"
            })
        })
        .then((result) => result.json())
        .then((result) =>{
            if(result.success){
                document.getElementById('orderId').value = result.data.orders[0].id;
                document.getElementById('orderHash').value = result.data.orders[0].hash;
                checkout_form.submit();
            }else{
                throw result.statusCode + " : " + result.messages[0];
            }
        })
        .catch(error => console.error("ERROR writing order:"+error))
    }
    </script>
    </body>
</html>
