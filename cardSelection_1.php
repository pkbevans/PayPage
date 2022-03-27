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
$paymentInstruments = new stdClass();
try {
    // Get Payment Instruments
    $api = str_replace('{customerId}', $incoming->customerId, API_TMS_V2_CUSTOMER_PAYMENT_INSTRUMENTS);

    $result = ProcessRequest(PORTFOLIO, $api , METHOD_GET, "", MID, AUTH_TYPE_SIGNATURE );
    // echo("<BR> BODY<PRE>" .json_encode($result, JSON_PRETTY_PRINT). "</PRE><BR>");
    if($result->responseCode === 200){
        $count = $result->response->count;
        if(isset($result->response->_embedded->paymentInstruments)){
            $paymentInstruments = $result->response->_embedded->paymentInstruments;
            $jsonPaymentInstruments = json_encode($paymentInstruments);
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
<ul class="list-group">
<?php foreach ($paymentInstruments as $paymentInstrument): ?>
    <li class="list-group-item">
        <div class="form-check">
            <input class="form-check-input" type="radio" name="paymentInstrument" value="<?php echo $paymentInstrument->id;?>" <?php echo ($paymentInstrument->default?"checked":"");?>>
            <input type="hidden" id="<?php echo "pi_" . $paymentInstrument->id ;?>" value="<?php echo json_encode($paymentInstrument);?>">
            <div class="row" id="<?php echo "billToText_" . $paymentInstrument->id;?>">
                <div class="col-2">
                    <img src="images/<?php echo $cardTypes[$paymentInstrument->card->type]['image']?>" class="img-fluid" alt="<?php echo $cardTypes[$paymentInstrument->card->type]['alt'];?>">
                </div>
                <div class="col-10">
                    <ul class="list-unstyled">
                        <li><strong><?php echo $paymentInstrument->_embedded->instrumentIdentifier->card->number; ?></strong></li>
                        <li><small>Expires:&nbsp;<?php echo $paymentInstrument->card->expirationMonth . "/" . $paymentInstrument->card->expirationYear;?></small></li>
                        <li><small><?php echo concatinateNameAddress($paymentInstrument->billTo);?></small></li>
                    </ul>
                </div>
            </div>
        </div>
    </li>
<?php endforeach; ?>
    <li class="list-group-item">
        <input class="form-check-input" type="radio" name="paymentInstrument" value="NEW">
        <label class="form-check-label" for="exampleRadios1">Add a new card</label>
    </li>
</ul>
<?php endif?>
