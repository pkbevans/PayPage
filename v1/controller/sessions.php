<?php

require_once('db.php');
require_once('../model/Response.php');

try{
    $writeDB = DB::connectWriteDB();
}catch(PDOException $ex){
    error_log("Connection error - ". $ex, 0);
    $response = new Response(500, false, "Database connection error", null );
    $response->send();
    exit;
}

if(array_key_exists("sessionid",$_GET)){

    $sessionId = $_GET['sessionid'];
    if($sessionId === '' || !is_numeric($sessionId)){
        $response = new Response(400, false, null, null);
        ($sessionId === '' ? $response->addMessage("Session Id cannot be blank") : false);
        (!is_numeric($sessionId) ? $response->addMessage("Session Id must be numeric") : false);
        $response->send();
        exit;   
    }
    if(!isset($_SERVER['HTTP_AUTHORIZATION']) || strlen($_SERVER['HTTP_AUTHORIZATION']) < 1){
        $response = new Response(401, false, null, null);
        if(!isset($_SERVER['HTTP_AUTHORIZATION'])){
            $response->addMessage("Access token is missing from the header");
        }elseif(strlen($_SERVER['HTTP_AUTHORIZATION']) < 1){
            $response->addMessage("Access token cannot be blank");
        }
        $response->send();
        exit;   
    }

    $accessToken = $_SERVER['HTTP_AUTHORIZATION'];

    if($_SERVER['REQUEST_METHOD'] === 'DELETE'){
        try{
            $query = $writeDB->prepare("delete from sessions where id = :id and accessToken = :accessToken");
            $query->bindParam(':id', $sessionId, PDO::PARAM_INT);
            $query->bindParam(':accessToken', $accessToken, PDO::PARAM_STR);
            $query->execute();
        }catch(PDOException $ex){
            $response = new Response(500, false, "There was an issue logging out - please try again", null);
            $response->send();
            exit;   
        }

        $rowCount = $query->rowCount();
        if($rowCount === 0){
            $response = new Response(401, false, "Failed to log out of this session using access token provided[".$accessToken."]", null);
            $response->send();
            exit;
        }
        // SUCCESS
        $returnData = array();
        $returnData['sessionId'] = intval($sessionId);
        $response = new Response(200, true, "HELLO", $returnData);
        $response->send();
        exit;
    }elseif($_SERVER['REQUEST_METHOD'] === 'PATCH'){

        if(!isset($_SERVER['CONTENT_TYPE']) || $_SERVER['CONTENT_TYPE'] !== 'application/json' || !is_numeric($sessionId)){
            $response = new Response(400, false, "Content Type not set to JSON", null);
            $response->send();
            exit;   
        }
        $rawPatchData = file_get_contents('php://input');
        if(!$jsonData = json_decode($rawPatchData)){
            $response = new Response(400, false, "Request body invalid JSON", null);
            $response->send();
            exit;           
        }
        if(!isset($jsonData->refreshToken) || strlen($jsonData->refreshToken) < 1){
            $response = new Response(400, false, null, null);
            if(!isset($jsonData->refreshToken)){
                $response->addMessage("Refresh token not provided");
            }else{
                (strlen($jsonData->refreshToken) < 1 ? $response->addMessage("Refresh token cannot be blank") : false);
            }
            $response->send();
            exit;           
        }

        try{
            $refreshToken = $jsonData->refreshToken;
            $query = $writeDB->prepare('select sessions.id as sessionid, sessions.userid as userid, accessToken, refreshToken, userActive, loginAttempts, accessTokenexpiry, refreshTokenexpiry from sessions, users where users.id = sessions.userid and sessions.id = :sessionid and sessions.accessToken = :accessToken and sessions.refreshToken = :refreshToken');
            $query->bindParam(':sessionid', $sessionId, PDO::PARAM_INT);
            $query->bindParam(':accessToken', $accessToken, PDO::PARAM_STR);
            $query->bindParam(':refreshToken', $refreshToken, PDO::PARAM_STR);
            $query->execute();

            $rowCount = $query->rowCount();
            
            if($rowCount === 0){
                $response = new Response(401, false, "Access token or refresh token is incorrect for session id", "" );
                $response->addMessage("Access token:".$accessToken);
                $response->addMessage("refresh token:".$refreshToken);
                $response->send();
                exit;           
            }

            $row = $query->fetch(PDO::FETCH_ASSOC);
            $returned_sessionid = $row['sessionid'];
            $returned_userid = $row['userid'];
            $returned_accessToken = $row['accessToken'];
            $returned_refreshToken = $row['refreshToken'];
            $returned_userActive = $row['userActive'];
            $returned_loginAttempts = $row['loginAttempts'];
            $returned_accessTokenexpiry = $row['accessTokenexpiry'];
            $returned_refreshTokenexpiry = $row['refreshTokenexpiry'];

            if($returned_userActive !== 'Y'){
                $response = new Response(401, false, "User account is not active", null);
                $response->send();
                exit;                           
            }
            if($returned_loginAttempts >= 3){
                $response = new Response(401, false, "User account is locked", null);
                $response->send();
                exit;                           
            }
            if(strtotime($returned_refreshTokenexpiry) < time()){
                $response = new Response(401, false, "Refresh token has expired - please log in again", null);
                $response->send();
                exit;                           
            }
            
            $accessToken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24).time()));
            $refreshToken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24).time()));

            $accessTokenExpirySecs = 1200;
            $refreshTokenExpirySecs = 1209600;
            //HERE
            $query = $writeDB->prepare('update sessions set accessToken = :accessToken, accessTokenexpiry = date_add(NOW(), INTERVAL :accessTokenExpirySecs SECOND), refreshToken = :refreshToken, refreshTokenexpiry = date_add(NOW(), INTERVAL :refreshTokenExpirySecs SECOND) where id = :sessionid and userid= :userid and accessToken = :returnedaccessToken and refreshToken = :returnedrefreshToken');
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
            if($rowCount === 0){
                $response = new Response(401, false, "Access token could not be refreshed - please log in again", null);
                $response->send();
                exit;                   
            }

            $returnData = array();
            $returnData['sessionId'] = $returned_sessionid;
            $returnData['accessToken'] = $accessToken;
            $returnData['accessTokenExpiry'] = $accessTokenExpirySecs;
            $returnData['refreshToken'] = $refreshToken;
            $returnData['refreshTokenExpiry'] = $refreshTokenExpirySecs;
            $returnData['sessionId'] = $returned_sessionid;
            $response = new Response(200, true, "Token refreshed", $returnData);
            $response->send();
            exit;                  
        }catch(PDOException $ex){
            error_log($ex->getMessage(),0);
            $response = new Response(500, false, "There was an issue refreshing access token - please log in again", null);
            $response->send();
            exit;           
        }

    }else{
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
    // Login request (Create session)
    sleep(1);   // Deliberate slow down - anti brute force attack 
    if($_SERVER['CONTENT_TYPE'] !== "application/json"){
        $response = new Response(400, false, "Content Type header not set to JSON", null);
        $response->send();
        exit;
    }
    $rawPostData = file_get_contents('php://input');
    if(!$jsonData = json_decode($rawPostData)){
        $response = new Response(400, false, "Request body is not valid JSON", null);
        $response->send();
        exit;
    }    

    if(!isset($jsonData->userName) || !isset($jsonData->password)){
        $response = new Response(400, false, null, null);
        (!isset($jsonData->userName) ? $response->addMessage("userName not supplied"):false);
        (!isset($jsonData->password) ? $response->addMessage("password not supplied"):false);
        $response->send();
        exit;
    }
    if(strlen($jsonData->userName) < 1 || strlen($jsonData->userName)>255 ||
            strlen($jsonData->password) < 1 || strlen($jsonData->password)>255){
        $response = new Response(400, false, null, null);
        (strlen($jsonData->userName) < 1 ? $response->addMessage("User name cannot be blank"):false);
        (strlen($jsonData->password) < 1 ? $response->addMessage("Password cannot be blank"):false);
        (strlen($jsonData->userName) > 255 ? $response->addMessage("User name too long"):false);
        (strlen($jsonData->password) > 255 ? $response->addMessage("Password too long"):false);
        $response->send();
        exit;
    }

    try{
        $userName = trim($jsonData->userName);
        $password = $jsonData->password;

        $query = $writeDB->prepare("select id, password, userActive, loginAttempts from users where userName = :userName");
        $query->bindParam(':userName', $userName, PDO::PARAM_STR);
        $query->execute();

        $rowCount = $query->rowCount();
        if($rowCount === 0){
            $response = new Response(401, false, "userName or password is incorrect", null);
            $response->send();
            exit;
        }

        $row = $query->fetch(PDO::FETCH_ASSOC);
        $returned_id = $row['id'];
        // $returned_firstName = $row['firstName'];
        // $returned_lastName = $row['lastName'];
        // $returned_userName = $row['userName'];
        // $returned_email = $row['email'];
        $returned_password = $row['password'];
        $returned_userActive = $row['userActive'];
        $returned_loginAttempts = $row['loginAttempts'];
        
        if($returned_userActive != 'Y'){
            $response = new Response(401, false, "User account not active", null);
            $response->send();
            exit;
        }
        if($returned_loginAttempts>= 3){
            $response = new Response(401, false, "User account is currently locked out", null);
            $response->send();
            exit;
        }

        if(!password_verify($password, $returned_password)){
            $query = $writeDB->prepare("update users set loginAttempts = loginAttempts+1 where id = :id loginAttempts from users where userName = :userName");
            $query->bindParam(':id', $returned_id, PDO::PARAM_INT);
            $query->execute();

            $response = new Response(401, false, "userName or password is incorrect", null);
            $response->send();
            exit;            
        }

        $accessToken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24))).time();
        $refreshToken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24))).time();
        $accessTokenExpirySecs = 1200;
        $refreshTokenExpirySecs = 1209600;    // 14 Days
        // Successfull login at this point
    }catch(PDOException $ex){
        $response = new Response(500, false, "Error logging in", null);
        $response->send();
        exit;
    }

    try{
        $writeDB->beginTransaction();

        $query = $writeDB->prepare("update users set loginAttempts = 0 where id = :id");
        $query->bindParam(':id', $returned_id, PDO::PARAM_INT);
        $query->execute();

        $query = $writeDB->prepare("insert into sessions (userid, accessToken, accessTokenexpiry, refreshToken, refreshTokenexpiry) values(:userid, :accessToken, date_add(NOW(), INTERVAL :accessTokenExpirySecs SECOND), :refreshToken, date_add(NOW(), INTERVAL :refreshTokenExpirySecs SECOND))");
        $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
        $query->bindParam(':accessToken', $accessToken, PDO::PARAM_STR);
        $query->bindParam(':accessTokenExpirySecs', $accessTokenExpirySecs, PDO::PARAM_INT);
        $query->bindParam(':refreshToken', $refreshToken, PDO::PARAM_STR);
        $query->bindParam(':refreshTokenExpirySecs', $refreshTokenExpirySecs, PDO::PARAM_INT);
        $query->execute();

        $lastSessionId = $writeDB->lastInsertId();

        $writeDB->commit();
        $returnData = array();
        $returnData['sessionId'] = intval($lastSessionId);
        $returnData['accessToken'] = $accessToken;
        $returnData['accessTokenExpiresIn'] = $accessTokenExpirySecs;
        $returnData['refreshToken'] = $refreshToken;
        $returnData['refreshTokenExpiresIn'] = $refreshTokenExpirySecs;

        $response = new Response(201, true, null, $returnData);
        $response->send();
        exit;
    
    }catch(PDOException $ex){
        $writeDB->rollBack();
        $response = new Response(500, false, "There was an issue loggin in - please try again", null);
        $response->send();
        exit;   
    }
}else{
    $response = new Response(404, false, "Endpoint not found", null);
    $response->send();
    exit;   
}








