<?php require_once 'PeRestLib/RestRequest.php';
$incoming = json_decode(file_get_contents('php://input'));
try {
    if($incoming->setDefaultOnly){
        $request = [
            "default" => true
        ];
    }else{
        $request = [
            "default" => $incoming->default,
            "billTo" => [
                "firstName" => $incoming->firstName,
                "lastName" => $incoming->lastName,
                "address1" => $incoming->address1,
                "address2" => $incoming->address2,
                "locality" => $incoming->locality,
                "administrativeArea" => $incoming->administrativeArea,
                "postalCode" => $incoming->postalCode,
                "country" => $incoming->country
    //            "phoneNumber" => "321321"
            ]
        ];
    }

    $requestBody = json_encode($request);

    $api = API_TMS_V2_CUSTOMERS . "/" .$incoming->customerId . "/payment-instruments/" . $incoming->paymentInstrumentId;

    $result = ProcessRequest("peportfolio", $api , METHOD_PATCH, $requestBody, "pemid03", AUTH_TYPE_SIGNATURE );
    echo(json_encode($result));

} catch (Exception $exception) {
    echo(json_encode($exception));
}
?>
