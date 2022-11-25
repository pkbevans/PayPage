<?php
//////////////////////FUNCTIONS
function getCookie($name){
    if(isset($_COOKIE[$name])){
        return $_COOKIE[$name];
    } else{
        return "";
    }
}

?>
<!DOCTYPE html>
<html lang="en-GB">
    <head>
        <!-- <meta http-equiv="Content-Security-Policy" content="script-src 'self' cdn.jsdelivr.net https://testflex.cybersource.com/ bondevans.com 'unsafe-inline' 'unsafe-eval'; style-src 'self' cdn.jsdelivr.net/ bondevans.com 'unsafe-eval' 'unsafe-inline' ; frame-src 'self' https://testflex.cybersource.com/ bondevans.com; child-src https://testflex.cybersource.com/; "> -->
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <title>View Basket</title>
    </head>
    <body>
        <div class="container">

        </div>
        <div class="container-fluid justify-content-center">
            <header class="d-flex flex-wrap justify-content-center py-3 mb-4 border-bottom">
                <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-dark text-decoration-none">
                    <span class="fs-4">Paul's Pants Checkout</span>
                </a>
                <ul class="nav nav-pills">
                    <li class="nav-item"><a href="/payPage/" class="nav-link active" aria-current="page">Home</a></li>
                    <li class="nav-item"><a href="#" class="nav-link">About</a></li>
                    <li class="nav-item"><a href="admin.php" class="nav-link">Admin portal...</a></li>
                </ul>
            </header>
            <!-- <small>5200000000001047, 5200000000000007, 371449111020228, 340000000001007</small> -->
            <div class="row">
                <div id="formSection">
                <form class="needs-validation" id="checkout_form" name="checkout" method="POST" target="checkout_iframe" action="" novalidate >
                    <label for="amount" class="form-label">Amount</label><input id="amount" class="form-control" type="text" name="amount" value="63.99" required/>
                    <label for="reference_number" class="form-label">Order Reference</label><input id="reference_number" class="form-control" type="text" name="reference_number" value="<?php echo uniqid("PayPage", false);?>" required/>
                    <label for="email" class="form-label">Email</label><input id="email" class="form-control" type="email" name="email" value="pkbevans@gmail.com" />
                    <label for="customer_reference" class="form-label">Merchants Customer Reference</label><input id="customerRef" class="form-control" type="text" name="customerRef" value="" />
                    <label for="customerToken" class="form-label">Customer Token</label><input id="customerToken" class="form-control" type="text" name="customerToken" value="<?php echo getCookie("customerId")?>"/>
                    <!--<label for="customerToken" class="form-label">Customer Token</label><input id="customerToken" class="form-control" type="text" name="customerToken" value=""/>-->
                    <input id="currency" type="hidden" name="currency" value="GBP"/>
                    <input id="orderId" type="hidden" name="orderId" value=""/>
                    <label for="autoCapture" class="form-label">Auto Capture</label>
                    <select id="autoCapture" class="form-select" name="autoCapture">
                        <option value="true" selected>Yes</option>
                        <option value="false">No</option>
                    </select>
                    <BR>
                    <button id="checkoutButton" type="button" class="btn btn-primary" onclick="validateForm()">Checkout</button>
                    <button type="button" class="btn btn-secondary" onclick="buyNowClicked()">Buy Now</button>
                    <input id="buyNow" type="hidden" name="buyNow" value="false"/>
                </form>
                </div>
                <iframe id="checkoutIframe" name="checkout_iframe" src="about:blank" class="responsive-iframe" style="overflow: hidden; display: none; border:none; height:90vh; width:100vw" ></iframe>
            </div>
        </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
    <script src="js/authorise.js"></script>
    <script>
    function buyNowClicked(){
        console.log("Buy Now");
        id=document.getElementById('customerToken');
        if(id.value===""){
            id.required = true;
        }else{
            document.getElementById("buyNow").value = "true";
        }
        validateForm();
    }
    function buttonClicked(){
        document.getElementById('formSection').style.display="none";
        document.getElementById('checkoutIframe').style.display="block";
        var checkout_form = document.getElementById('checkout_form');
        if(checkout_form){
            checkout_form.action = "checkout.php"
            writeOrder();
        }
    }
    function validateForm(){
      var form = document.getElementById('checkout_form');

        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
          form.classList.add('was-validated');
        }else{
            buttonClicked();
        }
    }
    function writeOrder(){
        $.ajax({
            type: "POST",
            url: "/payPage/db/insertOrder.php",
            data: JSON.stringify({
                "mrn": document.getElementById('reference_number').value,
                "customerId": document.getElementById('customerToken').value,
                "amount": document.getElementById('amount').value,
                "currency": document.getElementById('currency').value,
                "email": document.getElementById('email').value
            }),
            success: function (result) {
                console.log("\nOrder written:\n" + result);
                res = JSON.parse(result);

                if(res.status==="OK"){
                    document.getElementById('orderId').value = res.id;
                    checkout_form.submit();
                }
            }
        });
    }
    </script>
    </body>
</html>
