<?php
include 'rest_get_customer.php';
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
        <title>Get Customer Details</title>
    </head>
    <body>
        <div class="container-fluid">
            <pre>
<?php
    echo "paymentInstrumentCount:&nbsp;&nbsp;" . $paymentInstrumentCount . "<BR>";
    echo "shippingAddressAvailable:&nbsp;&nbsp;" . $shippingAddressAvailable . "<BR>"; 
    echo "storedCards:&nbsp;&nbsp;" . json_encode($storedCards, JSON_PRETTY_PRINT) . "<BR>"; 
    echo "defaultPaymentInstrument:&nbsp;&nbsp;" . json_encode($defaultPaymentInstrument, JSON_PRETTY_PRINT) . "<BR>";
    echo "defaultShippingAddress:&nbsp;&nbsp;" . json_encode($defaultShippingAddress, JSON_PRETTY_PRINT) . "<BR>";
    echo "billToText:&nbsp;&nbsp;" . $billToText . "<BR>";
    echo "shipToText:&nbsp;&nbsp;" . $shipToText . "<BR>";
?>
            </pre>
        </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
    <script>
    </script>
    </body>
</html>
