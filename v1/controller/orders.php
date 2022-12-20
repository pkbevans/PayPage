<?php
require_once('db.php');
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
try {
    // create db query to check access token is equal to the one provided
    $query = $writeDB->prepare('select userId, accessTokenExpiry, userActive, loginAttempts from sessions, users where sessions.userId = users.id and accessToken = :accessToken');
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
// within this if/elseif statement, it is important to get the correct order (if query string GET param is used in multiple routes)
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
        $response = getOrders($readDB, $orderId);
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
        try {
        // check request's content type header is JSON
        if ($_SERVER['CONTENT_TYPE'] !== 'application/json') {
            // set up response for unsuccessful request
            $response = new Response(400, false, "Content Type header not set to JSON", null);
            $response->send();
            exit;
        }

        // get PATCH request body as the PATCHed data will be JSON format
        $rawPatchData = file_get_contents('php://input');

        if (!$jsonData = json_decode($rawPatchData)) {
            // set up response for unsuccessful request
            $response = new Response(400, false, "Request body is not valid JSON", null);
            $response->send();
            exit;
        }

        // set order field updated to false initially
        $id_updated = false;
        $merchantReference_updated = false;
        $amount_updated = false;
        $refundAmount_updated = false;
        $currency_updated = false;
        $customerId_updated = false;
        $customerEmail_updated = false;
        $status_updated = false;
        $datetime_updated = false;
        // create blank query fields string to append each field to
        $queryFields = "";
        //  id, merchantReference, amount, refundAmount, currency, customerId, customerEmail, status, datetime      
        // check if title exists in PATCH
        if (isset($jsonData->merchantReference)) {
            // set title field updated to true
            $merchantReference_updated = true;
            // add title field to query field string
            $queryFields .= "merchantReference = :merchantReference, ";
        }
        // check if amount exists in PATCH
        if (isset($jsonData->amount)) {
            // set amount field updated to true
            $amount_updated = true;
            // add amount field to query field string
            $queryFields .= "amount = :amount, ";
        }
        // check if refundAmount exists in PATCH
        if (isset($jsonData->refundAmount)) {
            // set refundAmount field updated to true
            $refundAmount_updated = true;
            // add refundAmount field to query field string
            $queryFields .= "refundAmount = : refundAmount, ";
        }
        // check if currency exists in PATCH
        if (isset($jsonData->currency)) {
            // set currency field updated to true
            $currency_updated = true;
            // add currency field to query field string
            $queryFields .= "currency = :currency, ";
        }
        // check if customerId exists in PATCH
        if (isset($jsonData->customerId)) {
            // set customerId field updated to true
            $customerId_updated = true;
            // add customerId field to query field string
            $queryFields .= "customerId = :customerId, ";
        }
        // check if customerEmail exists in PATCH
        if (isset($jsonData->customerEmail)) {
            // set customerEmail field updated to true
            $customerEmail_updated = true;
            // add customerEmail field to query field string
            $queryFields .= "customerEmail = :customerEmail, ";
        }
        // check if status exists in PATCH
        if (isset($jsonData->status)) {
            // set status field updated to true
            $status_updated = true;
            // add status field to query field string
            $queryFields .= "status = :status, ";
        }
        // check if datetime exists in PATCH
        if (isset($jsonData->datetime)) {
            // set datetime field updated to true
            $datetime_updated = true;
            // add currency field to query field string
            $queryFields .= "datetime = STR_TO_DATE(:datetime, '%d/%m/%Y %H:%i'), ";
        }

        // remove the right hand comma and trailing space
        $queryFields = rtrim($queryFields, ", ");

        // check if any order fields supplied in JSON
        if ($title_updated === false && $description_updated === false && $deadline_updated === false && $filter_updated === false) {
            $response = new Response(400, false, "No order fields provided", null);
            $response->send();
            exit;
        }
        // ADD AUTH TO QUERY
        // create db query to get order from database to update - use master db
        $query = $writeDB->prepare('SELECT id, merchantReference, amount, refundAmount, currency, customerId, customerEmail, status, DATE_FORMAT(datetime, "%d/%m/%Y %H:%i") as datetime from orders where id = :orderId');
        $query->bindParam(':orderId', $orderId, PDO::PARAM_INT);
        // $query->bindParam(':userId', $returned_userId, PDO::PARAM_INT);
        $query->execute();

        // get row count
        $rowCount = $query->rowCount();
        // make sure that the order exists for a given order id
        if ($rowCount === 0) {
            // set up response for unsuccessful return
            $response = new Response(404, false, "No order found to update", null);
            $response->send();
            exit;
        }

        // for each row returned - should be just one
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            // create new order object
            $order = new Order($row['id'], $row['merchantReference'], $row['amount'], $row['refundAmount'], $row['currency'], $row['customerId'], $row['customerEmail'], $row['status'], $row['datetime']);
        }
        // ADD AUTH TO QUERY
        // create the query string including any query fields
        $queryString = "update orders set " . $queryFields . " where id = :orderId";
        // prepare the query
        $query = $writeDB->prepare($queryString);

        // if title has been provided
        if ($title_updated === true) {
            // set order object title to given value (checks for valid input)
            $order->setTitle($jsonData->title);
            // get the value back as the object could be handling the return of the value differently to
            // what was provided
            $up_title = $order->getTitle();
            // bind the parameter of the new value from the object to the query (prevents SQL injection)
            $query->bindParam(':title', $up_title, PDO::PARAM_STR);
        }

        // if description has been provided
        if ($description_updated === true) {
            // set order object description to given value (checks for valid input)
            $order->setDescription($jsonData->description);
            // get the value back as the object could be handling the return of the value differently to
            // what was provided
            $up_description = $order->getDescription();
            // bind the parameter of the new value from the object to the query (prevents SQL injection)
            $query->bindParam(':description', $up_description, PDO::PARAM_STR);
        }

        // if deadline has been provided
        if ($deadline_updated === true) {
            // set order object deadline to given value (checks for valid input)
            $order->setDeadline($jsonData->deadline);
            // get the value back as the object could be handling the return of the value differently to
            // what was provided
            $up_deadline = $order->getDeadline();
            // bind the parameter of the new value from the object to the query (prevents SQL injection)
            $query->bindParam(':deadline', $up_deadline, PDO::PARAM_STR);
        }

        // if filter has been provided
        if ($filter_updated === true) {
            // set order object filter to given value (checks for valid input)
            $order->setfilter($jsonData->filter);
            // get the value back as the object could be handling the return of the value differently to
            // what was provided
            $up_filter = $order->getfilter();
            // bind the parameter of the new value from the object to the query (prevents SQL injection)
            $query->bindParam(':filter', $up_filter, PDO::PARAM_STR);
        }

        // bind the order id provided in the query string
        $query->bindParam(':orderId', $orderId, PDO::PARAM_INT);
        // bind the user id returned
        $query->bindParam(':userId', $returned_userId, PDO::PARAM_INT);
        // run the query
        $query->execute();

        // get affected row count
        $rowCount = $query->rowCount();

        // check if row was actually updated, could be that the given values are the same as the stored values
        if ($rowCount === 0) {
            // set up response for unsuccessful return
            $response = new Response(400, false, "Order not updated - given values may be the same as the stored values", null);
            $response->send();
            exit;
        }
        // ADD AUTH TO QUERY
        // create db query to return the newly edited order - connect to master database
        $query = $writeDB->prepare('SELECT id, title, description, DATE_FORMAT(deadline, "%d/%m/%Y %H:%i") as deadline, filter from orders where id = :orderId and userId = :userId');
        $query->bindParam(':orderId', $orderId, PDO::PARAM_INT);
        $query->bindParam(':userId', $returned_userId, PDO::PARAM_INT);
        $query->execute();

        // get row count
        $rowCount = $query->rowCount();

        // check if order was found
        if ($rowCount === 0) {
            // set up response for unsuccessful return
            $response = new Response(404, false, "No order found", null);
            $response->send();
            exit;
        }
        // create order array to store returned orders
        $orderArray = array();

        // for each row returned
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            // create new order object for each row returned
            $order = new Order($row['id'], $row['merchantReference'], $row['amount'], $row['refundAmount'], $row['currency'], $row['customerId'], $row['customerEmail'], $row['status'], $row['datetime']);

            // create order and store in array for return in json data
            $orderArray[] = $order->returnOrderAsArray();
        }
        // bundle orders and rows returned into an array to return in the json data
        $returnData = array();
        $returnData['rows_returned'] = $rowCount;
        $returnData['orders'] = $orderArray;

        // set up response for successful return
        $response = new Response(200, true, "Order updated", $returnData);
        $response->send();
        exit;
        } catch (OrderException $ex) {
        $response = new Response(400, false, $ex->getMessage(), null);
        $response->send();
        exit;
        }
        // if error with sql query return a json error
        catch (PDOException $ex) {
        error_log("Database Query Error: " . $ex, 0);
        $response = new Response(500, false, "Failed to update order - check your data for errors", null);
        $response->send();
        exit;
        }
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

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $response = getOrders($readDB, null, $_GET);
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
// handle getting all orders page of 20 at a time
elseif (array_key_exists("page", $_GET)) {
    // if request is a GET e.g. get orders
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // get page id from query string
        $page = $_GET['page'];

        //check to see if page id in query string is not empty and is number, if not return json error
        if ($page == '' || !is_numeric($page)) {
        $response = new Response(400, false, "Page number cannot be blank and must be numeric", null);
        $response->send();
        exit;
        }

        // set limit to 20 per page
        $limitPerPage = 20;

        // attempt to query the database
        try {
        // ADD AUTH TO QUERY

        // get total number of orders for user
        // create db query
        $query = $readDB->prepare('SELECT count(id) as totalNoOfOrders from orders where userId = :userId');
        $query->bindParam(':userId', $returned_userId, PDO::PARAM_INT);
        $query->execute();

        // get row for count total
        $row = $query->fetch(PDO::FETCH_ASSOC);

        $ordersCount = intval($row['totalNoOfOrders']);

        // get number of pages required for total results use ceil to round up
        $numOfPages = ceil($ordersCount / $limitPerPage);

        // if no rows returned then always allow page 1 to show a successful response with 0 orders
        if ($numOfPages == 0) {
            $numOfPages = 1;
        }

        // if passed in page number is greater than total number of pages available or page is 0 then 404 error - page not found
        if ($page > $numOfPages || $page == 0) {
            $response = new Response(404, false, "Page not found", null);
            $response->send();
            exit;
        }

        // set offset based on current page, e.g. page 1 = offset 0, page 2 = offset 20
        $offset = ($page == 1 ?  0 : (20 * ($page - 1)));

        // ADD AUTH TO QUERY
        // get rows for page
        // create db query
        $query = $readDB->prepare('SELECT id, title, description, DATE_FORMAT(deadline, "%d/%m/%Y %H:%i") as deadline, filter from orders where userId = :userId limit :pglimit OFFSET :offset');
        $query->bindParam(':userId', $returned_userId, PDO::PARAM_INT);
        $query->bindParam(':pglimit', $limitPerPage, PDO::PARAM_INT);
        $query->bindParam(':offset', $offset, PDO::PARAM_INT);
        $query->execute();

        // get row count
        $rowCount = $query->rowCount();

        // create order array to store returned orders
        $orderArray = array();

        // for each row returned
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            // create new order object for each row
            $order = new Order($row['id'], $row['merchantReference'], $row['amount'], $row['refundAmount'], $row['currency'], $row['customerId'], $row['customerEmail'], $row['status'], $row['datetime']);

            // create order and store in array for return in json data
            $orderArray[] = $order->returnOrderAsArray();
        }

        // bundle orders and rows returned into an array to return in the json data
        $returnData = array();
        $returnData['rows_returned'] = $rowCount;
        $returnData['total_rows'] = $ordersCount;
        $returnData['total_pages'] = $numOfPages;
        // if passed in page less than total pages then return true
        ($page < $numOfPages ? $returnData['has_next_page'] = true : $returnData['has_next_page'] = false);
        // if passed in page greater than 1 then return true
        ($page > 1 ? $returnData['has_previous_page'] = true : $returnData['has_previous_page'] = false);
        $returnData['orders'] = $orderArray;

        // set up response for successful return
        $response = new Response(200, true, null, $returnData);
        $response->toCache(true);
        $response->send();
        exit;
        }
        // if error with sql query return a json error
        catch (OrderException $ex) {
        $response = new Response(500, false, $ex->getMessage(), null);
        $response->send();
        exit;
        } catch (PDOException $ex) {
        error_log("Database Query Error: " . $ex, 0);
        $response = new Response(500, false, "Failed to get orders", null);
        $response->send();
        exit;
        }
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

// if request is a GET e.g. get orders
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $response = getOrders($readDB);
    $response->send();
}
// else if request is a POST e.g. create order
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // create order
    try {
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

    // TODO - data validation
    if (!isset($jsonData->merchantReference)) {
        $response = new Response(400, false, "merchantReference not set", null);
        $response->send();
        exit;
    }
    if (!isset($jsonData->amount)) {
        $response = new Response(400, false, "amount not set", null);
        $response->send();
        exit;
    }
    if (!isset($jsonData->refundAmount)) {
        $response = new Response(400, false, "refundAmount not set", null);
        $response->send();
        exit;
    }
    if (!isset($jsonData->currency)) {
        $response = new Response(400, false, "currency not set", null);
        $response->send();
        exit;
    }
    if (!isset($jsonData->customerId)) {
        $response = new Response(400, false, "customerId not set", null);
        $response->send();
        exit;
    }
    if (!isset($jsonData->customerEmail)) {
        $response = new Response(400, false, "customerEmail not set", null);
        $response->send();
        exit;
    }
    if (!isset($jsonData->status)) {
        $response = new Response(400, false, "status not set", null);
        $response->send();
        exit;
    }
    //  id, merchantReference, amount, refundAmount, currency, customerId, customerEmail, status, datetime      

    // create new order with data, if non mandatory fields not provided then set to null
    $newOrder = new Order(null, $jsonData->merchantReference, $jsonData->amount, $jsonData->refundAmount, $jsonData->currency, $jsonData->customerId, $jsonData->customerEmail, $jsonData->status, null);
    // get title, description, deadline, filter and store them in variables

    // ADD AUTH TO QUERY
    // create db query
    $query = $writeDB->prepare('insert into orders (merchantReference, amount, refundAmount, currency, customerId, customerEmail, status, datetime) ' .
        'values (:merchantReference, :amount, :refundAmount, :currency, :customerId, :customerEmail, :status, NOW())');
    $query->bindParam(':merchantReference', $jsonData->merchantReference, PDO::PARAM_STR);
    $query->bindParam(':amount', $jsonData->amount, PDO::PARAM_STR);
    $query->bindParam(':refundAmount', $jsonData->refundAmount, PDO::PARAM_STR);
    $query->bindParam(':currency', $jsonData->currency, PDO::PARAM_STR);
    $query->bindParam(':customerId', $jsonData->customerId, PDO::PARAM_STR);
    $query->bindParam(':customerEmail', $jsonData->customerEmail, PDO::PARAM_STR);
    $query->bindParam(':status', $jsonData->status, PDO::PARAM_STR);
    $query->execute();

    // get row count
    $rowCount = $query->rowCount();

    // check if row was actually inserted, PDO exception should have caught it if not.
    if ($rowCount === 0) {
        // set up response for unsuccessful return
        $response = new Response(500, false, "Failed to create order", null);
        $response->send();
        exit;
    }

    // get last order id so we can return the Order in the json
    $lastorderId = $writeDB->lastInsertId();
    // ADD AUTH TO QUERY
    // create db query to get newly created order - get from master db not read slave as replication may be too slow for successful read
    $query = $writeDB->prepare('SELECT id, merchantReference, amount, refundAmount, currency, customerId, customerEmail, status, DATE_FORMAT(datetime, "%d/%m/%Y %H:%i") as datetime from orders where id = :orderId');
    $query->bindParam(':orderId', $lastorderId, PDO::PARAM_INT);
    // $query->bindParam(':userId', $returned_userId, PDO::PARAM_INT);
    $query->execute();

    // get row count
    $rowCount = $query->rowCount();
    // make sure that the new order was returned
    if ($rowCount === 0) {
        // set up response for unsuccessful return
        $response = new Response(500, false, "Failed to retrieve order after creation", null);
        $response->send();
        exit;
    }

    // create empty array to store orders
    $orderArray = array();

    // for each row returned - should be just one
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        // create new order object
        $order = new Order($row['id'], $row['merchantReference'], $row['amount'], $row['refundAmount'], $row['currency'], $row['customerId'], $row['customerEmail'], $row['status'], $row['datetime']);

        // create order and store in array for return in json data
        $orderArray[] = $order->returnOrderAsArray();
    }
    // bundle orders and rows returned into an array to return in the json data
    $returnData = array();
    $returnData['rows_returned'] = $rowCount;
    $returnData['orders'] = $orderArray;

    //set up response for successful return
    $response = new Response(201, true, "Order created", $returnData);
    $response->send();
    exit;
    }
    // if order fails to create due to data types, missing fields or invalid data then send error json
    catch (OrderException $ex) {
    $response = new Response(400, false, $ex->getMessage(), null);
    $response->send();
    exit;
    }
    // if error with sql query return a json error
    catch (PDOException $ex) {
    error_log("Database Query Error: " . $ex, 0);
    $response = new Response(500, false, "Failed to insert order into database - check submitted data for errors", null);
    $response->send();
    exit;
    }
}
// if any other request method apart from GET or POST is used then return 405 method not allowed
else {
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

function getOrders($db, $id = null, $filter = null){
    // attempt to query the database
    try {
        // ADD AUTH TO QUERY
        // create db query
        $stmt = 'SELECT id, merchantReference, amount, refundAmount, currency, customerId, customerEmail, status, DATE_FORMAT(datetime, "%d/%m/%Y %H:%i") as datetime FROM orders ';
        if($id){
            // Get specified order
            $stmt .= 'where id = :id';
            $query = $db->prepare($stmt);
            $query->bindParam(':id', $id, PDO::PARAM_INT);
        }elseif($filter){
            $clauses = array();
            $count = 0;
            if(isset($filter['email']) && strlen($filter['email'])>1) {
                $stmt .= 'where customerEmail= :email';
                $clauses['email'] = true;
                ++$count;
            }else{
                $clauses['email'] = false;
            }
            if(isset($filter['mrn']) && strlen($filter['mrn'])>1){
                $stmt .= ($count?' and ':'where ').'merchantReference=:mrn';
                $clauses['mrn'] = true;
                ++$count;
            }else{
                $clauses['mrn'] = false;
            }
            if(isset($filter['customerId']) && strlen($filter['customerId'])>1){
                $stmt .= ($count?' and ':'where ').'customerId=:customerId';
                $clauses['customerId'] = true;
                ++$count;
            }else{
                $clauses['customerId'] = false;
            }
            if(isset($filter['id']) && strlen($filter['id'])>1){
                $stmt .= ($count?' and ':'where ').'id=:id';
                $clauses['id'] = true;
                ++$count;
            }else{
                $clauses['id'] = false;
            }
            if(isset($filter['status']) && strlen($filter['status'])>1){
                $stmt .= ($count?' and ':'where ').'status=:status';
                $clauses['status'] = true;
            }else{
                $clauses['status'] = false;
            }
            $query = $db->prepare($stmt);
            if($clauses['email']){
                $query->bindParam(':email', $filter['email'], PDO::PARAM_STR);
            }
            if($clauses['mrn']){
                $query->bindParam(':mrn', $filter['mrn'], PDO::PARAM_STR);
            }
            if($clauses['customerId']){
                $query->bindParam(':customerId', $filter['customerId'], PDO::PARAM_INT);
            }
            if($clauses['id']){
                $query->bindParam(':id', $filter['id'], PDO::PARAM_INT);
            }
            if($clauses['status']){
                $query->bindParam(':status', $filter['status'], PDO::PARAM_STR);
            }
        }
        else{
            // Get all orders
            $query = $db->prepare($stmt);
        }
        $query->execute();
    
        // get row count
        $rowCount = $query->rowCount();
        if ( $id && $rowCount === 0) {
            // set up response for unsuccessful return
            $response = new Response(404, false, "Order not found", null);
            return $response;
        }

        // create order array to store returned orders
        $orderArray = array();
        // for each row returned
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            // create new order object for each row
            $order = new Order($row['id'], $row['merchantReference'], $row['amount'], $row['refundAmount'], $row['currency'], $row['customerId'], $row['customerEmail'], $row['status'], $row['datetime']);
            // create order and store in array for return in json data
            $orderArray[] = $order->returnOrderAsArray();
            if($id){
                // find all payments for this order
                $query2 = $db->prepare('select * from payments where orderId = :orderId');
                $query2->bindParam(':orderId', $id, PDO::PARAM_INT);
                $query2->execute();
                $payments = $query2->fetchAll(PDO::FETCH_ASSOC);
                $orderArray[0]['payments_returned'] = count($payments);
                $orderArray[0]['payments'] = $payments;
            }
        }
    
        // bundle orders and rows returned into an array to return in the json data
        $returnData = array();
        $returnData['rows_returned'] = $rowCount;
        $returnData['orders'] = $orderArray;
    
        // set up response for successful return
        $response = new Response(200, true, null, $returnData);
        $response->toCache(true);
        return $response;
    }
    catch (OrderException $ex) {
        // if error with sql query return a json error
        $response = new Response(500, false, $ex->getMessage(), null);
        return $response;
    }
    catch (PDOException $ex) {
        error_log("Database Query Error: " . $ex->getMessage(), 0);
        $response = new Response(500, false, "Failed to get ".($id?'order':'orders'), null);
        return $response;
    }    
}
function deleteOrder($db, $id){
    // attempt to query the database
    try {
        // ADD AUTH TO QUERY
        // create db query
        $query = $db->prepare('delete from orders where id = :orderId');
        $query->bindParam(':orderId', $id, PDO::PARAM_INT);
        // $query->bindParam(':userId', $returned_userId, PDO::PARAM_INT);
        $query->execute();

        // get row count
        $rowCount = $query->rowCount();

        if ($rowCount === 0) {
            // set up response for unsuccessful return
            $response = new Response(404, false, "Order not found", null);
            return $response;
        }
        // set up response for successful return
        $response = new Response(200, true, "Order deleted", null);
        return $response;
    }
    // if error with sql query return a json error
    catch (PDOException $ex) {
        $response = new Response(500, false, "Failed to delete order", null);
        return $response;
    }
}
