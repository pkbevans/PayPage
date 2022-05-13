<?php

$cardTypes = [
    "001" => [
        "name" => "visa",
        "image" => "Visa.svg",
        "alt" => "Visa logo"
    ],
    "002" => [
        "name" => "Mastercard",
        "image" => "Mastercard.svg",
        "alt" => "Mastercard logo"
    ],
    "003" => [
        "name" => "Amex",
        "image" => "Amex.svg",
        "alt" => "Amex logo"
    ]
];

function cardExpired($paymentInstrument){
    $currentYear=date("Y");
    $currentMonth=date("m");
    if($paymentInstrument->card->expirationYear<$currentYear ||
            ($paymentInstrument->card->expirationYear==$currentYear &&
            $paymentInstrument->card->expirationMonth<$currentMonth)){
        return true;
    }else{
        return false;
    }
}
