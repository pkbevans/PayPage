<?php
require_once 'Request.php';
require_once 'Response.php';
require_once 'CGK.php';
final class API {
    //URLs
    const TEST_URL = 'https://apitest.cybersource.com';
    const PROD_URL = 'https://api.cybersource.com';

    //APIs (URL paths)
    const FLEX_V1_KEYS = '/flex/v1/keys';
    const TSS_V2_SEARCHES_id = '/tss/v2/searches/{searchId}';
    const TSS_V2_TRANSACTIONS_id = '/tss/v2/transactions/{transactionId}';
    const PTS_V2_PAYMENTS = '/pts/v2/payments';
    const PTS_V2_PAYMENTS_id_REFUNDS = '/pts/v2/payments/{refundId}/refunds';
    const PTS_V2_PAYOUTS = '/pts/v2/payouts';
    const RISK_V1_AUTHENTICATION_SETUPS = '/risk/v1/authentication-setups';
    const RISK_V1_AUTHENTICATIONS = '/risk/v1/authentications';
    const RISK_V1_AUTHENTICATION_RESULTS = '/risk/v1/authentication-results';
    const BOARDING_V1_REGISTRATIONS = '/boarding/v1/registrations';
    const TMS_V2_CUSTOMERS = '/tms/v2/customers';
    const TMS_V2_CUSTOMERS_id = '/tms/v2/customers/{customerId}';
    const TMS_V2_CUSTOMERS_id_PAYMENTINSTRUMENTS = '/tms/v2/customers/{customerId}/payment-instruments';
    const TMS_V2_CUSTOMERS_id_SHIPPINGADDRESSSES = '/tms/v2/customers/{customerId}/shipping-addresses';
    //methods
    const POST = 'POST';
    const GET = 'GET';
    const DELETE = 'DELETE';
    const PUT = 'PUT';
    const PATCH = 'PATCH';

    public static function sendRequest($url, $method, $api, $mid, $body=null, $id=null, $query=null, $child=null, $authentication='signature') {
        //by default request is created with Content-Type: application/json, override for APIs which require application/xml instead, see 'Request.php' for details
        $request = new Request($url, $method, $api);
        if ($body !== null) {
            $request->body = $body;
        }
        if ($id !== null) {
            $start = strpos($api,'{');
            $end = strpos($api,'}');
            if ($start !== false && $end !== false) {
                $length = $end - $start;
                $result = substr($api,$start,$length+1);
                $request->path = str_replace($result,$id,$api);
            }
        }
        if ($query !== null) {
            $request->query = $query;
        }

        $request->prepareHeaders($mid, $authentication);
        $request->prepareUrl();

        if ($authentication === 'signature') {
            $request->prepareTarget();
            if ($child !== null) { CGK::generateHttpSignatureForRequest($request, $child); } else { CGK::generateHttpSignatureForRequest($request); }
        }

        if ($authentication === 'bearer') {
            if ($child !== null) { CGK::generateBearerTokenForRequestAndMid($request, $mid, $child); } else { CGK::generateBearerTokenForRequestAndMid($request, $mid); }
        }

        $curlResponse = API::sendCurlRequest($request);
        $response = new Response($curlResponse);

        return json_encode(array(
            'request' => array(
                'requestLine' => $request->requestLine,
                'headers' => $request->headers,
                'body' => $request->body,
                'rawRequest' => $request->rawRequest
            ),
            'response' => array(
                'httpCode' => $response->httpCode,
                'statusLine' => $response->statusLine,
                'headers' => $response->headers,
                'body' => $response->body,
                'rawResponse' => $response->rawResponse
            )
        ));
    }
    private static function sendCurlRequest($request) {
        $config = parse_ini_file('configuration.ini');
        try {
            $ch = curl_init();
            if ($ch === false) {
                throw new Exception('curl_init failed');
            }
            curl_setopt($ch, CURLOPT_URL, $request->completeUrl);
            if ($request->body !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $request->body);
            }
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request->method);
            curl_setopt($ch, CURLOPT_HTTPHEADER, API::formatCurlHeaders($request->headers));
            curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            if ($config['proxy'] == true) {
                curl_setopt($ch, CURLOPT_PROXY,$config['proxy_address'].':'.$config['proxy_port']);
                curl_setopt($ch, CURLOPT_PROXYUSERPWD,$config['proxy_username'].':'.$config['proxy_password']);
            }
            $result = curl_exec($ch);
            if ($result === false) {
                if (curl_errno($ch) === 5) {
                    throw new Exception('You do not appear to be behind proxy. Please check proxy settings in the configuration file. '.curl_error($ch));
                } elseif (curl_errno($ch) === 7) {
                    throw new Exception('Please check proxy settings in the configuration file. '.curl_error($ch));
                } elseif (curl_errno($ch) === 56) {
                    throw new Exception('Proxy authentication required, please check your username and password in the configuration file. '.curl_error($ch));
                } else {
                    throw new Exception('I am not handling this error, please debug the code. '.curl_errno($ch).' - '.curl_error($ch));
                }
            }
            $request->processHeaders(curl_getinfo($ch, CURLINFO_HEADER_OUT));
            curl_close($ch);
            return $result;
        } catch (Exception $e) {
            echo $e->getMessage();
            exit();
        }
    }
    private static function formatCurlHeaders($headers) {
        $cHeaders = array();
        foreach ($headers as $name => $value){
            array_push($cHeaders, $name.': '.$value);
        }
        return $cHeaders;
    }
}
