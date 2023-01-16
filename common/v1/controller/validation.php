<?php
include_once 'db.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/common/v1/model/Response.php';
const USERTYPE_INTERNAL = "INTERNAL";
const USERTYPE_CUSTOMER = "CUSTOMER";

function checkPermission($accessToken, $requiredType, $adminRequired, $customerId = null)
{
    // Validate that user has Admin permissions
    try {
        $readDB = DB::connectReadDB();
        $response = validateAccessToken($readDB, $accessToken);
        if ($response->success()) {
            // User is logged in, now check that they have required permission
            $data = $response->getData();
            if (($adminRequired && $data['admin'] !== 'Y') || 
                    $data['type'] !== $requiredType ||
                    (!empty($customerId) && $customerId !== $data['customerId'])) {
                $response = new Response(405, false, "You are not authorised to perform this action", null);
                return $response;
            }
        }
        return $response;
    } catch (PDOException $ex) {
        // log connection error for troubleshooting and return a json error response
        error_log("Connection Error: " . $ex, 0);
        $response = new Response(500, false, "Database connection error", null);
        return $response;
    }
}
function validateAccessToken($db, $accessToken){
    try {
        // create db query to check access token is equal to the one provided
        $query = $db->prepare('select userId, accessTokenExpiry, userActive, loginAttempts, type, admin, customerId from sessions, users where sessions.userId = users.id and accessToken = :accessToken');
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
        $returned_customerId = $row['customerId'];
    
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
        $data['customerId'] = $returned_customerId;

        $response = new Response(200, true, "", $data);
        return $response;
    } catch (PDOException $ex) {
        $response = new Response(500, false, "There was an issue authenticating - please try again", null);
        return $response;
    }
}
