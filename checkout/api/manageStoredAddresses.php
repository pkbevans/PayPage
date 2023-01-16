<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/payPage/common/PeRestLib/RestRequest.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/checkout/php/utils/countries.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/checkout/php/utils/cards.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/checkout/php/utils/addresses.php';
$count=0;
$shippingAddresses = new stdClass();
try {
    // Get Shipping Addresses
    $api = str_replace('{customerId}', $_REQUEST['customerId'], API_TMS_V2_CUSTOMER_SHIPPING_ADDRESSES);

    $result = ProcessRequest(MID, $api , METHOD_GET, "", CHILD_MID, AUTH_TYPE_SIGNATURE );
//    echo("<BR> BODY<PRE>" .json_encode($result, JSON_PRETTY_PRINT). "</PRE><BR>");
    if($result->responseCode === 200){
        $count = $result->response->count;
        if(isset($result->response->_embedded->shippingAddresses)){
            $shippingAddresses = $result->response->_embedded->shippingAddresses;
        }else{
            // IGNORE
        }
    }else{
        // IGNORE
    }
} catch (Exception $exception) {
    echo(json_encode($exception));
}?>
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="/payPage/common/css/styles.css"/>
    <title>Manage Addresses</title>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div id="addressSelection">
                    <?php if ($count>0): ?>
                    <div id="storedAddressSection">
                        <ul class="list-group ">
                    <?php foreach ($shippingAddresses as $shippingAddress): ?>
                        <li class="list-group-item <?php echo ($shippingAddress->default?"list-group-item-primary":"");?>">
                    <?php if($shippingAddress->default):?>
                            <div class="row"><div class="col-12"><strong>*Default Address</strong></div></div>
                    <?php endif?>
                            <div style="max-height: 999999px;"><?php echo concatinateNameAddress($shippingAddress->shipTo);?></div>
                            <form id="<?php echo $shippingAddress->id;?>_form" style="display: none">
                                <div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group form-floating mb-3">
                                                <input id="<?php echo $shippingAddress->id;?>_firstName" type="text" class="form-control form-control-sm" value="<?php echo $shippingAddress->shipTo->firstName;?>" placeholder="First name" maxlength="60" required>
                                                <label for="<?php echo $shippingAddress->id;?>_firstName" class="form-label">First name*</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group form-floating mb-3">
                                                <input id="<?php echo $shippingAddress->id;?>_lastName" type="text" class="form-control form-control-sm" value="<?php echo $shippingAddress->shipTo->lastName;?>" placeholder="Last Name" maxlength="60" required>
                                                <label for="<?php echo $shippingAddress->id;?>_lastName" class="form-label">Surname*</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group form-floating mb-3">
                                                <input id="<?php echo $shippingAddress->id;?>_address1" type="text" class="form-control form-control-sm" value="<?php echo $shippingAddress->shipTo->address1;?>" placeholder="1st line of address" maxlength="60" required>
                                                <label for="<?php echo $shippingAddress->id;?>_address1" class="form-label">Address line 1*</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group form-floating mb-3">
                                                <input id="<?php echo $shippingAddress->id;?>_address2" type="text" class="form-control form-control-sm" value="<?php echo (isset($shippingAddress->shipTo->address2)?$shippingAddress->shipTo->address2:"");?>" placeholder="2nd line of address" maxlength="60">
                                                <label for="<?php echo $shippingAddress->id;?>_address2" class="form-label">Address line 2</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group form-floating mb-3">
                                                <input id="<?php echo $shippingAddress->id;?>_locality" type="text" class="form-control form-control-sm" value="<?php echo $shippingAddress->shipTo->locality;?>" placeholder="City/County" maxlength="50" required>
                                                <label for="<?php echo $shippingAddress->id;?>_locality" class="form-label">City/County*</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group form-floating mb-3">
                                                <input id="<?php echo $shippingAddress->id;?>_postalCode" type="text" class="form-control form-control-sm" value="<?php echo $shippingAddress->shipTo->postalCode;?>" placeholder="Postcode" maxlength="10" required>
                                                <label for="<?php echo $shippingAddress->id;?>_postalCode" class="form-label">PostCode*</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12">
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
                                        <button type="button" class="btn btn-primary" onclick="updateAddress('<?php echo $shippingAddress->id;?>',false)">Save</button>
                                    </div>
                                </div>
                            </form>
                        <div id="<?php echo $shippingAddress->id."_buttons";?>">
                            <div class="row">
                                <div class="col-3">
                                    <button type="button" class="btn btn-link" onclick="editAddress('<?php echo $shippingAddress->id;?>')">Edit</button>
                                </div>
                                <div class="col-3">
                                    <button type="button" class="btn btn-link" onclick="updateAddress('<?php echo $shippingAddress->id;?>', true)" <?php echo ($shippingAddress->default?"disabled":"")?>>Make default</button>
                                </div>
                                <div class="col-3">
                                    <button type="button" class="btn btn-link" onclick="deleteAddress('<?php echo $shippingAddress->id;?>')"  <?php echo ($shippingAddress->default?"disabled":"")?>>Remove</button>
                                </div>
                            </div>
                        </div>
                        </li>
                    <?php endforeach; ?>
                        <li class="list-group-item" id="newAddressButton">
                            <div class="row">
                                <button type="button" class="btn btn-primary" onclick="newAddress()">Add a new address</button>
                            </div>
                        </li>
                        </ul>

                    </div>
                    <?php endif?>
                    <div id="newAddressSection" style="display: <?php echo ($count>0?'none':'block')?>">
                        <form id="newAddressForm" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-12"><h5>Please enter the delivery address</h5></div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group form-floating mb-3">
                                            <input id="ship_to_firstName" type="text" class="form-control form-control-sm" value="" placeholder="First name" maxlength="60" required>
                                            <label for="ship_to_firstName" class="form-label">First name*</label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group form-floating mb-3">
                                            <input id="ship_to_lastName" type="text" class="form-control form-control-sm" value="" placeholder="Last Name" maxlength="60" required>
                                            <label for="ship_to_lastName" class="form-label">Surname*</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group form-floating mb-3">
                                            <input id="ship_to_address1" type="text" class="form-control form-control-sm" value="" placeholder="1st line of address" maxlength="60" required>
                                            <label for="ship_to_address1" class="form-label">Address line 1*</label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group form-floating mb-3">
                                            <input id="ship_to_address2" type="text" class="form-control form-control-sm" value="" placeholder="2nd line of address" maxlength="60">
                                                <label for="ship_to_address2" class="form-label">Address line 2</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group form-floating mb-3">
                                            <input id="ship_to_locality" type="text" class="form-control form-control-sm" value="" placeholder="City/County" maxlength="50" required>
                                            <label for="ship_to_locality" class="form-label">City/County*</label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group form-floating mb-3">
                                            <input id="ship_to_postalCode" type="text" class="form-control form-control-sm" value="" placeholder="Postcode" maxlength="10" required>
                                            <label for="ship_to_postalCode" class="form-label">PostCode*</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <select id="ship_to_country" class="form-control form-control-sm">
                    <?php
                    foreach ($countries as $key => $value) {
                    echo "<option value=\"". $key ."\">" . $value . "</option>\n";
                    }
                    ?>
                                            </select>
                                            <label for="ship_to_country" class="form-label">Country*</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="row">
                            <div class="col-9">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="add_defaultAddress" >
                                    <label class="form-check-label" for="flexCheckDefault">Make this my default address</label>
                                </div>
                                <button type="button" class="btn btn-primary" onclick="addAddress()">Save</button>
                                <button type="button" class="btn btn-secondary" onclick="cancelAdd()">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="errorAlert" class="alert alert-danger fade show" role="alert" style="display:none">
            <strong><span id="alertText">Somethin went wrong. Please try again.</span></strong>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
</body>
<script src="https://flex.cybersource.com/cybersource/assets/microform/0.11/flex-microform.min.js"></script>
<script>
var customerId = "<?php echo $_REQUEST['customerId'];?>";
var errorAlert;
document.addEventListener("DOMContentLoaded", function (e) {
    errorAlert = document.getElementById("errorAlert");
});
function newAddress(){
    console.log("New Address");
    document.getElementById("newAddressSection").style.display = "block";
    document.getElementById("newAddressButton").style.display = "none";
    document.getElementById("storedAddressSection").style.display = "none";
}
function editAddress(id){
    document.getElementById(id+"_form").style.display = "block";
    document.getElementById(id+"_buttons").style.display = "none";
    document.getElementById("newAddressButton").style.display = "none";
}
function cancelAdd(){
    document.getElementById("newAddressSection").style.display = "none";
    document.getElementById("newAddressButton").style.display = "block";
    document.getElementById("storedAddressSection").style.display = "block";
}
function updateAddress(id, setDefaultOnly){
    console.log("\nUpdating Shipping Address: "+id);
    // Validate fields
    if(!setDefaultOnly){
        form = document.getElementById(id+"_form");
        if(!validateForm(form)){
            return;
        }
    }
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

    return fetch("/payPage/checkout/api/updateCustomerShippingAddress.php", {
        method: "post",
        body: JSON.stringify({
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
        })
    })
    .then((result) => {
        if (result.ok) {
            // Successfull response - reload this page and notify parent
            location.reload();
            parent.onStoredDataUpdated("ADDRESS", "UPDATE");
        } else {
            // 500 System error or anything else
            document.getElementById("alertText").innerHTML = "Update Failed.  Please check your internet connection and try again";
            errorAlert.style.display = "block";
            throw "Unable to update Address. "  + result.status + ":" + result.statusText;
        }        
    })
    .catch(error => console.error(error))
}
// function makeDefault(id){
    // console.log("makeDefault: "+id);
// }
function deleteAddress(id){
    console.log("\nDeleting Shipping Address: "+id);
    return fetch("/payPage/checkout/api/deleteCustomerShippingAddress.php", {
        method: "post",
        body: JSON.stringify({
            "customerId": customerId,
            "shippingAddressId": id
        })
    })
    .then((result) => {
        if (result.ok) {
            // Successfull response - reload this page and notify parent
            location.reload();
            parent.onStoredDataUpdated("ADDRESS", "DELETE");
        } else {
            // 500 System error or anything else
            document.getElementById("alertText").innerHTML = "Delete Failed.  Please check your internet connection and try again";
            errorAlert.style.display = "block";
            throw "Unable to update Address. "  + result.status + ":" + result.statusText;
        }        
    })
    .catch(error => console.error(error))
}
function addAddress(){
    console.log("\nAdding Shipping Address");
    // Validate fields
    form = document.getElementById('newAddressForm');
    if(!validateForm(form)){
        return;
    }
    def = document.getElementById("add_defaultAddress");
    if(def){
        defaultAddress = def.checked;
    }else{
        defaultAddress = false;
    }
    firstName = document.getElementById("ship_to_firstName").value;
    lastName = document.getElementById("ship_to_lastName").value;
    address1 = document.getElementById("ship_to_address1").value;
    address2 = document.getElementById("ship_to_address2").value;
    locality = document.getElementById("ship_to_locality").value;
    postalCode = document.getElementById("ship_to_postalCode").value;
    country = document.getElementById("ship_to_country").value;

    return fetch("/payPage/checkout/api/addCustomerShippingAddress.php", {
        method: "post",
        body: JSON.stringify({
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
        })
    })
    .then((result) => {
        if (result.ok) {
            // Successfull response - reload this page and notify parent
            location.reload();
            parent.onStoredDataUpdated("ADDRESS", "ADD");
        } else {
            // 500 System error or anything else
            document.getElementById("alertText").innerHTML = "Add Failed.  Please check your internet connection and try again";
            errorAlert.style.display = "block";
            throw "Unable to update Address. "  + result.status + ":" + result.statusText;
        }        
    })
    .catch(error => console.error(error))
}
function validateForm(form){
    if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
        form.classList.add('was-validated');
        return false;
    }
    return true;
}
function closeWindow(){
    console.log("Close");
}
</script>
</html>