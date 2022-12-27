<?php
include_once '../PeRestLib/RestConstants.php';
$incoming = json_decode(file_get_contents('php://input'));
if (!$accessToken = refreshAccessToken()) {
    echo "Error: Refreshing token<BR>";
    // TODO - Login required
    exit;
}
// Access token refreshed - now get the orders
$url = 'http://'. $_SERVER['SERVER_NAME'] . '/payPage/v1/controller/orders.php?email='. $incoming->email. '&customerId=' . $incoming->customerId . '&mrn=' . $incoming->mrn .'&id=' . $incoming->orderId .'&status=' . $incoming->status;
if(!$response = fetch(METHOD_GET, $url, $accessToken, null)){
    echo "Error: Fetching orders<BR>";
    exit;
}
$jsonData = json_decode($response);
if(!$jsonData = json_decode($response)) {
    echo $response;
    exit;
}
$orders = new stdClass();
if($jsonData->statusCode == 200){
    $orders = $jsonData->data->orders;
    include '../view/listOrders.php';
}else{
    echo '<BR><pre>.' . json_encode($jsonData, JSON_PRETTY_PRINT) . '</pre>';
}
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