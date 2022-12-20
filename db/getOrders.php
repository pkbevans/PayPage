<?php
include_once '../PeRestLib/RestConstants.php';
$incoming = json_decode(file_get_contents('php://input'));
$accessToken = refreshAccessToken();
$url = 'http://site.test/payPage/v1/orders?email='. $incoming->email. '&customerId=' . $incoming->customerId . '&mrn=' . $incoming->mrn .'&id=' . $incoming->orderId .'&status=' . $incoming->status;
$response = fetch(METHOD_GET, $url, $accessToken, null);
$data = json_decode($response);
$orders = new stdClass();
if($data->statusCode == 200){
    $orders = $data->data->orders;
}
function refreshAccessToken()
{
    // Refresh Access token
    $accessToken = getCookie('accessToken');
    $refreshToken = getCookie('refreshToken');
    $sessionId = getCookie('sessionId');
    $payload = new stdClass();
    $payload->refreshToken = $refreshToken;
    $url = 'http://site.test/paypage/v1/sessions/' . $sessionId;
    $response = fetch(METHOD_PATCH, $url, $accessToken, json_encode($payload));
    $data = json_decode($response);
    if ($data->statusCode == 200) {
        // Update session cookie
        $accessToken = $data->data->accessToken;
        $refreshToken = $data->data->refreshToken;
        setcookie('accessToken', $accessToken, 0, '/');
        setcookie('refreshToken', $refreshToken, 0, '/');
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
    $data = curl_exec($curl);
    curl_close($curl);
    return $data;
}
function getCookie($name){
    if(isset($_COOKIE[$name])){
        return $_COOKIE[$name];
    } else{
        return "";
    }
}