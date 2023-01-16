<?php
include_once '../../common/cybsApi/RestConstants.php';
function fetch($accessToken, $method, $url, $payload){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    if($method == METHOD_POST || $method == METHOD_PATCH || $method == METHOD_PUT ) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
    }
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization:'. $accessToken, 'Content-type:application/json'));
    curl_setopt($curl, CURLOPT_HEADER, false);
    $response = curl_exec($curl);
    $response_info = curl_getinfo($curl);

    curl_close($curl);

    if ($response_info['http_code'] !== 200 && $response_info['http_code'] !== 201) {
        return array($response_info['http_code'], $response);
    }
    if(!$jsonData = json_decode($response)) {
        return array($response_info['http_code'], $response);
    }
    
    return array($response_info['http_code'], $jsonData->data);
}
function getCookie($name){
    if(isset($_COOKIE[$name])){
        return $_COOKIE[$name];
    } else{
        return "";
    }
}