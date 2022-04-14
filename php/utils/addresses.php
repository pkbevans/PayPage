<?php

function concatinateNameAddress($nameAddress){
    // return name and address string
    if(!isset($nameAddress->address2)){
        $nameAddress->address2 = "";
    }
    return "<p class=\"fs-6\">" . "<b>" .xtrim($nameAddress->firstName, " ") .
            xtrim($nameAddress->lastName, "</b><br>") .
            xtrim($nameAddress->address1, ", ") .
            xtrim($nameAddress->address2, ", ") .
            xtrim($nameAddress->locality, ", ") .
            xtrim($nameAddress->postalCode, ", ") .
            xtrim($nameAddress->country, ".</p>");
}
function xtrim($in, $suffix){
    $out = trim($in);
    return (empty($out)? "" : $out . $suffix );
}
function ppTrim($in){
    return rtrim($in, " ,.");
}