<?php
include_once '../ppSecure/PeRestLibKeys.php';
//const PORTFOLIO = "barclayssitt00";
//const MID = "paulspants21005";
const PORTFOLIO = "pemid03";
const MID = "";
const TARGET_ORIGIN =  "https://bondevans.com";  
// Endpoints
const REQUEST_HOST =  "apitest.cybersource.com";  // TEST
//const REQUEST_HOST = "api.cybersource.com";  // PRODUCTION

// APIs
const API_PAYMENTS = "/pts/v2/payments/";
const API_TMS_PAYMENT_INSTRUMENTS = "/tms/v1/paymentinstruments";
const API_FLEX_V1_KEYS = '/flex/v1/keys';
const API_TSS_V2_SEARCHES = '/tss/v2/searches';
const API_TSS_V2_SEARCHES_id = '/tss/v2/searches/{searchId}';
const API_TSS_V2_TRANSACTIONS_id = '/tss/v2/transactions/{transactionId}';
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

// HTTP METHODS
const METHOD_POST = "POST";
const METHOD_GET = "GET";
const METHOD_PATCH = "PATCH";
const METHOD_DELETE = 'DELETE';
const METHOD_PUT = 'PUT';

const AUTH_TYPE_SIGNATURE = "signature";
const AUTH_TYPE_BEARER = "bearer";

const MAXSIZE_NAME = 60;
const MAXSIZE_ADDRESS = 60;
const MAXSIZE_CITY = 50;
const MAXSIZE_POSTCODE = 10;
const MAXSIZE_COUNTRY = 2;
