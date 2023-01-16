<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/common/v1/controller/validation.php';

$incoming = json_decode(file_get_contents('php://input'));
$response = checkPermission($incoming->accessToken, USERTYPE_INTERNAL, true);
if(!$response->success()){
    $response->send();
    exit;
}
$logFilePath = $_SERVER['DOCUMENT_ROOT'] . "/payPage/logs/";
$referenceNumber = $incoming->referenceNumber; 
$prettyLog = "";
try {
    $fileName = $logFilePath . $referenceNumber . ".log";
    if ( !file_exists($fileName) ) {
        throw new Exception("Unable to open file: ". $fileName);
      }
    $fp = fopen($fileName, 'r'); //opens file in read mode
    if(!$fp){
        throw new Exception("Unable to open file: ". $fileName);
    }  
    // $log = fread($fp, filesize($fileName));
    $json = "{";
    $x=1;
    while(!feof($fp)) {
        $line = fgets($fp);
        // Ignore empty line
        if(!ctype_space($line) && $line != "") {
            $json .=  ($x==1?"":",") ."\"Msg". $x."\":". $line;
            ++$x;
        }
    }
    $json .= "}";
    fclose($fp); 
    $logs = new stdClass();
    $logs = json_decode($json);
    $prettyLog = json_encode($logs, JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR|JSON_UNESCAPED_SLASHES);
    if(json_last_error()){
        $prettyLog = $json;
    }
} catch (Exception $ex) {
    $prettyLog = "ERROR: " . $ex->getMessage();
}
// All good if it gets here
include_once $_SERVER['DOCUMENT_ROOT'].'/payPage/admin/view/viewGatewayLog.php';
