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
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
        <title>Get Customer Details</title>
    </head>
    <body>
        <div class="container-fluid">
            <form>
                <label for="customerToken">Customer Token ID</label><input id="customerToken" type="text" name="customerToken" value="CCAC2DFA364CFA16E053AF598E0A3AA0">
                <button type="button" onclick="go()">Go</button>
            </form>
            <div id="cardsIframe" style="display: none">
                <iframe name="cards_iframe" src="" class="responsive-iframe" style="overflow: hidden; display: block; border:none; height:50vh; width:100%" ></iframe>
            </div>
            <form id="cards_form" method="POST" target="cards_iframe" action="">
                <input id="customerTokenCards" type="hidden" name="customerToken" value="">
            </form>

            <div id="shippingIframe" style="display: none">
                <iframe name="shipping_iframe" src="" class="responsive-iframe" style="overflow: hidden; display: block; border:none; height:50vh; width:100%" ></iframe>
            </div>
            <form id="shipping_form" method="POST" target="shipping_iframe" action="">
                <input id="customerTokenShipping" type="hidden" name="customerToken" value="">
            </form>

        </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
    </body>
    <script>
    document.addEventListener("DOMContentLoaded", function (e) {
    });
    function go(){
        customerToken = document.getElementById('customerToken').value;

        document.getElementById('cardsIframe').style.display = "block";
        var cardsForm = document.getElementById('cards_form');
        document.getElementById('customerTokenCards').value = customerToken;
        if (cardsForm){
            cardsForm.action = "edit_cards.php";
            cardsForm.submit();
        }
        document.getElementById('shippingIframe').style.display = "block";
        var shippingForm = document.getElementById('shipping_form');
        document.getElementById('customerTokenShipping').value = customerToken;
        if (shippingForm){
            shippingForm.action = "edit_addresses.php";
            shippingForm.submit();
        }
    }
    </script>
</html>
