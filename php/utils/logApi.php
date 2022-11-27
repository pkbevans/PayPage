<?php
$logFilePath = $_SERVER['DOCUMENT_ROOT'] . "/payPage/logs/";
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
function logApi($orderReference, $type, $status, $amount, $tokenCreated, $completeMsg){
    global $logFilePath;
    // Append msg to $orderReference file
    try {
        $fileName = $logFilePath . $orderReference . ".log";
        $fp = fopen($fileName, 'a'); //opens file in append mode  
        fwrite($fp, $completeMsg . "\n");  
        fclose($fp);  
    } catch (Exception $ex) {
        echo "FAILED TO OPEN FILE: " . $fileName;
    }
}
function getApiLog($orderReference){
    global $logFilePath;
    try {
        $fileName = $logFilePath . $orderReference . ".log";
        $fp = fopen($fileName, 'r'); //opens file in read mode  
        $log = fread($fp, filesize($fileName));
        fclose($fp);  
        return $log;
    } catch (Exception $ex) {
        echo "FAILED TO OPEN FILE: " . $fileName;
    }
}