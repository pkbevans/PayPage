<?php
require '../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;

$jwt = 'eyJraWQiOiJ6dSIsImFsZyI6IlJTMjU2In0.eyJmbHgiOnsicGF0aCI6Ii9mbGV4L3YyL3Rva2VucyIsImRhdGEiOiJmOHdma0hlMWpQWXppZmFWNW9zSGhoQUFFRXpqeUo1ZklvK2JsRStXTTRtNXFxTys4WWN3YjNzQXZVTTQyblJtMXZHaTNTNGRlejJPNTFER1pEU0JmU3Z2bkhnWW9Wa1RQcU9POTZuejRCcGNQeEh1bUh0dkVHK2Y5SklpQjlzMExmZWQiLCJvcmlnaW4iOiJodHRwczovL3Rlc3RmbGV4LmN5YmVyc291cmNlLmNvbSIsImp3ayI6eyJrdHkiOiJSU0EiLCJlIjoiQVFBQiIsInVzZSI6ImVuYyIsIm4iOiJuQ3RiOUdvT0gxRGJtX3FRSTE0TVpPSk5lMXhhanJNSU5sZDc2TEI2UnMycXVOUGEzaUgzcm9uQ2NuYUJ6U3ZVaGdpT0dxOHlZYVNybnBkZGV0OFhhSU5rbmFsWkZDcDg2TzRnTjFQVk1HZEVfc2ZGZnoxbW5HanhjUk9sWE81SF9WbFRpTnR6MTNEZElyN2d6TEZpYW9hcEtMNFBHeFRUNHpGamRpem5iWlptR0tDQjlNcFN4S0FLUUstajZlN2ZHckc4emswLTFjYlJqeTBVa2VNUkNQNS1iZS1Tb0o1NUZKamFjRGJKeUxpX3pyeE41aGswbjU0YXI3NUdBVUZ6bkt6ekhvR0JfdW5neXlLNnpuUjh4UkwxdGlxbDA0bHR2d2o5T2d3MDU3VElLSmItdFZnUV8wbXhXNzg5U3JKYjJGT01JcXE5Mk50NTRYMmNxcXNUSVEiLCJraWQiOiIwOHpNYVJ1WVlsM2tlNHVMRFBzaUdMT3p5UWJiajhUciJ9fSwiY3R4IjpbeyJkYXRhIjp7ImNsaWVudExpYnJhcnkiOiJodHRwczovL3Rlc3RmbGV4LmN5YmVyc291cmNlLmNvbS9taWNyb2Zvcm0vYnVuZGxlL3YxL2ZsZXgtbWljcm9mb3JtLm1pbi5qcyIsInRhcmdldE9yaWdpbnMiOlsiaHR0cHM6Ly9zaXRlLnRlc3QiXSwibWZPcmlnaW4iOiJodHRwczovL3Rlc3RmbGV4LmN5YmVyc291cmNlLmNvbSJ9LCJ0eXBlIjoibWYtMS4wLjAifV0sImlzcyI6IkZsZXggQVBJIiwiZXhwIjoxNjc1NDY3NTU3LCJpYXQiOjE2NzU0NjY2NTcsImp0aSI6IkVucXNNRjdISUs0N2FMaHEifQ.IzZWPXqeRsCV8jnM5FUlzhJAezj7eD_m0u_75uRBjdGdXe2W4w3pXP87qJF3_9Ft-zA8FWhj7MtWJICCWJoGJH2UnjmaIb5_SkSpicXvJfPkMLpD7VA-uH3TYRi1elIFr5j6ykN6Bb0UEd7DX24aTUKOpVwNWuLMfzxPCaEJzXqtrqEIoqlLhm7w1S65Jp8JVaqABrm-c_5Afh2bNTDS4GrZLK_F7R4maUvxsqvSZAUoL2H-FkYwFegBYsFBl2nj8k7_LW8te6ovhon-e66DF7Z7Evu0H1x6qnBkLZWJ7gIzaZDB6ZbnPAXZkQ6A2343tzNsGiR8seb2MCn--Cptng';
echo "JWT: " . $jwt . "<BR><BR>";
if(verifyToken($jwt)){
    echo "VERIFIED<BR>";
}else{
    echo "SOMETHING WENT WRONG<BR>";
}

function verifyToken($jwt)
{
    // Decode JWT header to get value of kid
    [$rawHeader, $rawPayload] = explode('.', $jwt, 2);
    $header = json_decode(base64_decode($rawHeader));
    // echo "HEADER: " . json_encode($header) . "<BR><BR>";
    $kid = $header->kid;
    $alg = $header->alg;
    // echo "KID: " . $kid . "<BR><BR>";
    // Get public key with given kid
    $key = file_get_contents('https://testflex.cybersource.com/flex/v2/public-keys/' . $kid);
    // echo "KEY: " . $key . "<BR><BR>";
    // Turn this key into a PEM
    $jwks = [];
    $pk = json_decode($key, true);
    $pk['alg'] = $alg;
    $jwks['keys'][0] = $pk;
    // echo '<pre>' . json_encode($jwks, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . '</pre>';
    // Verify the JWT against the given public key
    try {
        $decoded = JWT::decode($jwt, JWK::parseKeySet($jwks));
        // echo '<pre>' . json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . '</pre>';
        return true;
    }catch( Exception $e){
        echo "ERROR: " . $e. "<BR>";
        return false;
    }
}