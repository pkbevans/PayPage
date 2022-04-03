<?php
require_once 'PeRestLib/RestRequest.php';
include_once 'countries.php';
include_once 'card_types.php';
////////////////////////////////////FUNCTIONS
function concatinateNameAddress($nameAddress){
    // return name and address string
    if(!isset($nameAddress->address2)){
        $nameAddress->address2 = "";
    }
    return xtrim($nameAddress->firstName, " ") .
            xtrim($nameAddress->lastName, "<BR>") .
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
$incoming = json_decode(file_get_contents('php://input'));
$shippingAddresses = new stdClass();
try {
    // Get Shipping Addresses
    $api = str_replace('{customerId}', $incoming->customerId, API_TMS_V2_CUSTOMER_SHIPPING_ADDRESSES);

    $result = ProcessRequest(PORTFOLIO, $api , METHOD_GET, "", MID, AUTH_TYPE_SIGNATURE );
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
<?php if ($count>0): ?>
<div>
    <ul class="list-group ">
<?php foreach ($shippingAddresses as $shippingAddress): ?>
    <li class="list-group-item <?php echo ($shippingAddress->default?"list-group-item-primary":"");?>">
<?php if($shippingAddress->default):?>
        <div class="row"><div class="col-12"><strong>*Default Address</strong></div></div>
<?php endif?>
        <small><div><?php echo concatinateNameAddress($shippingAddress->shipTo);?></div></small>
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
                    <button type="button" class="btn btn-link" onclick="updateAddress('<?php echo $shippingAddress->id;?>',false)">Save</button>
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
    <li class="list-group-item">
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
        </div>
    </div>
</div>