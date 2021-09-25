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
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
        <title>View Basket</title>
    </head>
    <body>
        <div class="container-fluid">
            <div id="formSection">
            <form class="needs-validation" id="checkout_form" name="checkout" method="POST" target="checkout_iframe" action="checkout.php" novalidate >
                <label for="amount" class="form-label">Amount</label><input id="amount" class="form-control" type="text" name="amount" value="63.99" required/>
                <label for="reference_number" class="form-label">Order Reference</label><input id="reference_number" class="form-control" type="text" name="reference_number" value="<?php echo uniqid("PayPage", false);?>" required/>
                <label for="customerToken" class="form-label">Customer Token</label><input id="customerToken" class="form-control" type="text" name="customerToken" value="<?php echo getCookie("customerId")?>"/>
                <input id="currency" type="hidden" name="currency" value="GBP"/>
                <label for="local" class="form-label">Local</label><input id="local" class="form-control" type="text" name="local" value="<?php echo $local; ?>"/>
                <BR><button type="submit" class="btn btn-primary" >Checkout</button>
            </form>
            </div>
            <iframe id="checkoutIframe" name="checkout_iframe" src="about:blank" class="responsive-iframe" style="overflow: hidden; display: block; border:none; height:100vh; width:100%" ></iframe>
        </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
    <script>
    function buttonClicked(){
        document.getElementById('formSection').style.display="none";
        document.getElementById('checkoutIframe').style.display="block";
        var checkout_form = document.getElementById('checkout_form');
        if(checkout_form) checkout_form.submit();
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
    </script>
    </body>
</html>
