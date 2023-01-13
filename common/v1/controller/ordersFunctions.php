<?php
function getOrders($db, $id = null, $filter = null, $page = 0, $rowsPerPage, $filterByUserId){
    // attempt to query the database
    try {
        // create db query
        $stmt = 'SELECT id, merchantReference, amount, refundAmount, currency, customerId, customerUserId, customerEmail, status, DATE_FORMAT(datetime, "%d/%m/%Y %H:%i") as datetime FROM orders ';
        if($id){
            // Get specified order
            $stmt .= 'where id = :id';
            $query = $db->prepare($stmt);
            $query->bindParam(':id', $id, PDO::PARAM_INT);
        }elseif($filter){
            $where = "";
            $clauses = array();
            $count = 0;
            if(isset($filter['email']) && strlen($filter['email'])>1) {
                $where .= 'where customerEmail= :email';
                $clauses['email'] = true;
                ++$count;
            }else{
                $clauses['email'] = false;
            }
            if(isset($filter['mrn']) && strlen($filter['mrn'])>1){
                $where .= ($count?' and ':'where ').'merchantReference=:mrn';
                $clauses['mrn'] = true;
                ++$count;
            }else{
                $clauses['mrn'] = false;
            }
            if(isset($filter['customerId']) && strlen($filter['customerId'])>1){
                $where .= ($count?' and ':'where ').'customerId=:customerId';
                $clauses['customerId'] = true;
                ++$count;
            }else{
                $clauses['customerId'] = false;
            }
            if(isset($filter['id']) && strlen($filter['id'])>1){
                $where .= ($count?' and ':'where ').'id=:id';
                $clauses['id'] = true;
                ++$count;
            }else{
                $clauses['id'] = false;
            }
            if(isset($filter['status']) && strlen($filter['status'])>1){
                $where .= ($count?' and ':'where ').'status=:status';
                $clauses['status'] = true;
            }else{
                $clauses['status'] = false;
            }
            // IF the user is a customer then, they can only see their own orders
            if(!empty($filterByUserId) && $filterByUserId>0){
                $where .= ($count?' and ':'where ').'customerUserId=:customerUserId';
                $clauses['customerUserId'] = true;
            }else{
                $clauses['customerUserId'] = false;
            }
            // If page>0 get total number of orders
            $offsetLimit = '';
            $offset = 0;
            if($page>0){
                // get total number of orders 
                // create db query
                $query = $db->prepare('SELECT count(id) as totalNoOfOrders from orders ' . $where);
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
                if($clauses['customerUserId']){
                    $query->bindParam(':customerUserId', $filterByUserId, PDO::PARAM_STR);
                }
                $query->execute();
    
                // get row for count total
                $row = $query->fetch(PDO::FETCH_ASSOC);
    
                $ordersCount = intval($row['totalNoOfOrders']);
    
                // get number of pages required for total results use ceil to round up
                $numOfPages = ceil($ordersCount / $rowsPerPage);
    
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
                $offset = ($page == 1 ?  0 : ($rowsPerPage * ($page - 1)));
                $offsetLimit = ' limit :pglimit OFFSET :offset';
            }
            $stmt .= $where;
            $stmt .= " order by id desc";
            $stmt .= $offsetLimit;
            // newest first
            // echo "STMT: " . $stmt;
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
            if($clauses['customerUserId']){
                $query->bindParam(':customerUserId', $filterByUserId, PDO::PARAM_STR);
            }
            if($page>0){
                $query->bindParam(':pglimit', $rowsPerPage, PDO::PARAM_STR);
                $query->bindParam(':offset', $offset, PDO::PARAM_STR);
            }
        }
        else{
            // Get all orders - newest first
            $stmt .= " order by id desc";
            // echo "STMT: " . $stmt;
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
            $order = new Order($row['id'], $row['merchantReference'], $row['amount'], $row['refundAmount'], $row['currency'], $row['customerId'], $row['customerUserId'], $row['customerEmail'], $row['status'], $row['datetime']);
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
        if($page>0){
            $returnData['total_rows'] = $ordersCount;
            $returnData['total_pages'] = $numOfPages;
            // if passed in page less than total pages then return true
            ($page < $numOfPages ? $returnData['has_next_page'] = true : $returnData['has_next_page'] = false);
            // if passed in page greater than 1 then return true
            ($page > 1 ? $returnData['has_prev_page'] = true : $returnData['has_prev_page'] = false);
        }
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
        $response->addMessage($stmt);
        $response->addMessage($where);
        $response->addMessage($offsetLimit);
        $response->addMessage($ex->getMessage());
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
function updateOrder2($db, $id){
    // update order
    try {
        // check request's content type header is JSON
        if (!isset($_SERVER['CONTENT_TYPE']) || $_SERVER['CONTENT_TYPE'] !== 'application/json') {
            // set up response for unsuccessful request
            $response = new Response(400, false, "Content Type header not set to JSON", null);
            $response->send();
            return;
        }

        // get PATCH request body as the PATCHed data will be JSON format
        $rawPatchData = file_get_contents('php://input');

        if (!$jsonData = json_decode($rawPatchData)) {
            // set up response for unsuccessful request
            $response = new Response(400, false, "Request body is not valid JSON", null);
            $response->send();
            return;
        }

        // set order field updated to false initially
        // CAN'T UPDATE ID, MRN, AMOUNT, CURRENCY, DATE CREATED
        $refundAmount_updated = false;
        $customerId_updated = false;
        $customerUserId_updated = false;
        $customerEmail_updated = false;
        $status_updated = false;
        // create blank query fields string to append each field to
        $queryFields = "";
        //  id, merchantReference, amount, refundAmount, currency, customerId, customerUserId, customerEmail, status, datetime      
        // check if refundAmount exists in PATCH
        if (isset($jsonData->refundAmount)) {
            // set refundAmount field updated to true
            $refundAmount_updated = true;
            // add refundAmount field to query field string
            $queryFields .= "refundAmount = :refundAmount, ";
        }
        // check if customerId exists in PATCH
        if (isset($jsonData->customerId)) {
            // set customerId field updated to true
            $customerId_updated = true;
            // add customerId field to query field string
            $queryFields .= "customerId = :customerId, ";
        }
        // check if customerUserId exists in PATCH
        if (isset($jsonData->customerUserId)) {
            // set customerUserId field updated to true
            $customerUserId_updated = true;
            // add customerUserId field to query field string
            $queryFields .= "customerUserId = :customerUserId, ";
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

        // remove the right hand comma and trailing space
        $queryFields = rtrim($queryFields, ", ");

        // check if any order fields supplied in JSON
        if ($refundAmount_updated === false && $customerId_updated === false && $customerUserId_updated === false && $customerEmail_updated === false && $status_updated === false) {
            $response = new Response(400, false, "No order fields provided", null);
            $response->send();
            return;
        }
        // ADD AUTH TO QUERY
        // create db query to get order from database to update - use master db
        $query = $db->prepare('SELECT id, merchantReference, amount, refundAmount, currency, customerId, customerUserId, customerEmail, status, DATE_FORMAT(datetime, "%d/%m/%Y %H:%i") as datetime from orders where id = :orderId');
        $query->bindParam(':orderId', $id, PDO::PARAM_INT);
        $query->execute();

        // get row count
        $rowCount = $query->rowCount();
        // make sure that the order exists for a given order id
        if ($rowCount === 0) {
            // set up response for unsuccessful return
            $response = new Response(404, false, "No order to update", null);
            $response->send();
            return;
        }

        // for each row returned - should be just one
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            // create new order object
            $order = new Order($row['id'], $row['merchantReference'], $row['amount'], $row['refundAmount'], $row['currency'], $row['customerId'], $row['customerUserId'], $row['customerEmail'], $row['status'], $row['datetime']);
        }
        // ADD AUTH TO QUERY
        // create the query string including any query fields
        $queryString = "update orders set " . $queryFields . " where id = :orderId";
        // prepare the query
        $query = $db->prepare($queryString);

        // if title has been provided
        if ($refundAmount_updated === true) {
            // set order object title to given value (checks for valid input)
            $order->setRefundAmount($jsonData->refundAmount);
            // get the value back as the object could be handling the return of the value differently to
            // what was provided
            $up_refundAmount = $order->getRefundAmount();
            // bind the parameter of the new value from the object to the query (prevents SQL injection)
            $query->bindParam(':refundAmount', $up_refundAmount, PDO::PARAM_STR);
        }

        // if description has been provided
        if ($customerEmail_updated === true) {
            // set order object description to given value (checks for valid input)
            $order->setCustomerEmail($jsonData->customerEmail);
            // get the value back as the object could be handling the return of the value differently to
            // what was provided
            $up_customerEmail = $order->getCustomerEmail();
            // bind the parameter of the new value from the object to the query (prevents SQL injection)
            $query->bindParam(':customerEmail', $up_customerEmail, PDO::PARAM_STR);
        }

        // if customerId has been provided
        if ($customerId_updated === true) {
            // set order object deadline to given value (checks for valid input)
            $order->setCustomerId($jsonData->customerId);
            // get the value back as the object could be handling the return of the value differently to
            // what was provided
            $up_customerId = $order->getCustomerId();
            // bind the parameter of the new value from the object to the query (prevents SQL injection)
            $query->bindParam(':customerId', $up_customerId, PDO::PARAM_STR);
        }
        // if customerUserId has been provided
        if ($customerUserId_updated === true) {
            // set order object deadline to given value (checks for valid input)
            $order->setCustomerUserId($jsonData->customerUserId);
            // get the value back as the object could be handling the return of the value differently to
            // what was provided
            $up_customerUserId = $order->getCustomerUserId();
            // bind the parameter of the new value from the object to the query (prevents SQL injection)
            $query->bindParam(':customerUserId', $up_customerUserId, PDO::PARAM_STR);
        }

        // if status has been provided
        if ($status_updated === true) {
            // set order object filter to given value (checks for valid input)
            $order->setStatus($jsonData->status);
            // get the value back as the object could be handling the return of the value differently to
            // what was provided
            $up_status = $order->getStatus();
            // bind the parameter of the new value from the object to the query (prevents SQL injection)
            $query->bindParam(':status', $up_status, PDO::PARAM_STR);
        }

        // bind the order id provided in the query string
        $query->bindParam(':orderId', $id, PDO::PARAM_INT);
        // run the query
        $query->execute();

        // get affected row count
        $rowCount = $query->rowCount();

        // check if row was actually updated, could be that the given values are the same as the stored values
        if ($rowCount === 0) {
            // set up response for unsuccessful return
            $response = new Response(400, false, "Order not updated - given values may be the same as the stored values", null);
            $response->send();
            return;
        }
        // ADD AUTH TO QUERY
        // create db query to return the newly edited order - connect to master database
        $query = $db->prepare('SELECT id, merchantReference, amount, refundAmount, currency, customerId, customerUserId, customerEmail, status, DATE_FORMAT(datetime, "%d/%m/%Y %H:%i") as datetime from orders where id = :orderId');
        $query->bindParam(':orderId', $id, PDO::PARAM_INT);
        $query->execute();

        // get row count
        $rowCount = $query->rowCount();

        // check if order was found
        if ($rowCount === 0) {
            // set up response for unsuccessful return
            $response = new Response(404, false, "No order found", null);
            $response->send();
            return;
        }
        // create order array to store returned orders
        $orderArray = array();

        // for each row returned
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            // create new order object for each row returned
            $order = new Order($row['id'], $row['merchantReference'], $row['amount'], $row['refundAmount'], $row['currency'], $row['customerId'], $row['customerUserId'], $row['customerEmail'], $row['status'], $row['datetime']);

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
        return;
    } 
    catch (OrderException $ex) {
        $response = new Response(400, false, $ex->getMessage(), null);
        $response->send();
        return;
    }
    // if error with sql query return a json error
    catch (PDOException $ex) {
        error_log("Database Query Error: " . $ex, 0);
        $response = new Response(500, false, "Failed to update order - check your data for errors", null);
        $response->addMessage($ex->getMessage());
        $response->send();
        return;
    }
}
function createOrder($db, $jsonData){
    // create order
    try {
        // TODO - data validation
        if (!isset($jsonData->merchantReference)) {
            $response = new Response(400, false, "merchantReference not set", null);
            return $response;
        }
        if (!isset($jsonData->amount)) {
            $response = new Response(400, false, "amount not set", null);
            return $response;
        }
        if (!isset($jsonData->refundAmount)) {
            $response = new Response(400, false, "refundAmount not set", null);
            return $response;
        }
        if (!isset($jsonData->currency)) {
            $response = new Response(400, false, "currency not set", null);
            return $response;
        }
        if (!isset($jsonData->customerId)) {
            $response = new Response(400, false, "customerId not set", null);
            return $response;
        }
        if (!isset($jsonData->customerUserId)) {
            $response = new Response(400, false, "customerUserId not set", null);
            return $response;
        }
        if (!isset($jsonData->customerEmail)) {
            $response = new Response(400, false, "customerEmail not set", null);
            return $response;
        }
        if (!isset($jsonData->status)) {
            $response = new Response(400, false, "status not set", null);
            return $response;
        }
        // create new order with data, if non mandatory fields not provided then set to null
        $newOrder = new Order(null, $jsonData->merchantReference, $jsonData->amount, $jsonData->refundAmount, $jsonData->currency, $jsonData->customerId, $jsonData->customerUserId, $jsonData->customerEmail, $jsonData->status, null);
        // get title, description, deadline, filter and store them in variables

        // create db query
        $query = $db->prepare('insert into orders (merchantReference, amount, refundAmount, currency, customerId, customerUserId, customerEmail, status, datetime) ' .
            'values (:merchantReference, :amount, :refundAmount, :currency, :customerId, :customerUserId, :customerEmail, :status, NOW())');
        $query->bindParam(':merchantReference', $jsonData->merchantReference, PDO::PARAM_STR);
        $query->bindParam(':amount', $jsonData->amount, PDO::PARAM_STR);
        $query->bindParam(':refundAmount', $jsonData->refundAmount, PDO::PARAM_STR);
        $query->bindParam(':currency', $jsonData->currency, PDO::PARAM_STR);
        $query->bindParam(':customerId', $jsonData->customerId, PDO::PARAM_STR);
        $query->bindParam(':customerUserId', $jsonData->customerUserId, PDO::PARAM_STR);
        $query->bindParam(':customerEmail', $jsonData->customerEmail, PDO::PARAM_STR);
        $query->bindParam(':status', $jsonData->status, PDO::PARAM_STR);
        $query->execute();

        // get row count
        $rowCount = $query->rowCount();

        // check if row was actually inserted, PDO exception should have caught it if not.
        if ($rowCount === 0) {
            // set up response for unsuccessful return
            $response = new Response(500, false, "Failed to create order", null);
            return $response;
        }

        // get last order id so we can return the Order in the json
        $lastorderId = $db->lastInsertId();
        // create db query to get newly created order - get from master db not read slave as replication may be too slow for successful read
        $query = $db->prepare('SELECT id, merchantReference, amount, refundAmount, currency, customerId, customerUserId, customerEmail, status, DATE_FORMAT(datetime, "%d/%m/%Y %H:%i") as datetime from orders where id = :orderId');
        $query->bindParam(':orderId', $lastorderId, PDO::PARAM_INT);
        $query->execute();

        // get row count
        $rowCount = $query->rowCount();
        // make sure that the new order was returned
        if ($rowCount === 0) {
            // set up response for unsuccessful return
            $response = new Response(500, false, "Failed to retrieve order after creation", null);
            return $response;
        }

        // create empty array to store orders
        $orderArray = array();

        // for each row returned - should be just one
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            // create new order object
            $order = new Order($row['id'], $row['merchantReference'], $row['amount'], $row['refundAmount'], $row['currency'], $row['customerId'], $row['customerUserId'], $row['customerEmail'], $row['status'], $row['datetime']);

            // create order and store in array for return in json data
            $orderArray[] = $order->returnOrderAsArray();
        }
        // bundle orders and rows returned into an array to return in the json data
        $returnData = array();
        $returnData['rows_returned'] = $rowCount;
        $returnData['orders'] = $orderArray;

        //set up response for successful return
        $response = new Response(201, true, "Order created", $returnData);
        return $response;
    }
    // if order fails to create due to data types, missing fields or invalid data then send error json
    catch (OrderException $ex) {
        $response = new Response(400, false, $ex->getMessage(), null);
        return $response;
    }
    // if error with sql query return a json error
    catch (PDOException $ex) {
        error_log("Database Query Error: " . $ex, 0);
        $response = new Response(500, false, "Failed to insert order into database - check submitted data for errors", null);
        return $response;
    }
}
function validateAccessToken($db, $accessToken){
    try {
        // create db query to check access token is equal to the one provided
        $query = $db->prepare('select userId, accessTokenExpiry, userActive, loginAttempts, type, admin from sessions, users where sessions.userId = users.id and accessToken = :accessToken');
        $query->bindParam(':accessToken', $accessToken, PDO::PARAM_STR);
        $query->execute();
    
        // get row count
        $rowCount = $query->rowCount();
    
        if ($rowCount === 0) {
            // set up response for unsuccessful log out response
            $response = new Response(401, false, "Invalid access token", null);
            return $response;
        }
    
        // get returned row
        $row = $query->fetch(PDO::FETCH_ASSOC);
    
        // save returned details into variables
        $returned_userId = $row['userId'];
        $returned_accessTokenExpiry = $row['accessTokenExpiry'];
        $returned_userActive = $row['userActive'];
        $returned_loginAttempts = $row['loginAttempts'];
        $returned_type = $row['type'];
        $returned_admin = $row['admin'];
    
        // check if account is active
        if ($returned_userActive != 'Y') {
            $response = new Response(401, false, "User account is not active", null);
            return $response;
        }
    
        // check if account is locked out
        if ($returned_loginAttempts >= 3) {
            $response = new Response(401, false, "User account is currently locked out", null);
            return $response;
        }
    
        // check if access token has expired
        if (strtotime($returned_accessTokenExpiry) < time()) {
            $response = new Response(401, false, null, null);
            $response->addMessage("accessToken: " . $accessToken);
            $response->addMessage("expiry: " . $returned_accessTokenExpiry);
            $response->addMessage("time now: " . date('Y-m-d H:i:s', time()));
            $response->addMessage("expiry:" . strtotime($returned_accessTokenExpiry) . " now:" . time());
            $response->addMessage("Access token has expired");
            return $response;
        }
        // All OK if it gets here
        $data = array();
        $data['userId'] = $returned_userId;
        $data['accessTokenExpiry'] = $returned_accessTokenExpiry;
        $data['userActive'] = $returned_userActive;
        $data['loginAttempts'] = $returned_loginAttempts;
        $data['type'] = $returned_type;
        $data['admin'] = $returned_admin;

        $response = new Response(200, true, "", $data);
        return $response;
    } catch (PDOException $ex) {
        $response = new Response(500, false, "There was an issue authenticating - please try again", null);
        return $response;
    }
}