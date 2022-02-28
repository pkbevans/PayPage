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
        fwrite($fp, $type . ":" . $status .":" . $amount . ":" . $tokenCreated . ":" . $completeMsg . "\n");  
        fclose($fp);  
    } catch (Exception $ex) {
        echo "FAILED TO OPEN FILE: " . $fileName;
    }
}
