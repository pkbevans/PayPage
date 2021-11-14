<?php
include_once "RestRequest.php";
//////////////  TEST START \\\\\\\\\\\\\\\\
$xyz = "4111" ."1111" . "1111" . "1111";
$payload = [
    "clientReferenceInformation" => [
        "code" => "TC50171_3"
    ],
    "processingInformation" => [
        "commerceIndicator" => "internet"
    ],
    "orderInformation" => [
        "billTo" => [
            "firstName" => "john",
            "lastName" => "doe",
            "address1" => "201 S. Division St.",
            "postalCode" => "48104-2201",
            "locality" => "Ann Arbor",
            "administrativeArea" => "MI",
            "country" => "US",
            "phoneNumber" => "999999999",
            "email" => "test@cybs.com"
        ],
        "amountDetails" => [
            "totalAmount" => "10",
            "currency" => "GBP"
        ]
    ],
    "paymentInformation" => [
        "card" => [
            "expirationYear" => "2031",
            "number" => $xyz,
            "securityCode" => "123",
            "expirationMonth" => "12",
            "type" => "001"
        ]
    ]
];

$requestBody = json_encode($payload);
// HTTP POST REQUEST
echo "Sample 1: POST call - CyberSource Payments API - HTTP POST Payment request";
$result = ProcessRequest("barclayssitt00", API_PAYMENTS, METHOD_POST, $requestBody, MID, AUTH_TYPE_BEARER);
echo "<PRE>" . json_encode($result, JSON_PRETTY_PRINT) . "</PRE>";

// HTTP GET REQUEST
$customerId = "C5D2B25A1619A464E053A2598D0AE767";
$resource = API_TMS_V2_CUSTOMERS ."/" . $customerId;
//echo "<BR>Sample 2: GET call";
//$result = ProcessRequest(MID, $resource, METHOD_GET, "{}");
//echo "<PRE>" . json_encode($result, JSON_PRETTY_PRINT) . "</PRE>";
