<?php
if(strstr($_SERVER['HTTP_HOST'],"localhost")){
    $local = "true";
}else{
    $local = "false";
}
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
        <!--<meta http-equiv="Content-Security-Policy" content="script-src 'self' cdn.jsdelivr.net https://testflex.cybersource.com/ bondevans.com 'unsafe-inline' 'unsafe-eval'; style-src 'self' cdn.jsdelivr.net/ bondevans.com 'unsafe-eval' 'unsafe-inline' ; frame-src 'self' https://testflex.cybersource.com/ bondevans.com; child-src https://testflex.cybersource.com/; ">--> 
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <title>View Basket</title>
    </head>
    <body>
        <span>5200000000001047,5200000000000007,371449111020228,340000000001007</span><br>
        <div class="container-fluid justify-content-center">
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
                <label for="local" class="form-label">Local</label><input id="local" class="form-control" type="text" name="local" value="<?php echo $local; ?>"/>
                <label for="autoCapture" class="form-label">Auto Capture</label>
                <select id="autoCapture" class="form-select" name="autoCapture">
                    <option value="true" selected>Yes</option>
                    <option value="false" selected>No</option>
                </select>
                <BR><button type="submit" class="btn btn-primary">Checkout</button>
            </form>
            </div>
            <iframe id="checkoutIframe" name="checkout_iframe" src="about:blank" class="responsive-iframe" style="overflow: hidden; display: none; border:none; height:90vh; width:100vw" ></iframe>
        </div>
        </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
    <script>
    function buttonClicked(){
        document.getElementById('formSection').style.display="none";
        document.getElementById('checkoutIframe').style.display="block";
        var checkout_form = document.getElementById('checkout_form');
        if(checkout_form){
            checkout_form.action = "checkout.php"
            writeOrder();
        }
    }

    // Example starter JavaScript for disabling form submissions if there are invalid fields
    (function () {
      'use strict';

      // Fetch all the forms we want to apply custom Bootstrap validation styles to
      var forms = document.querySelectorAll('.needs-validation');

      // Loop over them and prevent submission
      Array.prototype.slice.call(forms)
        .forEach(function (form) {
          form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
              event.preventDefault();
              event.stopPropagation();
            }else{
                buttonClicked();
            }

            form.classList.add('was-validated');
          }, false);
        });
    })();
    function writeOrder(){
        $.ajax({
            type: "POST",
            url: "/payPage/tests/write_order.php",
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
