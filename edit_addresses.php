<?php
include "rest_get_customer_addresses.php";
include_once 'countries.php';
////////////////////////////////////FUNCTIONS
function concatinateNameAddress($nameAddress){
    // return name and address string
    if(!isset($nameAddress->address2)){
        $nameAddress->address2 = "";
    }
    return xtrim($nameAddress->firstName, " ") .
            xtrim($nameAddress->lastName, ", ") .
            xtrim($nameAddress->address1, ", ") .
            xtrim($nameAddress->address2, ", ") .
            xtrim($nameAddress->locality, ", ") .
            xtrim($nameAddress->postalCode, ", ") .
            xtrim($nameAddress->country, ".");
}

function xtrim($in, $suffix){
    $out = trim($in);
    return (empty($out)? "" : $out . $suffix );
}
///////////////////////////////////END FUNCTIONS
///////////////////////////////////VARIABLES
$count=0;
?>
<!DOCTYPE html>
<html lang="en-GB">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
        <title>Manage Addresses</title>
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    </head>
    <body>
        <h5>Your Delivery Addresses</h5>
        <div class="accordion" id="accordionExample">
<?php       foreach ($shippingAddresses as $shippingAddress): ?>
            <div class="accordion-item" id="<?php echo $shippingAddress->id;?>_item">
                <h2 class="accordion-header" id="heading<?php echo $shippingAddress->id;?>">
                        <button class="accordion-button <?php echo ($shippingAddress->default?"":"collapsed");?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $shippingAddress->id;?>" aria-expanded="true" aria-controls="collapse<?php echo $shippingAddress->id;?>">
                            <div class="container">
                                <div class="row">
                                    <div class="col-sm-3">
                                        <h5><?php $count++;echo ($shippingAddress->default?"Default Delivery Address." :"Delivery Address #". $count );?></h5>
                                    </div>
                                    <div class="col-sm-8">
                                        <span id="<?php echo $shippingAddress->id;?>_textArea" class="form-control form-control-sm"><?php echo concatinateNameAddress($shippingAddress->shipTo);?></span>
                                    </div>
                                </div>
                            </div>
                    </button>
                </h2>
                <div id="collapse<?php echo $shippingAddress->id;?>" class="accordion-collapse collapse <?php echo ($shippingAddress->default?"show":"");?>" aria-labelledby="heading<?php echo $shippingAddress->id;?>" data-bs-parent="#accordionExample">
                    <div class="accordion-body">
                    <div class="row">
                        <div id="<?php echo $shippingAddress->id;?>_buttons">
                            <div class="col-sm-6">
                                <button type="button" class="btn btn-link" onclick="useShippingAddress('<?php echo $shippingAddress->id;?>')">Use this address</button>
                                <button type="button" class="btn btn-link" onclick="editShippingAddress('<?php echo $shippingAddress->id;?>')">Edit</button>
<?php if(!$shippingAddress->default):?>
                            <button type="button" class="btn btn-link" onclick="updateShippingAddress('<?php echo $shippingAddress->id;?>',true)">Set as default</button>
                            <button type="button" class="btn btn-link" onclick="deleteShippingAddress('<?php echo $shippingAddress->id;?>')">Remove</button>
<?php endif?>
                            </div>
                        </div>
                        <form id="<?php echo $shippingAddress->id;?>_form" style="display: none">
                            <div>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <div class="form-group form-floating mb-3">
                                            <input id="<?php echo $shippingAddress->id;?>_firstName" type="text" class="form-control form-control-sm" value="<?php echo $shippingAddress->shipTo->firstName;?>" placeholder="First name" required>
                                            <label for="<?php echo $shippingAddress->id;?>_firstName" class="form-label">First name*</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group form-floating mb-3">
                                            <input id="<?php echo $shippingAddress->id;?>_lastName" type="text" class="form-control form-control-sm" value="<?php echo $shippingAddress->shipTo->lastName;?>" placeholder="Last Name" required>
                                            <label for="<?php echo $shippingAddress->id;?>_lastName" class="form-label">Surname*</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <div class="form-group form-floating mb-3">
                                            <input id="<?php echo $shippingAddress->id;?>_address1" type="text" class="form-control form-control-sm" value="<?php echo $shippingAddress->shipTo->address1;?>" placeholder="1st line of address" required>
                                            <label for="<?php echo $shippingAddress->id;?>_address1" class="form-label">Address line 1*</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group form-floating mb-3">
                                            <input id="<?php echo $shippingAddress->id;?>_address2" type="text" class="form-control form-control-sm" value="<?php echo (isset($shippingAddress->shipTo->address2)?$shippingAddress->shipTo->address2:"");?>" placeholder="2nd line of address">
                                            <label for="<?php echo $shippingAddress->id;?>_address2" class="form-label">Address line 2</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <div class="form-group form-floating mb-3">
                                            <input id="<?php echo $shippingAddress->id;?>_locality" type="text" class="form-control form-control-sm" value="<?php echo $shippingAddress->shipTo->locality;?>" placeholder="City/County" required>
                                            <label for="<?php echo $shippingAddress->id;?>_locality" class="form-label">City/County*</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group form-floating mb-3">
                                            <input id="<?php echo $shippingAddress->id;?>_postalCode" type="text" class="form-control form-control-sm" value="<?php echo $shippingAddress->shipTo->postalCode;?>" placeholder="Postcode" required>
                                            <label for="<?php echo $shippingAddress->id;?>_postalCode" class="form-label">PostCode*</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group form-floating mb-3">
                                            <select id="<?php echo $shippingAddress->id;?>_country" class="form-select">
<?php
foreach ($countries as $key => $value) {
    echo "<option value=\"". $key ."\"" . ( $shippingAddress->shipTo->country == $key? "selected": "") . ">" . $value . "</option>\n";
}
?>
                                            </select>
                                            <label for="<?php echo $shippingAddress->id;?>_address_country" class="form-label">Country*</label>
                                        </div>
                                    </div>
                                </div>
                        *Required fields
                            </div>
<?php if(!$shippingAddress->default):?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" id="<?php echo $shippingAddress->id;?>_defaultAddress" <?php echo ($shippingAddress->default?"checked ":"");?>>
                                <label class="form-check-label" for="flexCheckDefault">Make this my default address</label>
                            </div>
<?php endif?>
                            <div class="row">
                                <div class="col-sm-1">
                                    <button type="button" class="btn btn-link" onclick="updateShippingAddress('<?php echo $shippingAddress->id;?>',false)">Save</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    </div>
                </div>
            </div>
<?php endforeach; ?>
            <div class="accordion-item" id="add_item">
                <h2 class="accordion-header" id="headingAdd">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAdd" aria-expanded="true" aria-controls="collapseAdd">
                        Add New Address
                    </button>
                </h2>
                <div id="collapseAdd" class="accordion-collapse collapse" aria-labelledby="headingAdd" data-bs-parent="#accordionExample">
                    <div class="accordion-body">
                    <div class="row">
                        <form id="add_form" style="display: block">
                            <div>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <div class="form-group form-floating mb-3">
                                            <input id="add_firstName" type="text" class="form-control form-control-sm" value="" placeholder="First name" required>
                                            <label for="add_firstName" class="form-label">First name*</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group form-floating mb-3">
                                            <label for="add_lastName" class="form-label">Surname*</label>
                                            <input id="add_lastName" type="text" class="form-control form-control-sm" value="" placeholder="Last Name" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <div class="form-group form-floating mb-3">
                                            <label for="add_address1" class="form-label">Address line 1*</label>
                                            <input id="add_address1" type="text" class="form-control form-control-sm" value="" placeholder="1st line of address" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group form-floating mb-3">
                                            <label for="add_address2" class="form-label">Address line 2</label>
                                            <input id="add_address2" type="text" class="form-control form-control-sm" value="" placeholder="2nd line of address">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <div class="form-group form-floating mb-3">
                                            <label for="add_locality" class="form-label">City/County*</label>
                                            <input id="add_locality" type="text" class="form-control form-control-sm" value="" placeholder="City/County" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group form-floating mb-3">
                                            <label for="add_postalCode" class="form-label">PostCode*</label>
                                            <input id="add_postalCode" type="text" class="form-control form-control-sm" value="" placeholder="Postcode" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group form-floating mb-3">
                                            <select id="add_country" class="form-select">
<?php
foreach ($countries as $key => $value) {
    echo "<option value=\"". $key ."\">" . $value . "</option>\n";
}
?>
                                            </select>
                                            <label for="add_address_country" class="form-label">Country*</label>
                                        </div>
                                    </div>
                                </div>
                        *Required fields
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" id="add_defaultAddress" >
                                <label class="form-check-label" for="flexCheckDefault">Make this my default address</label>
                            </div>
                            <div class="row">
                                <div class="col-sm-1">
                                    <button type="button" class="btn btn-link" onclick="addShippingAddress()">Save</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="row">
            <div class="col-sm-1">
                <button type="button" class="btn btn-link" onclick="cancel()">Cancel</button>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
    </body>
    <script>
    var customerId = "<?php echo $customerToken;?>";

    function editShippingAddress(id){
        document.getElementById(id+"_form").style.display = "block";
        document.getElementById(id+"_buttons").style.display = "none";
    }
    function updateShippingAddress(id, setDefaultOnly){
        console.log("\nUpdating Shipping Address: "+id);
        def = document.getElementById(id+"_defaultAddress");
        if(def){
            // This address is NOT the default address
            defaultAddress = def.checked;
        }else{
            // This address is currently the default address
            defaultAddress = true;
        }
        firstName = document.getElementById(id+"_firstName").value;
        lastName = document.getElementById(id+"_lastName").value;
        address1 = document.getElementById(id+"_address1").value;
        address2 = document.getElementById(id+"_address2").value;
        locality = document.getElementById(id+"_locality").value;
        postalCode = document.getElementById(id+"_postalCode").value;
        country = document.getElementById(id+"_country").value;

        $.ajax({
            type: "POST",
            url: "rest_update_customer_shipping_address.php",
            data: JSON.stringify({
                "setDefaultOnly": setDefaultOnly,
                "customerId": customerId,
                "shippingAddressId": id,
                "default": defaultAddress,
                "firstName": firstName,
                "lastName": lastName,
                "address1": address1,
                "address2": address2,
                "locality": locality,
                "administrativeArea": "",
                "postalCode": postalCode,
                "country": country,
                "phoneNumber": ""
            }),
            success: function (result) {
                // Response is a json string - turn it into a javascript object
                let res = JSON.parse(result);
                console.log("\nUpdate:\n" + JSON.stringify(res, undefined, 2));
                let httpCode = res.httpCode;
                let status = res.response.status;
                if (httpCode === "200") {
                    // Successfull response
                    location.reload();
                } else {
                    // 500 System error or anything else
                }
            }
        });
    }
    function cancel(){
        parent.onIframeCancelled();
    }
    function useShippingAddress(id){
        shipToText = document.getElementById(id+"_textArea").innerHTML;
        parent.onShippingAddressUpdated(id, shipToText);
    }
    function addShippingAddress(){
        console.log("\nAdding Shipping Address");
        def = document.getElementById("add_defaultAddress");
        if(def){
            defaultAddress = def.checked;
        }else{
            defaultAddress = false;
        }
        firstName = document.getElementById("add_firstName").value;
        lastName = document.getElementById("add_lastName").value;
        address1 = document.getElementById("add_address1").value;
        address2 = document.getElementById("add_address2").value;
        locality = document.getElementById("add_locality").value;
        postalCode = document.getElementById("add_postalCode").value;
        country = document.getElementById("add_country").value;

        $.ajax({
            type: "POST",
            url: "rest_add_customer_shipping_address.php",
            data: JSON.stringify({
                "customerId": customerId,
                "default": defaultAddress,
                "firstName": firstName,
                "lastName": lastName,
                "address1": address1,
                "address2": address2,
                "locality": locality,
                "administrativeArea": "",
                "postalCode": postalCode,
                "country": country,
                "phoneNumber": ""
            }),
            success: function (result) {
                // Response is a json string - turn it into a javascript object
                let res = JSON.parse(result);
                console.log("\Add:\n" + JSON.stringify(res, undefined, 2));
                let httpCode = res.httpCode;
                let status = res.response.status;
                if (httpCode === "201") {
                    // Successfull response
                    location.reload();
                } else {
                    // 500 System error or anything else
                }
            }
        });
    }
    function deleteShippingAddress(id){
        console.log("\nDeleting Shipping Address: "+id);
        $.ajax({
            type: "POST",
            url: "rest_delete_customer_shipping_address.php",
            data: JSON.stringify({
                "customerId": customerId,
                "shippingAddressId": id
            }),
            success: function (result) {
                // Response is a json string - turn it into a javascript object
                let res = JSON.parse(result);
                console.log("\nDelete:\n" + JSON.stringify(res, undefined, 2));
                let httpCode = res.httpCode;
                if (httpCode === "204") {
                    // Successfull response
                    location.reload();
                } else {
                    // 500 System error or anything else - TODO
                }
            }
        });
    }
    </script>
</html>
