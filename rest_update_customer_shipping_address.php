<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/payPage/PeRestLib/RestRequest.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/payPage/php/utils/addresses.php';

$incoming = json_decode(file_get_contents('php://input'));
try {
    if($incoming->setDefaultOnly){
        $request = [
            "default" => true
        ];
    }else{
        $request = [
            "default" => $incoming->default,
            "shipTo" => [
                "firstName" => substr(ppTrim($incoming->firstName), 0, MAXSIZE_NAME),
                "lastName" => substr(ppTrim($incoming->lastName), 0, MAXSIZE_NAME),
                "address1" => substr(ppTrim($incoming->address1), 0, MAXSIZE_ADDRESS),
                "address2" => substr(ppTrim($incoming->address2), 0, MAXSIZE_ADDRESS),
                "locality" => substr(ppTrim($incoming->locality), 0, MAXSIZE_CITY),
                "administrativeArea" => substr(ppTrim($incoming->administrativeArea), 0, MAXSIZE_STATE),
                "postalCode" => substr(ppTrim($incoming->postalCode), 0, MAXSIZE_POSTCODE),
                "country" => substr(ppTrim($incoming->country), 0, MAXSIZE_COUNTRY)
    //            "phoneNumber" => "321321"
            ]
        ];
    }
    $requestBody = json_encode($request);
    $api = API_TMS_V2_CUSTOMERS . "/" .$incoming->customerId . "/shipping-addresses/" . $incoming->shippingAddressId;

    $result = ProcessRequest(PORTFOLIO, $api , METHOD_PATCH, $requestBody, MID, AUTH_TYPE_SIGNATURE );
    echo(json_encode($result));
} catch (Exception $exception) {
    echo(json_encode($exception));
}
?>
