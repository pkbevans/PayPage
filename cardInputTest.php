<!DOCTYPE html>
<html lang="en-GB">
<head   >
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <!--<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">-->
    <link rel="stylesheet" type="text/css" href="css/styles.css"/>
    <title>Bootstrap Test</title>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
    <div class="container">
        <div id="progressSpinner"  class="spinner-border text-info" style="display: block;"></div>
        <div id="cardInput">
        </div>
        <div class="row mt-3 mb-3">
            <div class="col-sm-6">
            <button type="button" class="btn btn-primary" onclick="getToken(tokenCallback)" id="payButton" disabled="true">Pay</button>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
</body>
<script src="https://flex.cybersource.com/cybersource/assets/microform/0.11/flex-microform.min.js"></script>
<script src="js/newCard2.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function (e) {
        createCardInput("cardInput", "progressSpinner", "payButton" );
    });
    function tokenCallback(result){
        console.log(result);
    }
</script>
</html>
