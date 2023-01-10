<?php

require_once('db.php');
require_once('../model/Response.php');

try{
    $writeDB = DB::connectWriteDB();
}catch(PDOException $ex){
    error_log("Database query error - ". $ex, 0);
    $response = new Response(500, false, "Database connection error", null);
    $response->addMessage($ex->getMessage());
    $response->send();
    exit();
}

if($_SERVER['REQUEST_METHOD'] !== "POST"){
    // Only allow users to be created
    $response = new Response(405, false, "Request method not allowed", null);
    $response->send();
    exit();
}
if($_SERVER['CONTENT_TYPE'] !== "application/json"){
    $response = new Response(400, false, "Content Type header not set to JSON", null);
    $response->send();
    exit();
}

$rawPostData = file_get_contents('php://input');
if(!$jsonData = json_decode($rawPostData)){
    $response = new Response(400, false, "Request body is not valid JSON", null);
    $response->send();
    exit();
}
if(!isset($jsonData->firstName) || !isset($jsonData->lastName) || !isset($jsonData->userName) || !isset($jsonData->email) || !isset($jsonData->password) ){
    $response = new Response(400, false, null, null);
    (!isset($jsonData->firstName) ? $response->addMessage("firstName not supplied"):false);
    (!isset($jsonData->lastName) ? $response->addMessage("lastName not supplied"):false);
    (!isset($jsonData->userName) ? $response->addMessage("userName not supplied"):false);
    (!isset($jsonData->email) ? $response->addMessage("email not supplied"):false);
    (!isset($jsonData->password) ? $response->addMessage("password not supplied"):false);
    $response->send();
    exit();
}

if(strlen($jsonData->firstName) < 1 || strlen($jsonData->firstName)>255 ||
        strlen($jsonData->lastName) < 1 || strlen($jsonData->lastName)>255 ||
        strlen($jsonData->userName) < 1 || strlen($jsonData->userName)>255 ||
        strlen($jsonData->email) < 1 || strlen($jsonData->email)>255 ||
        strlen($jsonData->password) < 1 || strlen($jsonData->password)>255){
    $response = new Response(400, false, null, null);
    (strlen($jsonData->firstName) < 1 ? $response->addMessage("First name cannot be blank"):false);
    (strlen($jsonData->lastName) < 1 ? $response->addMessage("Last name cannot be blank"):false);
    (strlen($jsonData->userName) < 1 ? $response->addMessage("User name cannot be blank"):false);
    (strlen($jsonData->email) < 1 ? $response->addMessage("Email cannot be blank"):false);
    (strlen($jsonData->password) < 1 ? $response->addMessage("Password cannot be blank"):false);
    (strlen($jsonData->firstName) > 255 ? $response->addMessage("First name too long"):false);
    (strlen($jsonData->lastName) > 255 ? $response->addMessage("Last name too long"):false);
    (strlen($jsonData->userName) > 255 ? $response->addMessage("User name too long"):false);
    (strlen($jsonData->email) > 255 ? $response->addMessage("Email name too long"):false);
    (strlen($jsonData->password) > 255 ? $response->addMessage("Password too long"):false);
    $response->send();
    exit();
}

$firstName = trim($jsonData->firstName);
$lastName = trim($jsonData->lastName);
$userName = trim($jsonData->userName);
$email = trim($jsonData->email);
$password = $jsonData->password;

try{
    $query = $writeDB->prepare("select id from users where userName = :userName");
    $query->bindParam(':userName', $userName, PDO::PARAM_STR);
    $query->execute();

    $rowCount = $query->rowCount();
    if($rowCount !== 0){
        $response = new Response(409, false, "userName already exists", null);
        $response->send();
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $query = $writeDB->prepare('insert into users (firstName, lastname, userName, email, password, customerId, type, admin) values(:firstName, :lastName, :userName, :email, :password, "", "CUSTOMER", "N")');
    $query->bindParam(':firstName', $firstName, PDO::PARAM_STR);
    $query->bindParam(':lastName', $lastName, PDO::PARAM_STR);
    $query->bindParam(':userName', $userName, PDO::PARAM_STR);
    $query->bindParam(':email', $email, PDO::PARAM_STR);
    $query->bindParam(':password', $hashed_password, PDO::PARAM_STR);
    $query->execute();

    $rowCount = $query->rowCount();
    if($rowCount === 0){
        $response = new Response(500, false, "Unable to create user account - please try again", null);
        $response->send();
        exit();
    }
    $lastUserID = $writeDB->lastInsertId();

    $returnData = array();
    $returnData['id'] = $lastUserID;
    $returnData['firstName'] = $firstName;
    $returnData['lastName'] = $lastName;
    $returnData['userName'] = $userName;
    $returnData['email'] = $email;

    $response = new Response(201, true, "User created", $returnData);
    $response->send();
    exit();    
}catch(PDOException $ex){
    error_log("Database query error - ".$ex, 0);
    $response = new Response(500, false, "Error creating user account - please try again", null);
    $response->send();
    exit();
}