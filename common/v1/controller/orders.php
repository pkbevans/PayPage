<?php
require_once('db.php');
require_once('ordersFunctions.php');
require_once('../model/Order.php');
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
$response = validateAccessToken($readDB, $accessToken);
if ($response->success()) {
    $data = $response->getData();
    $returned_userId = $data['userId'];
    $returned_accessTokenExpiry = $data['accessTokenExpiry'];
    $returned_userActive = $data['userActive'];
    $returned_loginAttempts = $data['loginAttempts'];
    $returned_type = $data['type'];
    $returned_admin = $data['admin'];
}else{
    $response->send();
}

// END OF AUTH SCRIPT
// If user is a TYPE=CUSTOMER then they can only see their own txns.  If the user is an INTERNAL then they 
// can see all
if($returned_type === "CUSTOMER"){
    $filterByUserId = $returned_userId;
}else{
    $filterByUserId = null;
}
// within this if/elseif statement, it is important to get the correct order 
// (if query string GET param is used in multiple routes)
// check if orderId is in the url e.g. /orders/1
if (array_key_exists("orderId", $_GET)) {
    // get order id from query string
    $orderId = $_GET['orderId'];

    //check to see if order id in query string is not empty and is number, if not return json error
    if ($orderId == '' || !is_numeric($orderId)) {
        $response = new Response(400, false, "Order ID cannot be blank or must be numeric", null);
        $response->send();
        exit;
    }

    // if request is a GET, e.g. get order
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $response = getOrders($readDB, $orderId, null, 0, 0, $filterByUserId);
        $response->send();
    }     
    // else if request if a DELETE e.g. delete order
    elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $response = deleteOrder($writeDB, $orderId);
        $response->send();
    }
    // handle updating order
    elseif ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
        // update order
        updateOrder2($writeDB, $orderId);
    } elseif (array_key_exists("patch", $_GET) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        // Hack to get round PATCH not supported on one.com
        updateOrder2($writeDB, $orderId);
    }
    // if any other request method apart from GET, PATCH, DELETE is used then return 405 method not allowed
    else {
        $response = new Response(405, false, "Request method not allowed", null);
        $response->send();
        exit;
    }
}
// filter orders
elseif (array_key_exists("id", $_GET) || array_key_exists("mrn", $_GET) ||
            array_key_exists("email", $_GET) || array_key_exists("customerId", $_GET) || 
                        array_key_exists("status", $_GET)) {
    // get query string
    $filter = $_GET;
    // check to see if filter in query string is either Y or N
    if (strlen($filter <1)) {
        $response = new Response(400, false, "Filter can't be empty", null);
        $response->send();
        exit;
    }

    $page = 0;
    $rowsPerPage = 0;
    if (array_key_exists("page", $_GET)) {
        // get page id from query string
        $page = $_GET['page'];

        //check to see if page id in query string is not empty and is number, if not return json error
        if ($page == '' || !is_numeric($page)) {
            $response = new Response(400, false, "Page number cannot be blank and must be numeric", null);
            $response->send();
            exit;
        }
        // See if the rows-per-page is set, else default is 20
        $rowsPerPage = 20;
        if (array_key_exists("rows", $_GET)) {
            $rowsPerPage = $_GET['rows'];

            //check to see if page id in query string is not empty and is number, if not return json error
            if ($rowsPerPage == '' || !is_numeric($rowsPerPage)) {
                $response = new Response(400, false, "Rows cannot be blank and must be numeric", null);
                $response->send();
                exit;
            }
        }
    }
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $response = getOrders($readDB, null, $_GET, $page, $rowsPerPage, $filterByUserId);
        $response->send();
        exit;
    }
    // if any other request method apart from GET is used then return 405 method not allowed
    else {
        $response = new Response(405, false, "Request method not allowed", null);
        $response->send();
        exit;
    }
}
// handle getting all orders or creating a new one
elseif (empty($_GET)) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // if request is a GET e.g. get ALL orders
        $response = getOrders($readDB, null, null, 0, 0, $filterByUserId);
        $response->send();
    }
    // else if request is a POST e.g. create order
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        
        $response = createOrder($writeDB, $jsonData);
        $response->send();        
    }else{
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