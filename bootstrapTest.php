<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css/stylesTest.css"/>
    <title>Bootstrap Test</title>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
    <div class="container d-flex justify-content-center mt-5 mb-5">
        <div class="row g-3">
            <div class="col-md-6"> 
                <div class="card">
                    <div class="accordion" id="accordionExample">
                        <div class="card">
                            <div class="card-header p-0">
                            </div>
                            <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
                                <div class="card-body payment-card-body"> 
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="icons">
                                                <img src="images/Visa.svg" width="30">
                                                <img src="images/Mastercard.svg" width="30">
                                                <img src="images/Amex.svg" width="30">
                                            </div>
                                        </div>
                                    <span class="font-weight-normal card-text">Card Number</span>
                                    <div class="input">
                                        <i class="fa fa-credit-card"></i>
                                        <div id="number-container" class="form-control form-control-sm"></div>
                                    </div>
                                    <div class="row mt-3 mb-3">
                                        <div class="col-md-6"> <span class="font-weight-normal card-text">Expiry Date</span>
                                            <div class="input">
                                                <i class="fa fa-calendar"></i>
                                                <input class="form-control" id="expiryDate" type="text" placeholder="MM/YY" pattern="[0-1][0-9]\/[2][1-9]" inputmode="numeric" autocomplete="cc-exp" autocorrect="off" spellcheck="off" aria-invalid="false" aria-placeholder="MM/YY" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6"> 
                                            <label id="securityCodeLabel" class="form-check-label" for="securityCode-container">Security Code</label>
                                            <div class="input"> <i class="fa fa-lock"></i>
                                                <div id="securityCode-container" class="form-control form-control-sm"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="newCardButtonSection" class="row">
                                        <div class="col-sm-6">
                                            <button type="button" class="btn btn-primary" onclick="pay()" id="newCardButton" disabled="true">Pay</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6"> <span>Summary</span>
                <div class="card">
                    <div class="d-flex justify-content-between p-3">
                        <div class="d-flex flex-column"> <span>Pro(Billed Monthly) <i class="fa fa-caret-down"></i></span> <a href="#" class="billing">Save 20% with annual billing</a> </div>
                        <div class="mt-1"> <sup class="super-price">$9.99</sup> <span class="super-month">/Month</span> </div>
                    </div>
                    <hr class="mt-0 line">
                    <div class="p-3">
                        <div class="d-flex justify-content-between mb-2"> <span>Refferal Bonouses</span> <span>-$2.00</span> </div>
                        <div class="d-flex justify-content-between"> <span>Vat <i class="fa fa-clock-o"></i></span> <span>-20%</span> </div>
                    </div>
                    <hr class="mt-0 line">
                    <div class="p-3 d-flex justify-content-between">
                        <div class="d-flex flex-column"> <span>Today you pay(US Dollars)</span> <small>After 30 days $9.59</small> </div> <span>$0</span>
                    </div>
                    <div class="p-3"> <button class="btn btn-primary btn-block free-button">Try it free for 30 days</button>
                        <div class="text-center"> <a href="#">Have a promo code?</a> </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
    <script src="js/expiryDate.js"></script>
</body>
<script src="https://flex.cybersource.com/cybersource/assets/microform/0.11/flex-microform.min.js"></script>
<script src="js/newCard.js"></script>
<script>
    function pay(){
        
    }
</script>
</html>
