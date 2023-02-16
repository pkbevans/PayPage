<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/payPage/common/cybsApi/RestConstants.php';
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
function logApi($orderReference, $completeMsg){
    // Append msg to $orderReference file
    try {
        $fileName = $_SERVER['DOCUMENT_ROOT'] . LOGS_LOCATION . $orderReference . ".log";
        $fp = fopen($fileName, 'a'); //opens file in append mode  
        fwrite($fp, $completeMsg . "\n");  
        fclose($fp);  
    } catch (Exception $ex) {
        echo "FAILED TO OPEN FILE: " . $fileName . " : " . $ex->getMessage();
    }
}
