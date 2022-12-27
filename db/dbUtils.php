<?php
include_once '../PeRestLib/RestConstants.php';
function refreshAccessToken()
{
    // Refresh Access token
    $accessToken = getCookie('accessToken');
    $refreshToken = getCookie('refreshToken');
    $sessionId = getCookie('sessionId');
    $payload = new stdClass();
    $payload->refreshToken = $refreshToken;
    $url = 'http://'. $_SERVER['SERVER_NAME'] . '/payPage/v1/controller/sessions.php?sessionid=' . $sessionId . '&patch=';
    // echo $url;
    $response = fetch(METHOD_POST, $url, $accessToken, json_encode($payload));
    if(!$response){
        return false;
    }
    if(!$jsonData = json_decode($response)) {
        echo $response;
        return false;
    }
    if ($jsonData->statusCode == 200) {
        // Update session cookie
        $accessToken = $jsonData->data->accessToken;
        $refreshToken = $jsonData->data->refreshToken;
        setcookie('accessToken', $accessToken, 0, '/');
        setcookie('refreshToken', $refreshToken, 0, '/');
        $time = date("D M d Y H:i:s", time() + $jsonData->data->accessTokenExpiresIn);
        setcookie ('accessTokenExpires', $time, 0, '/');
        $time = date("D M d Y H:i:s", time() + $jsonData->data->refreshTokenExpiresIn);
        setcookie ('refreshTokenExpires', $time, 0, '/');
    }
    else{
        echo $response;
        return false;
    }
    return $accessToken;
}
function fetch($method, $url, $accessToken, $payload){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    if($method == METHOD_POST || $method == METHOD_PATCH || $method == METHOD_PUT ) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
    }
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization:'. $accessToken, 'Content-type:application/json'));
    curl_setopt($curl, CURLOPT_HEADER, false);
    $jsonData = curl_exec($curl);
    curl_close($curl);
    return $jsonData;
}
function getCookie($name){
    if(isset($_COOKIE[$name])){
        return $_COOKIE[$name];
    } else{
        return "";
    }
}