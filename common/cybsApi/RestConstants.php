<?php
const KEYS_PATH =  "/ppSecure/";   // Replace with path to the CybsApiKeys file.
include_once $_SERVER["DOCUMENT_ROOT"] . KEYS_PATH. "CybsApiKeys.php";
include_once $_SERVER["DOCUMENT_ROOT"] . KEYS_PATH. "paypage.config.php";
// You need a CybsApiKeys.php file in this format, with REST keys matching the MID value
//////////////// DONT FORGET TO UPDATE googlePay.js with MID/CHILD_MID IF YOU CHANGE IT HERE!!!!/////////////////////////
// const MID = "<<TRANSACTING MID>>";   // Replace with Transacting MID (Or Can be PORTFOLIO or Account-level if using META KEY)
// const CHILD_MID = "";                // Normally blank (or Transacting MID if using PORTFOLIO or Account-level mid in MID)
// $keys = [
//    "<<TRANSACTING MID>>" => [
//        'key_id' => "<<REST KEY ID>>",
//        'secret_key'=> "<<REST SECRET KEY>>"
//    ]
// ];
//////////////// DONT FORGET TO UPDATE googlePay.js with MID/CHILD_MID IF YOU CHANGE IT HERE!!!!/////////////////////////

const PRODUCTION_TARGET_ORIGIN =  "bondevans.com";  // Replace with Production URL for non-localhost testing
const LOCALHOST_TARGET_ORIGIN =  "site.test";   // Replace with your localhost HTTPS alias.  MUST BE HTTPS
// Endpoints
const REQUEST_HOST =  "apitest.cybersource.com";  // CYBS TEST endpoint
//const REQUEST_HOST = "api.cybersource.com";  // CYBS PRODUCTION endpoint
const PUBLIC_KEYS_URL = "https://testflex.cybersource.com/flex/v2/public-keys/";
const CACHE_LOCATION = "/payPage/cache/";
const LOGS_LOCATION =  "/payPage/logs/";

// APIs
const API_PAYMENTS = "/pts/v2/payments";
const API_TMS_PAYMENT_INSTRUMENTS = "/tms/v1/paymentinstruments";
const API_FLEX_V2_SESSIONS = '/flex/v2/sessions';
const API_MICROFORM_SESSIONS = '/microform/v2/sessions';
const API_TSS_V2_SEARCHES = '/tss/v2/searches';
const API_TSS_V2_SEARCHES_id = '/tss/v2/searches/{searchId}';
const API_TSS_V2_TRANSACTIONS = '/tss/v2/transactions';
const API_PTS_V2_PAYMENTS_id_REFUNDS = '/pts/v2/payments/{refundId}/refunds';
const API_PTS_V2_PAYMENTS_CREDITS = '/pts/v2/credits';
const API_PTS_V2_PAYMENTS_REVERSAL = '/pts/v2/payments/{authId}/reversals';
const API_PTS_V2_PAYMENTS_id_VOIDS = '/pts/v2/payments/{voidId}/voids';
const API_PTS_V2_CAPTURES_id_VOIDS = '/pts/v2/captures/{voidId}/voids';
const API_PTS_V2_PAYOUTS = '/pts/v2/payouts';
const API_RISK_V1_AUTHENTICATION_SETUPS = '/risk/v1/authentication-setups';
const API_RISK_V1_AUTHENTICATIONS = '/risk/v1/authentications';
const API_RISK_V1_AUTHENTICATION_RESULTS = '/risk/v1/authentication-results';
const API_BOARDING_V1_REGISTRATIONS = '/boarding/v1/registrations';
const API_BOARDING_V1_TEMPLATES = "/boarding/v1/templates";
const API_TMS_V2_CUSTOMERS = '/tms/v2/customers';
const API_TMS_V2_CUSTOMER_PAYMENT_INSTRUMENTS = '/tms/v2/customers/{customerId}/payment-instruments';
const API_TMS_V2_CUSTOMER_SHIPPING_ADDRESSES = '/tms/v2/customers/{customerId}/shipping-addresses';
const API_TMS_V2_INSTRUMENT_IDS = '/tms/v1/instrumentidentifiers';
const API_OMS_V1_ORGANIZATIONS = '/oms/v1/organizations';
const API_UNIFIED_CHECKOUT_CAPTURE_CONTEXTS = '/up/v1/capture-contexts';
const API_PAYMENT_DETAILS = '/up/v1/payment-details';

// HTTP METHODS
const METHOD_POST = "POST";
const METHOD_GET = "GET";
const METHOD_PATCH = "PATCH";
const METHOD_DELETE = "DELETE";
const METHOD_PUT = 'PUT';

const AUTH_TYPE_SIGNATURE = "signature";
const AUTH_TYPE_BEARER = "bearer";

const MAXSIZE_NAME = 60;
const MAXSIZE_ADDRESS = 60;
const MAXSIZE_CITY = 50;
const MAXSIZE_POSTCODE = 10;
const MAXSIZE_STATE = 2;
const MAXSIZE_COUNTRY = 2;
