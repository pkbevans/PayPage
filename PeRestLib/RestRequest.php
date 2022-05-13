<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/payPage/PeRestLib/RestConstants.php';

// HTTP POST request
function ProcessRequest($mid, $resource, $method, $payload, $child = null, $authentication = AUTH_TYPE_SIGNATURE )
{
    $headerParams = [];
    $headers = [];

    $url = "https://" . REQUEST_HOST . $resource;
    $resource = utf8_encode($resource);

    $date = date("D, d M Y G:i:s ") . "GMT";

    $headerParams['Content-Type'] = 'application/json;charset=utf-8';

    foreach ($headerParams as $key => $val) {
        $headers[] = "$key: $val";
    }

    if($authentication == AUTH_TYPE_SIGNATURE) {
        $authHeaders = GetHttpSignature($resource, $method, $date, $payload, $mid, $child);
    }else{
        $jsonWebToken = GetJsonWebToken($mid, $payload, $method, $date, $child);
        $authHeaders = array(
            'Authorization:' . $jsonWebToken
        );
    }
    $headerParams = array_merge($headers, $authHeaders);

    $curl = curl_init();

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headerParams);
    if($method == METHOD_POST || $method == METHOD_PATCH || $method == METHOD_PUT ) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
    }
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($curl, CURLOPT_HEADER, 1);
    curl_setopt($curl, CURLOPT_VERBOSE, 0);
    curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0");

    // Send the request
    $response = curl_exec($curl);

    $http_header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    $http_headers = httpParseHeaders(substr($response, 0, $http_header_size));
    $http_body = substr($response, $http_header_size);
    $response_info = curl_getinfo($curl);

    $result = new stdClass();
    $result->url = $url;
    $result->mid = $mid . (is_null($child)?"":"/".$child);
    $result->method = $method;
    $result->request = json_decode($payload);
    $result->responseCode = $response_info['http_code'];
    if($resource == API_FLEX_V2_SESSIONS || $resource == API_MICROFORM_SESSIONS
            || $resource == API_UNIFIED_CHECKOUT_CAPTURE_CONTEXTS){
        $result->rawResponse = $http_body;
    }else{
        $response = json_decode($http_body);
        $result->response = $response;
    }

    $requestHeaders = [];
    foreach ($headerParams as $key => $val) {
        $requestHeaders[$key] = $val;
    }
//    $result->requestHeaders = $requestHeaders;
//    $result->responseInfo = $response_info;
//    $result->responseHeaders=$http_headers;
//    $result->response = $response;

    return $result;
}

// Function to generate the HTTP Signature
// param: resourcePath - denotes the resource being accessed
// param: httpMethod - denotes the HTTP verb
// param: currentDate - stores the current timestamp
// param: payload - request body (JSON)
// param: mid - mid
// param: child - transacting mid if using meta key
function GetHttpSignature($resourcePath, $httpMethod, $currentDate, $payload, $mid, $child=null)
{
    global $keys;
    $digest = "";
    $signatureString = "";
    $headerString = "";

    if($httpMethod == METHOD_GET || $httpMethod == METHOD_DELETE)
    {
        $signatureString = "host: " . REQUEST_HOST . "\ndate: " . $currentDate . "\n(request-target): " . strtolower($httpMethod) . " " . $resourcePath . "\nv-c-merchant-id: " . $mid;
        // echo "<BR>HELLO 1<BR>";
        $headerString = "host date (request-target) v-c-merchant-id";

    }
    else if($httpMethod == METHOD_POST || $httpMethod == METHOD_PUT || $httpMethod == METHOD_PATCH)
    {
        // echo "<BR>HELLO 2:". $resourcePath . "<BR>";
        //Get digest data
        $digest = GenerateDigest($payload);

        $signatureString = "host: " . REQUEST_HOST . "\ndate: " . $currentDate . "\n(request-target): " . strtolower ($httpMethod) . " " . $resourcePath . "\ndigest: SHA-256=" . $digest . "\nv-c-merchant-id: " . $mid;
        $headerString = "host date (request-target) digest v-c-merchant-id";
    }

    $signatureByteString = utf8_encode($signatureString);
    $decodeKey = base64_decode($keys[$mid]["secret_key"]);
    $signature = base64_encode(hash_hmac("sha256", $signatureByteString, $decodeKey, true));

    $signatureHeader = array(
        'keyid="' . $keys[$mid]["key_id"] . '"',
        'algorithm="HmacSHA256"',
        'headers="' . $headerString . '"',
        'signature="' . $signature . '"'
    );

    $signatureToken = "Signature:" . implode(", ", $signatureHeader);

    $host = "Host:" . REQUEST_HOST;
    // If we are using a meta key, we create the signature based on the portfolio/account mid and then stick the
    // transacting mid into the v-c-merchant-id field
    if($child === null || empty($child)) {
        $vcMerchant = "v-c-merchant-id:" . $mid;
    }else{
        $vcMerchant = "v-c-merchant-id:" . $child;
    }
    $headers = array(
        $vcMerchant,
        $signatureToken,
        $host,
        'Date:' . $currentDate
    );

    // Only need the digest for POST/PUT/PATCH - not GET/DELETE
    if($httpMethod == METHOD_POST || $httpMethod == METHOD_PUT || $httpMethod == METHOD_PATCH){
        $digestArray = array("Digest: SHA-256=" . $digest);
        $headers = array_merge($headers, $digestArray);
    }

    return $headers;
}

// Function to get the JWT
// param: payload - denotes the request body
// param: httpMethod - denotes the HTTP verb
// param: currentDate - stores the current timestamp
function GetJsonWebToken($mid, $payload, $httpMethod, $currentDate, $child)
{
    if($httpMethod == METHOD_GET || $httpMethod == METHOD_DELETE)
    {
        $jwtBody = array("iat" => $currentDate);

    }
    else if($httpMethod == METHOD_POST || $httpMethod == METHOD_PUT || $httpMethod == METHOD_PATCH)
    {
        $digest = GenerateDigest($payload);
        $jwtBody = array("digest" => $digest, "digestAlgorithm" => "SHA-256", "iat" => $currentDate);

    }

    $tokenHeader = GenerateJsonWebToken($mid, $jwtBody, $child);
//    echo PHP_EOL . " -- TOKEN --" . PHP_EOL; echo $tokenHeader;
    return "Bearer " . $tokenHeader;
}

// Function to generate the JWT
function GenerateJsonWebToken($mid, $jwtBody, $child)
{
    global $keys;

    $filePath = __DIR__ . DIRECTORY_SEPARATOR . 'resources/' . $keys[$mid]["keyFileName"];
    $keyPass = $keys[$mid]["keyPass"];
    if ($child !== null) { $keyalias = $child; } else { $keyalias = $keys[$mid]['p12Alias']; }

    $cert_store = file_get_contents($filePath);

    if (openssl_pkcs12_read($cert_store, $cert_info, $keyPass))
    {
        $certdata = openssl_x509_parse($cert_info['cert'], 1);
        $privateKey = $cert_info['pkey'];
        $publicKey = PemToDer($cert_info['cert']);
        $x5cArray = array($publicKey);
        $headers = array(
            "v-c-merchant-id" => $keyalias,
            "x5c" => $x5cArray,
            'typ' => "JWT",
            'alg' => "RS256"
        );

        return generateBearer(json_encode($headers),json_encode($jwtBody),$privateKey);
    }
}
// Function to convert the provided pem cert to der
function PemToDer($Pem)
{
    $lines = explode("\n", trim($Pem));
    unset($lines[count($lines) - 1]);
    unset($lines[0]);
    return implode("\n", $lines);
}
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
function sign($input, $key) {
    $signature = '';
    $result = openssl_sign($input, $signature, $key, 'SHA256');
    if (!$result) {
        throw new Exception("OpenSSL unable to sign data");
    } else {
        return $signature;
    }
}
function generateBearer($header,$payload,$key){
    $base64urlHeader = base64url_encode($header);
    $base64urlPayload = base64url_encode($payload);
    $text = $base64urlHeader.'.'.$base64urlPayload;
    $signature = base64url_encode(sign($text, $key));
    return $text.'.'.$signature;
}
// Function used to generate the digest for the given payload
function GenerateDigest($requestPayload)
{
    $utf8EncodedString = utf8_encode($requestPayload);
    $digestEncode = hash("sha256", $utf8EncodedString, true);
    return base64_encode($digestEncode);
}
// Function to parse response headers
// ref/credit: http://php.net/manual/en/function.http-parse-headers.php#112986
function httpParseHeaders($raw_headers)
{
    $headers = [];
    $key = '';
    foreach (explode("\n", $raw_headers) as $h) {
        $h = explode(':', $h, 2);
        if (isset($h[1])) {
            if (!isset($headers[$h[0]])) {
                $headers[$h[0]] = trim($h[1]);
            } elseif (is_array($headers[$h[0]])) {
                $headers[$h[0]] = array_merge($headers[$h[0]], [trim($h[1])]);
            } else {
                $headers[$h[0]] = array_merge([$headers[$h[0]]], [trim($h[1])]);
            }
            $key = $h[0];
        } else {
            if (substr($h[0], 0, 1) === "\t") {
                $headers[$key] .= "\r\n\t".trim($h[0]);
            } elseif (!$key) {
                $headers[0] = trim($h[0]);
            }
            trim($h[0]);
        }
    }
    return $headers;
}
?>
