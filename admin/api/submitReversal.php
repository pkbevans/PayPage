<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/common/PeRestLib/RestRequest.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/common/db/paymentUtils.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/common/v1/controller/db.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/common/v1/controller/ordersFunctions.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/common/v1/model/Response.php';

$incoming = json_decode(file_get_contents('php://input'));

// Validate that user has Admin permissions
try {
    $readDB = DB::connectReadDB();
    $response = validateAccessToken($readDB, $incoming->accessToken);
    if($response->success()){
        $data = $response->getData();
        if( !($data['admin'] === 'Y' && $data['type'] === "INTERNAL")){
            $response = new Response(405, false, "You are not authorised to perform this action", null);
            $response->send();
            exit;
        }
    }else{
        $response->send();
        exit;
    }
} catch (PDOException $ex) {
    // log connection error for troubleshooting and return a json error response
    error_log("Connection Error: " . $ex, 0);
    $response = new Response(500, false, "Database connection error", null);
    $response->send();
    exit;
}

$result = new stdClass();
try {
    $request = [
        "clientReferenceInformation" => [
            "code"  =>  $incoming->referenceNumber
        ],
        "reversalInformation" => [
            "amountDetails" => [
                "totalAmount"  =>  $incoming->amount,
                "currency"  =>  $incoming->currency
            ]
        ],
        "reason" => "TODO"
    ];
    $requestBody = json_encode($request);
    // Get Customer
    $api = API_PAYMENTS . "/" . $incoming->requestId . "/reversals";
    $result = ProcessRequest(MID, $api , METHOD_POST, $requestBody, CHILD_MID, AUTH_TYPE_SIGNATURE );
    $dbError = "";
    if($result->responseCode == 201){
        if( !$dbResult = insertPayment($incoming->accessToken, "REVERSAL", $incoming->orderId, $incoming->amount, 0, $incoming->currency,
            $incoming->cardNumber,"n/a","n/a", $result->response->id, "SUBMITTED")){
            $dbError .= "DBError inserting Payment. ";
            $result->dberror = $dbError;
        }else{
            $result->insertPayment = $dbResult;
        }
        if( !$dbResult = updateOrder($incoming->accessToken, $incoming->orderId, "REVERSED")){
            $dbError .= "DBError updating Order";
            $result->dberror = $dbError;
        }else{
            $result->updateOrder = $dbResult;
        }
    }
} catch (Exception $exception) {
    $result->responseCode = 500;
    $result->exception = $exception;
}
$response = new Response(200, true, "success", $result);
$response->send();
?>
