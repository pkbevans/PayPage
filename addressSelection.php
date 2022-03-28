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
//     echo("<BR> BODY<PRE>" .json_encode($result, JSON_PRETTY_PRINT). "</PRE><BR>");
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
<div class="d-grid gap-2">
<?php foreach ($shippingAddresses as $shippingAddress): ?>
    <div class="row">
        <input type="hidden" id="<?php echo "sa_" . $shippingAddress->id ;?>" value='<?php echo json_encode($shippingAddress);?>'>
        <button type="button" class="btn btn-primary" onclick="useShippingAddress('<?php echo $shippingAddress->id;?>')"> 
            <div class="row">
                <div class="col-10">
                    <ul class="list-unstyled">
                        <li><small><div id="<?php echo "shippingAddress_". $shippingAddress->id;?>"><?php echo concatinateNameAddress($shippingAddress->shipTo);?></div></small></li>
                    </ul>
                </div>
            </div>
        </button>
    </div>
<?php endforeach; ?>
    <div class="row">
        <button type="button" class="btn btn-primary" onclick="showNewAddress()">Use a different address</button>
    </div>
</div>
<?php endif?>
    <div id="shippingSection" style="display: <?php echo ($count>0?'none':'block')?>">
    <form id="shippingForm" class="needs-validation" novalidate>
        <div class="row">
            <div class="col-12"><h5>Please enter the delivery address</h5></div>
        </div>
        <div id="shippingDetailsSection" class="form-group">
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
                        <input id="ship_to_address_line1" type="text" class="form-control form-control-sm" value="" placeholder="1st line of address" maxlength="60" required>
                        <label for="ship_to_address_line1" class="form-label">Address line 1*</label>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group form-floating mb-3">
                        <input id="ship_to_address_line2" type="text" class="form-control form-control-sm" value="" placeholder="2nd line of address" maxlength="60">
                            <label for="ship_to_address_line2" class="form-label">Address line 2</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-6">
                    <div class="form-group form-floating mb-3">
                        <input id="ship_to_address_city" type="text" class="form-control form-control-sm" value="" placeholder="City/County" maxlength="50" required>
                        <label for="ship_to_address_city" class="form-label">City/County*</label>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group form-floating mb-3">
                        <input id="ship_to_postcode" type="text" class="form-control form-control-sm" value="" placeholder="Postcode" maxlength="10" required>
                        <label for="ship_to_postcode" class="form-label">PostCode*</label>
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-12">
                    <div class="form-floating">
                        <select id="ship_to_address_country" class="form-control form-control-sm">
<?php
foreach ($countries as $key => $value) {
echo "<option value=\"". $key ."\">" . $value . "</option>\n";
}
?>
                        </select>
                        <label for="ship_to_address_country" class="form-label">Country*</label>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="row">
        <div class="col-3"></div>
        <div class="col-9">
            <button type="button" class="btn btn-primary" onclick="useShippingAddress('NEW')">Next</button>
            <button type="button" class="btn btn-secondary" onclick="cancel()">Cancel</button>
        </div>
    </div>
</div>
    
