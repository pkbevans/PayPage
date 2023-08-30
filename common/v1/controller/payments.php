<?php
require_once('db.php');
require_once('../model/Payment.php');
require_once('../model/Response.php');

// attempt to set up connections to read and write db connections
try {
    $writeDB = DB::connectWriteDB();
    $readDB = DB::connectReadDB();
} catch (PDOException $ex) {
    // log connection error for troubleshooting and return a json error response
    error_log("Connection Error: " . $ex, 0);
    $response = new Response(500, false, "Database connection error", null);
    $response->send();
    exit;
}

// BEGIN OF AUTH SCRIPT
// Authenticate user with access token
// check to see if access token is provided in the HTTP Authorization header and that the value is longer than 0 chars
// don't forget the Apache fix in .htaccess file
if (!isset($_SERVER['HTTP_AUTHORIZATION']) || strlen($_SERVER['HTTP_AUTHORIZATION']) < 1) {
    $response = new Response(401, false, null, null);
    if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $response->addMessage("Access token is missing from the header");
    } elseif (strlen($_SERVER['HTTP_AUTHORIZATION']) < 1) {
        $response->addMessage("Access token cannot be blank");
    }
    $response->send();
    exit;
}

// get supplied access token from authorisation header - used for delete (log out) and patch (refresh)
$accessToken = $_SERVER['HTTP_AUTHORIZATION'];

// attempt to query the database to check token details - use write connection as it needs to be synchronous for token
try {
    // create db query to check access token is equal to the one provided
    $query = $writeDB->prepare('select userId, accessTokenExpiry, userActive, loginAttempts from pp_sessions, pp_usrs where pp_sessions.userId = pp_usrs.id and accessToken = :accessToken');
    $query->bindParam(':accessToken', $accessToken, PDO::PARAM_STR);
    $query->execute();

    // get row count
    $rowCount = $query->rowCount();

    if ($rowCount === 0) {
        // set up response for unsuccessful log out response
        $response = new Response(401, false, "Invalid access token", null);
        $response->send();
        exit;
    }

    // get returned row
    $row = $query->fetch(PDO::FETCH_ASSOC);

    // save returned details into variables
    $returned_userId = $row['userId'];
    $returned_accessTokenExpiry = $row['accessTokenExpiry'];
    $returned_userActive = $row['userActive'];
    $returned_loginAttempts = $row['loginAttempts'];

    // check if account is active
    if ($returned_userActive != 'Y') {
        $response = new Response(401, false, "User account is not active", null);
        $response->send();
        exit;
    }

    // check if account is locked out
    if ($returned_loginAttempts >= 3) {
        $response = new Response(401, false, "User account is currently locked out", null);
        $response->send();
        exit;
    }

    // check if access token has expired
    if (strtotime($returned_accessTokenExpiry) < time()) {
        $response = new Response(401, false, null, null);
        $response->addMessage("expiry:" . $returned_accessTokenExpiry);
        $response->addMessage("time now:" . date('y/m/D H:i', time()));
        $response->addMessage("expiry:" . strtotime($returned_accessTokenExpiry) . " now:" . time());
        $response->addMessage("Access token has expired");
        $response->send();
        exit;
    }
} catch (PDOException $ex) {
    $response = new Response(500, false, "There was an issue authenticating - please try again", null);
    $response->send();
    exit;
}
// END OF AUTH SCRIPT
// within this if/elseif statement, it is important to get the correct payment 
// (if query string GET param is used in multiple routes)
// check if paymentId is in the url e.g. /payments/1
if (array_key_exists("paymentId", $_GET)) {
    // get payment id from query string
    $paymentId = $_GET['paymentId'];

    //check to see if payment id in query string is not empty and is number, if not return json error
    if ($paymentId == '' || !is_numeric($paymentId)) {
        $response = new Response(400, false, "Payment ID cannot be blank or must be numeric", null);
        $response->send();
        exit;
    }

    // if request is a GET, e.g. get payment
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get specified payment
        $response = getPayments($readDB, $paymentId, null);
        $response->send();
    }     
    // if any other request method apart from GET is used then return 405 method not allowed
    else {
        $response = new Response(405, false, "Request method not allowed", null);
        $response->send();
        exit;
    }
}
// filter payments
elseif (array_key_exists("orderId", $_GET)) {
    // get query string
    $filter = $_GET;
    // check to see if filter in query string is either Y or N
    if (strlen($filter <1)) {
        $response = new Response(400, false, "Filter can't be empty", null);
        $response->send();
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $response = getPayments($readDB, null, $_GET);
        $response->send();
        exit;
    } else {
        // if any other request method apart from GET is used then return 405 method not allowed
        $response = new Response(405, false, "Request method not allowed", null);
        $response->send();
        exit;
    }
}
// handle getting all payments or creating a new one
elseif (empty($_GET)) {
    // if request is a GET e.g. get payments
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get all payments
        $response = getPayments($readDB, null, null);
        $response->send();
    }
    // else if request is a POST e.g. create payment
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // create payment
        // check request's content type header is JSON
        if ($_SERVER['CONTENT_TYPE'] !== 'application/json') {
            // set up response for unsuccessful request
            $response = new Response(400, false, "Content Type header not set to JSON", null);
            $response->send();
            exit;
        }
        // get POST request body as the POSTed data will be JSON format
        $rawPostData = file_get_contents('php://input');

        if (!$jsonData = json_decode($rawPostData)) {
            // set up response for unsuccessful request
            $response = new Response(400, false, "Request body is not valid JSON", null);
            $response->send();
            exit;
        }
        $response = createPayment($writeDB, $jsonData);
        $response->send();
    } else {
        // if any other request method apart from GET or POST is used then return 405 method not allowed
        $response = new Response(405, false, "Request method not allowed", null);
        $response->send();
        exit;
    }
}
// return 404 error if endpoint not available
else {
    $response = new Response(404, false, "Endpoint not found", null);
    $response->send();
    exit;
}

function getPayments($db, $id = null, $filter = null){
    // attempt to query the database
    try {
        // ADD AUTH TO QUERY
        // create db query
        $stmt = 'SELECT id, orderId, type, amount, currency, cardNumber, cardType, authCode, gatewayRequestId, status, captured, DATE_FORMAT(datetime, "%d/%m/%Y %H:%i") as datetime FROM payments ';
        if($id){
            // Get specified payment
            $stmt .= 'where id = :id';
            $query = $db->prepare($stmt);
            $query->bindParam(':id', $id, PDO::PARAM_INT);
        }elseif($filter){
            $clauses = array();
            $count = 0;
            if(isset($filter['orderId']) && strlen($filter['orderId'])>1) {
                $stmt .= 'where orderId= :orderId';
                $clauses['orderId'] = true;
                ++$count;
            }else{
                $clauses['orderId'] = false;
            }
            echo "STMT:" . $stmt . "[".$filter['orderId']."]";
            $query = $db->prepare($stmt);
            if($clauses['orderId']){
                $query->bindParam(':orderId', $filter['orderId'], PDO::PARAM_INT);
            }
        }
        else{
            // Get all payments
            $query = $db->prepare($stmt);
        }
        $query->execute();
    
        // get row count
        $rowCount = $query->rowCount();
        if ( $id && $rowCount === 0) {
            // set up response for unsuccessful return
            $response = new Response(404, false, "Payment not found", null);
            return $response;
        }

        // create payment array to store returned payments
        $paymentArray = array();
        // for each row returned
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            // create new payment object for each row
            $payment = new Payment($row['id'], $row['orderId'], $row['type'], $row['amount'], $row['currency'], $row['cardNumber'], $row['cardType'], $row['authCode'], $row['gatewayRequestId'], $row['status'], $row['captured'], $row['datetime']);
            // create payment and store in array for return in json data
            $paymentArray[] = $payment->returnPaymentAsArray();
        }
    
        // bundle payments and rows returned into an array to return in the json data
        $returnData = array();
        $returnData['rows_returned'] = $rowCount;
        $returnData['payments'] = $paymentArray;
    
        // set up response for successful return
        $response = new Response(200, true, null, $returnData);
        $response->toCache(true);
        return $response;
    }
    catch (PaymentException $ex) {
        // if error with sql query return a json error
        $response = new Response(500, false, $ex->getMessage(), null);
        return $response;
    }
    catch (PDOException $ex) {
        error_log("Database Query Error: " . $ex->getMessage(), 0);
        $response = new Response(500, false, "Failed to get ".($id?'payment':'payments'), null);
        return $response;
    }    
}
function createPayment($db, $jsonData){
    try {
        // data validation
        if (!isset($jsonData->orderId)) {
            $response = new Response(400, false, "orderId not set", null);
            return $response;
        }
        if (!isset($jsonData->type)) {
            $response = new Response(400, false, "type not set", null);
            return $response;
        }
        if (!isset($jsonData->amount)) {
            $response = new Response(400, false, "amount not set", null);
            return $response;
        }
        if (!isset($jsonData->currency)) {
            $response = new Response(400, false, "currency not set", null);
            return $response;
        }
        if (!isset($jsonData->cardNumber)) {
            $response = new Response(400, false, "cardNumber not set", null);
            return $response;
        }
        if (!isset($jsonData->cardType)) {
            $response = new Response(400, false, "cardType not set", null);
            return $response;
        }
        if (!isset($jsonData->authCode)) {
            $response = new Response(400, false, "authCode not set", null);
            return $response;
        }
        if (!isset($jsonData->gatewayRequestId)) {
            $response = new Response(400, false, "gatewayRequestId not set", null);
            return $response;
        }
        if (!isset($jsonData->status)) {
            $response = new Response(400, false, "status not set", null);
            return $response;
        }
        if (!isset($jsonData->captured)) {
            $response = new Response(400, false, "captured not set", null);
            return $response;
        }
        // create new payment with data, if non mandatory fields not provided then set to null
        $newPayment = new Payment(null, $jsonData->orderId, $jsonData->type, $jsonData->amount, $jsonData->currency, $jsonData->cardNumber, $jsonData->cardType, $jsonData->authCode, $jsonData->gatewayRequestId, $jsonData->status,$jsonData->captured, null);
        // get title, description, deadline, filter and store them in variables

        // ADD AUTH TO QUERY
        // create db query
        $query = $db->prepare(
            'insert into payments (orderId, type, amount, currency, cardNumber, cardType, authCode, gatewayRequestId, status, captured, datetime) ' .
                        'values (:orderId, :type, :amount, :currency, :cardNumber, :cardType, :authCode, :gatewayRequestId, :status, :captured, NOW())');
        $query->bindParam(':orderId', $jsonData->orderId, PDO::PARAM_INT);
        $query->bindParam(':type', $jsonData->type, PDO::PARAM_STR);
        $query->bindParam(':amount', $jsonData->amount, PDO::PARAM_STR);
        $query->bindParam(':currency', $jsonData->currency, PDO::PARAM_STR);
        $query->bindParam(':cardNumber', $jsonData->cardNumber, PDO::PARAM_STR);
        $query->bindParam(':cardType', $jsonData->cardType, PDO::PARAM_STR);
        $query->bindParam(':authCode', $jsonData->authCode, PDO::PARAM_STR);
        $query->bindParam(':gatewayRequestId', $jsonData->gatewayRequestId, PDO::PARAM_STR);
        $query->bindParam(':status', $jsonData->status, PDO::PARAM_STR);
        $query->bindParam(':captured', $jsonData->captured, PDO::PARAM_STR);
        $query->execute();

        // get row count
        $rowCount = $query->rowCount();

        // check if row was actually inserted, PDO exception should have caught it if not.
        if ($rowCount === 0) {
            // set up response for unsuccessful return
            $response = new Response(500, false, "Failed to create payment", null);
            return $response;
        }

        // get last payment id so we can return the Payment in the json
        $lastpaymentId = $db->lastInsertId();
        // ADD AUTH TO QUERY
        // create db query to get newly created payment - get from master db not read slave as replication may be too slow for successful read
        $query = $db->prepare('SELECT id, orderId, type, amount, currency, cardNumber, cardType, authCode, gatewayRequestId, status, captured, DATE_FORMAT(datetime, "%d/%m/%Y %H:%i") as datetime from payments where id = :paymentId');
        $query->bindParam(':paymentId', $lastpaymentId, PDO::PARAM_INT);
        // $query->bindParam(':userId', $returned_userId, PDO::PARAM_INT);
        $query->execute();

        // get row count
        $rowCount = $query->rowCount();
        // make sure that the new payment was returned
        if ($rowCount === 0) {
            // set up response for unsuccessful return
            $response = new Response(500, false, "Failed to retrieve payment after creation", null);
            return $response;
        }

        // create empty array to store payments
        $paymentArray = array();

        // for each row returned - should be just one
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            // create new payment object
            $payment = new Payment($row['id'], $row['orderId'], $row['type'], $row['amount'], $row['currency'], $row['cardNumber'], $row['cardType'], $row['authCode'], $row['gatewayRequestId'], $row['status'], $row['captured'], $row['datetime']);

            // create payment and store in array for return in json data
            $paymentArray[] = $payment->returnPaymentAsArray();
        }
        // bundle payments and rows returned into an array to return in the json data
        $returnData = array();
        $returnData['rows_returned'] = $rowCount;
        $returnData['payments'] = $paymentArray;

        //set up response for successful return
        $response = new Response(201, true, "Payment created", $returnData);
        return $response;
    }
    // if payment fails to create due to data types, missing fields or invalid data then send error json
    catch (PaymentException $ex) {
        $response = new Response(400, false, $ex->getMessage(), null);
        return $response;
    }
    // if error with sql query return a json error
    catch (PDOException $ex) {
        error_log("Database Query Error: " . $ex, 0);
        $response = new Response(500, false, "Failed to insert payment into database - check submitted data for errors", null);
        $response->addMessage($ex->getMessage());
        return $response;
    }
}