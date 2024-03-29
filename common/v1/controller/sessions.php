<?php
require_once('db.php');
require_once('../model/Response.php');
const MAX_LOGIN_ATTEMPTS = 3;
const ACCESS_TOKEN_EXPIRY_SECS = 900;
const REFRESH_TOKEN_EXPIRY_SECS = 1200;

try{
    $writeDB = DB::connectWriteDB();
}catch(PDOException $ex){
    error_log("Connection error - ". $ex, 0);
    $response = new Response(500, false, "Database connection error", null );
    $response->send();
    exit;
}

if (array_key_exists("sessionid", $_GET)) {

    $sessionId = $_GET['sessionid'];
    if ($sessionId === '' || !is_numeric($sessionId)) {
        $response = new Response(400, false, null, null);
        ($sessionId === '' ? $response->addMessage("Session Id cannot be blank") : false);
        (!is_numeric($sessionId) ? $response->addMessage("Session Id must be numeric") : false);
        $response->send();
        exit;
    }
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

    $accessToken = $_SERVER['HTTP_AUTHORIZATION'];

    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        logoutSession($writeDB, $sessionId, $accessToken);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
        refreshAccessToken($writeDB, $sessionId, $accessToken);
    } elseif(array_key_exists("patch", $_GET) && $_SERVER['REQUEST_METHOD'] === 'POST'){
        refreshAccessToken($writeDB, $sessionId, $accessToken);
    } else {
        $response = new Response(405, true, "Request method not supported", null);
        $response->send();
        exit;
    }
}elseif(empty($_GET)){
    if($_SERVER['REQUEST_METHOD'] !== "POST"){
        $response = new Response(405, false, "Request method not allowed",null);
        $response->send();
        exit;
    }
    loginSession($writeDB);
}else{
    $response = new Response(404, false, "Endpoint not found", null);
    $response->send();
    exit;   
}
function loginSession($db){
    // Login request (Create session)
    sleep(1);   // Deliberate slow down - anti brute force attack 
    if($_SERVER['CONTENT_TYPE'] !== "application/json"){
        $response = new Response(400, false, "Content Type header not set to JSON", null);
        $response->send();
        return;
    }
    $rawPostData = file_get_contents('php://input');
    if(!$jsonData = json_decode($rawPostData)){
        $response = new Response(400, false, "Request body is not valid JSON", null);
        $response->send();
        return;
    }    

    if(!isset($jsonData->userName) || !isset($jsonData->password)){
        $response = new Response(400, false, null, null);
        (!isset($jsonData->userName) ? $response->addMessage("userName not supplied"):false);
        (!isset($jsonData->password) ? $response->addMessage("password not supplied"):false);
        $response->send();
        return;
    }
    if(strlen($jsonData->userName) < 1 || strlen($jsonData->userName)>255 ||
            strlen($jsonData->password) < 1 || strlen($jsonData->password)>255){
        $response = new Response(400, false, null, null);
        (strlen($jsonData->userName) < 1 ? $response->addMessage("User name cannot be blank"):false);
        (strlen($jsonData->password) < 1 ? $response->addMessage("Password cannot be blank"):false);
        (strlen($jsonData->userName) > 255 ? $response->addMessage("User name too long"):false);
        (strlen($jsonData->password) > 255 ? $response->addMessage("Password too long"):false);
        $response->send();
        return;
    }
    if(isset($jsonData->type)){
        if($jsonData->type !== "CUSTOMER" && $jsonData->type !== "INTERNAL"){
            $response = new Response(400, false, null, null);
            $response->addMessage("invalid type");
            $response->send();
            return;
        }
        $required_type = $jsonData->type;
    }

    try{
        $userName = trim($jsonData->userName);
        $password = $jsonData->password;

        $query = $db->prepare("select id, firstName, lastName, userName, email, customerId, password, userActive, loginAttempts, type from pp_usrs where userName = :userName");
        $query->bindParam(':userName', $userName, PDO::PARAM_STR);
        $query->execute();

        $rowCount = $query->rowCount();
        if($rowCount === 0){
            $response = new Response(401, false, "userName or password is incorrect", null);
            $response->send();
            return;
        }

        $row = $query->fetch(PDO::FETCH_ASSOC);
        $returned_id = $row['id'];
        $returned_firstName = $row['firstName'];
        $returned_lastName = $row['lastName'];
        $returned_userName = $row['userName'];
        $returned_email = $row['email'];
        $returned_customerId = $row['customerId'];
        $returned_password = $row['password'];
        $returned_userActive = $row['userActive'];
        $returned_loginAttempts = $row['loginAttempts'];
        $returned_type = $row['type'];
        
        if($returned_userActive != 'Y'){
            $response = new Response(401, false, "User account not active", null);
            $response->send();
            return;
        }
        if($returned_loginAttempts>= MAX_LOGIN_ATTEMPTS){
            $response = new Response(401, false, "User account is currently locked out", null);
            $response->send();
            return;
        }
        if(isset($required_type) && $required_type !== $returned_type){
            $response = new Response(401, false, "User account does not have access", null);
            $response->send();
            return;
        }

        if(!password_verify($password, $returned_password)){
            $query = $db->prepare("update pp_usrs set loginAttempts = loginAttempts+1 where id = :id");
            $query->bindParam(':id', $returned_id, PDO::PARAM_INT);
            $query->execute();

            $response = new Response(401, false, "userName or password is incorrect", null);
            $response->send();
            return;            
        }

        $accessToken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24))).time();
        $refreshToken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24))).time();
        $accessTokenExpirySecs = ACCESS_TOKEN_EXPIRY_SECS;
        $refreshTokenExpirySecs = REFRESH_TOKEN_EXPIRY_SECS;
        // Successfull login at this point
    }catch(PDOException $ex){
        error_log($ex->getMessage());
        $response = new Response(500, false, "Error logging in", null);
        $response->send();
        return;
    }

    try{
        $db->beginTransaction();

        $query = $db->prepare("update pp_usrs set loginAttempts = 0 where id = :id");
        $query->bindParam(':id', $returned_id, PDO::PARAM_INT);
        $query->execute();

        $query = $db->prepare("insert into pp_sessions (userid, accessToken, accessTokenexpiry, refreshToken, refreshTokenexpiry) values(:userid, :accessToken, date_add(NOW(), INTERVAL :accessTokenExpirySecs SECOND), :refreshToken, date_add(NOW(), INTERVAL :refreshTokenExpirySecs SECOND))");
        $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
        $query->bindParam(':accessToken', $accessToken, PDO::PARAM_STR);
        $query->bindParam(':accessTokenExpirySecs', $accessTokenExpirySecs, PDO::PARAM_INT);
        $query->bindParam(':refreshToken', $refreshToken, PDO::PARAM_STR);
        $query->bindParam(':refreshTokenExpirySecs', $refreshTokenExpirySecs, PDO::PARAM_INT);
        $query->execute();

        $lastSessionId = $db->lastInsertId();

        $db->commit();
        $returnData = array();
        $returnData['userName'] = $returned_userName;
        $returnData['firstName'] = $returned_firstName;
        $returnData['lastName'] = $returned_lastName;
        $returnData['email'] = $returned_email;
        $returnData['customerId'] = $returned_customerId;
        $returnData['customerUserId'] = $returned_id;
        $returnData['sessionId'] = intval($lastSessionId);
        $returnData['accessToken'] = $accessToken;
        $returnData['accessTokenExpiresIn'] = $accessTokenExpirySecs;
        $returnData['refreshToken'] = $refreshToken;
        $returnData['refreshTokenExpiresIn'] = $refreshTokenExpirySecs;

        $response = new Response(201, true, null, $returnData);
        $response->send();
        return;
    
    }catch(PDOException $ex){
        $db->rollBack();
        $response = new Response(500, false, "There was an issue loggin in - please try again", null);
        $response->send();
        return;   
    }    
}
function logoutSession($db, $sessionId, $accessToken){
    try {
        $query = $db->prepare("delete from pp_sessions where id = :id and accessToken = :accessToken");
        $query->bindParam(':id', $sessionId, PDO::PARAM_INT);
        $query->bindParam(':accessToken', $accessToken, PDO::PARAM_STR);
        $query->execute();
    } catch (PDOException $ex) {
        $response = new Response(500, false, "There was an issue logging out - please try again", null);
        $response->send();
        return;
    }

    $rowCount = $query->rowCount();
    if ($rowCount === 0) {
        $response = new Response(401, false, "Failed to log out of this session using access token provided[" . $accessToken . "]", null);
        $response->send();
        return;
    }
    // SUCCESS
    $returnData = array();
    $returnData['sessionId'] = intval($sessionId);
    $response = new Response(200, true, null, $returnData);
    $response->send();
    return;
}
function refreshAccessToken($db, $sessionId, $accessToken){
    if (!isset($_SERVER['CONTENT_TYPE']) || $_SERVER['CONTENT_TYPE'] !== 'application/json' || !is_numeric($sessionId)) {
        $response = new Response(400, false, "Content Type not set to JSON", null);
        $response->send();
        exit;
    }
    $rawPatchData = file_get_contents('php://input');
    if (!$jsonData = json_decode($rawPatchData)) {
        $response = new Response(400, false, "Request body invalid JSON", null);
        $response->send();
        exit;
    }
    if (!isset($jsonData->refreshToken) || strlen($jsonData->refreshToken) < 1) {
        $response = new Response(400, false, null, null);
        if (!isset($jsonData->refreshToken)) {
            $response->addMessage("Refresh token not provided");
        } else {
            (strlen($jsonData->refreshToken) < 1 ? $response->addMessage("Refresh token cannot be blank") : false);
        }
        $response->send();
        exit;
    }

    try {
        $refreshToken = $jsonData->refreshToken;
        $stmt ='select pp_sessions.id as sessionid, pp_sessions.userid as userid, accessToken, refreshToken, userActive, loginAttempts, accessTokenexpiry, refreshTokenexpiry from pp_sessions, pp_usrs where pp_usrs.id = pp_sessions.userid and pp_sessions.id = :sessionid and pp_sessions.accessToken = :accessToken and pp_sessions.refreshToken = :refreshToken';
        $query = $db->prepare($stmt);
        $query->bindParam(':sessionid', $sessionId, PDO::PARAM_INT);
        $query->bindParam(':accessToken', $accessToken, PDO::PARAM_STR);
        $query->bindParam(':refreshToken', $refreshToken, PDO::PARAM_STR);
        $query->execute();

        $rowCount = $query->rowCount();
        if ($rowCount === 0) {
            $response = new Response(401, false, "Access token or refresh token is incorrect for session id", null);
            $response->addMessage("stmt:" . $stmt);
            $response->addMessage("SessionId:" . $sessionId);
            $response->addMessage("Access token:" . $accessToken);
            $response->addMessage("refresh token:" . $refreshToken);
            $response->send();
            return;
        }

        $row = $query->fetch(PDO::FETCH_ASSOC);
        $returned_sessionid = $row['sessionid'];
        $returned_userid = $row['userid'];
        $returned_accessToken = $row['accessToken'];
        $returned_refreshToken = $row['refreshToken'];
        $returned_userActive = $row['userActive'];
        $returned_loginAttempts = $row['loginAttempts'];
        $returned_refreshTokenexpiry = $row['refreshTokenexpiry'];

        if ($returned_userActive !== 'Y') {
            $response = new Response(401, false, "User account is not active", null);
            $response->send();
            return;
        }
        if ($returned_loginAttempts >= MAX_LOGIN_ATTEMPTS) {
            $response = new Response(401, false, "User account is locked", null);
            $response->send();
            return;
        }
        if (strtotime($returned_refreshTokenexpiry) < time()) {
            $response = new Response(401, false, "Refresh token has expired - please log in again", null);
            $response->send();
            return;
        }

        $accessToken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24) . time()));
        $refreshToken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24) . time()));

        $accessTokenExpirySecs = ACCESS_TOKEN_EXPIRY_SECS;
        $refreshTokenExpirySecs = REFRESH_TOKEN_EXPIRY_SECS;
        //HERE
        $query = $db->prepare('update pp_sessions set accessToken = :accessToken, accessTokenexpiry = date_add(NOW(), INTERVAL :accessTokenExpirySecs SECOND), refreshToken = :refreshToken, refreshTokenexpiry = date_add(NOW(), INTERVAL :refreshTokenExpirySecs SECOND) where id = :sessionid and userid= :userid and accessToken = :returnedaccessToken and refreshToken = :returnedrefreshToken');
        $query->bindParam(':userid', $returned_userid, PDO::PARAM_INT);
        $query->bindParam(':sessionid', $returned_sessionid, PDO::PARAM_INT);
        $query->bindParam(':accessToken', $accessToken, PDO::PARAM_STR);
        $query->bindParam(':accessTokenExpirySecs', $accessTokenExpirySecs, PDO::PARAM_INT);
        $query->bindParam(':refreshToken', $refreshToken, PDO::PARAM_STR);
        $query->bindParam(':refreshTokenExpirySecs', $refreshTokenExpirySecs, PDO::PARAM_INT);
        $query->bindParam(':returnedaccessToken', $returned_accessToken, PDO::PARAM_STR);
        $query->bindParam(':returnedrefreshToken', $returned_refreshToken, PDO::PARAM_STR);
        $query->execute();

        $rowCount = $query->rowCount();
        if ($rowCount === 0) {
            $response = new Response(401, false, "Access token could not be refreshed - please log in again", null);
            $response->send();
            return;
        }

        $returnData = array();
        $returnData['sessionId'] = $returned_sessionid;
        $returnData['accessToken'] = $accessToken;
        $returnData['accessTokenExpiresIn'] = $accessTokenExpirySecs;
        $returnData['refreshToken'] = $refreshToken;
        $returnData['refreshTokenExpiresIn'] = $refreshTokenExpirySecs;
        $returnData['sessionId'] = $returned_sessionid;
        $response = new Response(200, true, "Token refreshed", $returnData);
        $response->send();
        return;
    } catch (PDOException $ex) {
        error_log($ex->getMessage(), 0);
        $response = new Response(500, false, "There was an issue refreshing access token - please log in again", null);
        $response->send();
        return;
    }
}





