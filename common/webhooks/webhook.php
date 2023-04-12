<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/payPage/common/cybsApi/RestConstants.php';
require_once( '../v1/model/Response.php');

// Log the payload
$rawPostData = file_get_contents('php://input');
logWebHook($rawPostData);
$response = new Response(200, true, "OK", null);
$response->send();
exit();

function logWebHook($completeMsg){
    // Append msg to $orderReference file
    try {
        $fileName = $_SERVER['DOCUMENT_ROOT'] . LOGS_LOCATION . "WEBHOOK" . ".log";
        $fp = fopen($fileName, 'a'); //opens file in append mode  
        fwrite($fp, $completeMsg . "\n");  
        fclose($fp);  
    } catch (Exception $ex) {
        echo "FAILED TO OPEN FILE: " . $fileName . " : " . $ex->getMessage();
    }
}

