<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/payPage/PeRestLib/RestRequest.php';
include '../db/paymentUtils.php';
// $incoming = json_decode(file_get_contents('php://input'));
// $orderId= $incoming->orderId;
$orderId= $_REQUEST['orderId'];
echo "<BR>order=". $orderId;
$result = new stdClass();
try {
    // $api = API_TSS_V2_TRANSACTIONS . "/" . $incoming->requestId;
    $api = API_TSS_V2_TRANSACTIONS . "/" . $_REQUEST["requestId"];
    $result = ProcessRequest(MID, $api , METHOD_GET, "", CHILD_MID, AUTH_TYPE_SIGNATURE );
    echo "<PRE>" . json_encode( $result, JSON_PRETTY_PRINT ) . "</PRE>";
    if($result->responseCode == 200){
        // Got the txn
        $txn= $result->response;
        if(property_exists($txn->applicationInformation, "status")){
            $status = $txn->applicationInformation->status;
        }else{
            // Auths dont have a status (??)  Have a look through the application
            // applications for "ics_auth"
            $status = "UNKNOWN";
            foreach($txn->applicationInformation->applications as $application){
                if($application->name == "ics_auth"){
                    if($application->reasonCode == 100){
                        $status = "AUTHORIZED";
                        break;
                    }
                }
            }
        }
        $amount=$txn->orderInformation->amountDetails->totalAmount;
        $currency=$txn->orderInformation->amountDetails->currency;
        echo "<BR>status=". $status;
        switch($status){
            case "PENDING":
                echo "<BR>Voiding";
                $api = API_PAYMENTS . "/" . $_REQUEST["requestId"] . "/voids";
                $request = [
                    "clientReferenceInformation" => [
                        "code" => "test_void"
                    ]
                ];
                $result = ProcessRequest(MID, $api , METHOD_POST, json_encode($request), CHILD_MID, AUTH_TYPE_SIGNATURE );
                echo "<PRE>" . json_encode( $result, JSON_PRETTY_PRINT ) . "</PRE>";
                if($result->responseCode == 201){
                    if( !insertPayment("VOID", $orderId, $amount, 0, $currency,
                            "n/a","n/a","n/a", $result->response->id, "SUBMITTED")){
                        echo "<BR>DBError inserting Payment";
                    }
                    if( !updateOrder($orderId, "VOIDED")){
                        echo "<BR>DBError updating Order";
                    }
                    // SUCCESS - submit Auth Reversal
                    echo "<BR>Reversing";
                    $api = API_PAYMENTS . "/" . $_REQUEST["requestId"] . "/reversals";
                    $request = [
                        "clientReferenceInformation" => [
                            "code" => "test_reversal"
                        ],
                        "reversalInformation" => [
                            "amountDetails" => [
                                "totalAmount" => $amount,
                                "currency" => $currency
                            ],
                            "reason" => "testing"
                        ]
                    ];
                    $result = ProcessRequest(MID, $api , METHOD_POST, json_encode($request), CHILD_MID, AUTH_TYPE_SIGNATURE );
                    echo "<PRE>" . json_encode( $result, JSON_PRETTY_PRINT ) . "</PRE>";
                    if($result->responseCode == 201){
                        if( !insertPayment("AUTH REVERSAL", $orderId, $amount, 0, $currency,
                                "n/a","n/a","n/a", $result->response->id, "SUBMITTED")){
                            echo "<BR>DBError inserting Payment";
                        }
                        if( !updateOrder($orderId, "REVERSED")){
                            echo "<BR>DBError updating Order";
                        }
                        echo "<BR>COMPLETE";
                    }
                }
                // Captured AUTH - submit VOID and AUTH REVERSAL
                break;

            case "AUTHORIZED":
                // Authorised - submit AUTH REVERSAL
                echo "<BR>Reversing";
                $api = API_PAYMENTS . "/" . $_REQUEST["requestId"] . "/reversals";
                $request = [
                    "clientReferenceInformation" => [
                        "code" => "test_reversal"
                    ],
                    "reversalInformation" => [
                        "amountDetails" => [
                            "totalAmount" => $amount,
                            "currency" => $currency
                        ],
                        "reason" => "testing"
                    ]
                ];
                $result = ProcessRequest(MID, $api , METHOD_POST, json_encode($request), CHILD_MID, AUTH_TYPE_SIGNATURE );
                echo "<PRE>" . json_encode( $result, JSON_PRETTY_PRINT ) . "</PRE>";
                if($result->responseCode == 201){
                    if( !insertPayment("AUTH REVERSAL", $orderId, $amount, 0, $currency,
                            "n/a","n/a","n/a", $result->response->id, "SUBMITTED")){
                        echo "<BR>DBError inserting Payment";
                    }
                    if( !updateOrder($orderId, "REVERSED")){
                        echo "<BR>DBError updating Order";
                    }
                    echo "<BR>COMPLETE";
                }
                break;
            case "TRANSMITTED":
                // Settled payment - submit follow-on credit (refund)
                echo "<BR>Refunding";
                $api = API_PAYMENTS . "/" . $_REQUEST["requestId"] . "/refunds";
                $request = [
                    "clientReferenceInformation" => [
                        "code"  =>  "test_refund"
                    ],
                    "orderInformation" => [
                        "amountDetails" => [
                            "totalAmount"  =>  $amount,
                            "currency"  =>  $currency
                        ]
                    ]
                ];
                $result = ProcessRequest(MID, $api , METHOD_POST, json_encode($request), CHILD_MID, AUTH_TYPE_SIGNATURE );
                echo "<PRE>" . json_encode( $result, JSON_PRETTY_PRINT ) . "</PRE>";
                if($result->responseCode == 201){
                    if( !insertPayment("REFUND", $orderId, $amount, 0, $currency,
                            "n/a","n/a","n/a", $result->response->id, "SUBMITTED")){
                        echo "<BR>DBError inserting Payment";
                    }
                    if( !updateOrder($orderId, "REFUNDED")){
                        echo "<BR>DBError updating Order";
                    }
                    echo "<BR>Refunded";
                }
                break;
            case "VOIDED":
            // Already voided - ignore
            case "DECLINED":
            // Not authorised - ignore
            default:
                echo "<BR>IGNORE";
                break;
        }
    }
} catch (Exception $exception) {
    $result->responseCode = 500;
    $result->exception = $exception;
}
?>
