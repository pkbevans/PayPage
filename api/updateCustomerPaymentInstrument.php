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
            "billTo" => [
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
    $api = API_TMS_V2_CUSTOMERS . "/" .$incoming->customerId . "/payment-instruments/" . $incoming->paymentInstrumentId;

    $result = ProcessRequest(MID, $api , METHOD_PATCH, $requestBody, CHILD_MID, AUTH_TYPE_SIGNATURE );
    if($result->responseCode == 200 ) {
        header('HTTP/1.1 ' . $result->responseCode . ' OK');
        echo (json_encode($result->response));
    }else{
        header('HTTP/1.1 ' . $result->responseCode . ' ERROR');
        echo json_encode($result);
    }
} catch (Exception $exception) {
    echo(json_encode($exception));
}
?>
