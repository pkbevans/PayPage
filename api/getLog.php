<?php
$logFilePath = $_SERVER['DOCUMENT_ROOT'] . "/payPage/logs/";
$incoming = json_decode(file_get_contents('php://input'));
$referenceNumber = $incoming->referenceNumber; 

try {
    $fileName = $logFilePath . $referenceNumber . ".log";
    $fp = fopen($fileName, 'r'); //opens file in read mode  
    // $log = fread($fp, filesize($fileName));
    $json = "{";
    $x=1;
    while(!feof($fp)) {
        $line = fgets($fp);
        // Ignore empty line
        if(!ctype_space($line) && $line != "") {
            $json .=  ($x==1?"":",") ."\"log". $x."\":". $line;
            ++$x;
        }
    }
    $json .= "}";
    fclose($fp); 
    $logs = new stdClass();
    $logs = json_decode($json);
    $pretty = json_encode($logs, JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR);
    if(json_last_error()){
        $pretty = $json;
    }
} catch (Exception $ex) {
    echo "<BR>HELLO6";
    echo "FAILED TO OPEN FILE: " . $fileName;
}
